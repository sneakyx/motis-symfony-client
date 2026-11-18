<?php

declare(strict_types=1);

namespace Sneakyx\MotisSymfonyClient\Tests\Unit;

use DateTimeImmutable;
use Sneakyx\MotisPhpClient\Model\Itinerary;
use Sneakyx\MotisSymfonyClient\Exception\LocationNotFoundException;
use Sneakyx\MotisSymfonyClient\ItineraryCollection;
use Sneakyx\MotisSymfonyClient\MotisManager;
use Sneakyx\MotisSymfonyClient\Tests\BundleTestCase;
use Sneakyx\MotisSymfonyClient\Tests\Support\MockHttpClient;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

final class RouteSearchTest extends BundleTestCase
{
    public function testFromAndToCanBePlainCoordinateStrings(): void
    {
        [$client, $http] = $this->makeClient(['plan_response.json']);
        $manager = new MotisManager($client, new ArrayAdapter(), false);

        $result = $manager
            ->route()
            ->from('50.7683,6.0911')
            ->to('50.752,6.171')
            ->at('2026-11-18T10:00:00+01:00')
            ->get();

        self::assertInstanceOf(ItineraryCollection::class, $result);
        self::assertGreaterThan(0, $result->count());
        self::assertInstanceOf(Itinerary::class, $result->first());

        $query = $this->lastQuery($http);
        self::assertSame('50.7683,6.0911', $query['fromPlace']);
        self::assertSame('50.752,6.171', $query['toPlace']);
        self::assertSame('2026-11-18T10:00:00+01:00', $query['time']);
        self::assertSame('false', (string) $query['arriveBy']);
    }

    public function testFromAndToAreGeocodedWhenGivenAsStrings(): void
    {
        [$client, $http] = $this->makeClient([
            'geocode_response.json',
            'geocode_response.json',
            'plan_response.json',
        ]);
        $manager = new MotisManager($client, new ArrayAdapter(), false);

        $result = $manager
            ->route()
            ->from('Frankfurt (Main) Hauptwache')
            ->to('Hauptwache 5')
            ->at('2026-11-18T10:00:00+01:00')
            ->get();

        self::assertGreaterThan(0, $result->count());

        // Both place names were geocoded to the fixture match coordinates.
        $plan = $http->requests()[2];
        \parse_str($plan->getUri()->getQuery(), $query);
        self::assertSame('50.11224,8.68214', $query['fromPlace']);
        self::assertSame('50.11224,8.68214', $query['toPlace']);
    }

    public function testArriveByIsPassedToTheApi(): void
    {
        [$client, $http] = $this->makeClient(['plan_response.json']);
        $manager = new MotisManager($client, null, false);

        $manager
            ->route()
            ->from('50.7683,6.0911')
            ->to('50.752,6.171')
            ->at(new DateTimeImmutable('2026-11-18T10:00:00+01:00'))
            ->arriveBy()
            ->get();

        $query = $this->lastQuery($http);
        self::assertSame('true', (string) $query['arriveBy']);
    }

    public function testThrowsWhenALocationCannotBeGeocoded(): void
    {
        [$client] = $this->makeClient(['empty_geocode_response.json']);
        $manager = new MotisManager($client, null, false);

        $this->expectException(LocationNotFoundException::class);

        $manager
            ->route()
            ->from('Nowhere 1234567890')
            ->to('50.7683,6.0911')
            ->get();
    }

    /**
     * Returns the query parameters of the last recorded request.
     *
     * @return array<string, mixed>
     */
    private function lastQuery(MockHttpClient $http): array
    {
        $request = $http->requests()[\count($http->requests()) - 1];
        $query = [];
        \parse_str($request->getUri()->getQuery(), $query);

        return $query;
    }
}
