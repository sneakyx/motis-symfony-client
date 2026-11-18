<?php

declare(strict_types=1);

namespace Sneakyx\MotisSymfonyClient\Tests\Unit;

use Sneakyx\MotisPhpClient\Model\GeocodeMatch;
use Sneakyx\MotisPhpClient\Model\HealthResponse;
use Sneakyx\MotisPhpClient\MotisClient;
use Sneakyx\MotisSymfonyClient\MotisManager;
use Sneakyx\MotisSymfonyClient\Tests\BundleTestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

final class MotisManagerTest extends BundleTestCase
{
    public function testExposesTheUnderlyingClient(): void
    {
        [$client] = $this->makeClient([]);

        $manager = new MotisManager($client);

        self::assertSame($client, $manager->client());
    }

    public function testGeocodeReturnsMatches(): void
    {
        [$client, $http] = $this->makeClient(['geocode_response.json']);

        $manager = new MotisManager($client, null, false);
        $result = $manager->geocode('Hauptwache');

        self::assertGreaterThan(0, \count($result));
        self::assertInstanceOf(GeocodeMatch::class, $result[0]);
        self::assertSame('Frankfurt (Main) Hauptwache', $result[0]->getName());
        self::assertCount(1, $http->requests());
    }

    public function testGeocodeResultsAreCached(): void
    {
        // Only one response is configured: a second live request would throw.
        [$client, $http] = $this->makeClient(['geocode_response.json']);

        $manager = new MotisManager($client, new ArrayAdapter(), true, 3600);
        $first = $manager->geocode('Hauptwache');
        $second = $manager->geocode('Hauptwache');

        self::assertEquals($first, $second);
        self::assertCount(1, $http->requests());
    }

    public function testGeocodeBypassesCacheWhenNoPoolIsGiven(): void
    {
        [$client, $http] = $this->makeClient(['geocode_response.json', 'geocode_response.json']);

        $manager = new MotisManager($client, null, true);
        $manager->geocode('Hauptwache');
        $manager->geocode('Hauptwache');

        self::assertCount(2, $http->requests());
    }

    public function testGeocodeBypassesCacheWhenDisabled(): void
    {
        [$client, $http] = $this->makeClient(['geocode_response.json', 'geocode_response.json']);

        $manager = new MotisManager($client, new ArrayAdapter(), false);
        $manager->geocode('Hauptwache');
        $manager->geocode('Hauptwache');

        self::assertCount(2, $http->requests());
    }

    public function testHealth(): void
    {
        [$client] = $this->makeClient(['health_response.json']);

        $manager = new MotisManager($client, null, false);
        $health = $manager->health();

        self::assertInstanceOf(HealthResponse::class, $health);
        self::assertTrue($health->getRt());
        self::assertFalse($health->getGbfs());
    }
}
