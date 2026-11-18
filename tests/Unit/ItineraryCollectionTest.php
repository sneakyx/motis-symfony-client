<?php

declare(strict_types=1);

namespace Sneakyx\MotisSymfonyClient\Tests\Unit;

use Sneakyx\MotisPhpClient\Model\Itinerary;
use Sneakyx\MotisSymfonyClient\ItineraryCollection;
use Sneakyx\MotisSymfonyClient\Tests\BundleTestCase;

final class ItineraryCollectionTest extends BundleTestCase
{
    public function testFastestReturnsTheItineraryWithTheShortestDuration(): void
    {
        $slow = new Itinerary(['duration' => 600]);
        $fast = new Itinerary(['duration' => 300]);

        $collection = new ItineraryCollection([$slow, $fast]);

        self::assertSame($fast, $collection->fastest());
    }

    public function testFastestReturnsNullForAnEmptyCollection(): void
    {
        self::assertNull((new ItineraryCollection([]))->fastest());
    }

    public function testFastestTreatsNullDurationAsSlowest(): void
    {
        $withoutDuration = new Itinerary([]);
        $slow = new Itinerary(['duration' => 900]);

        $collection = new ItineraryCollection([$withoutDuration, $slow]);

        self::assertSame($slow, $collection->fastest());
    }

    public function testAllFirstCountAndIteration(): void
    {
        $first = new Itinerary(['duration' => 300]);
        $second = new Itinerary(['duration' => 600]);

        $collection = new ItineraryCollection([$first, $second]);

        self::assertSame([$first, $second], $collection->all());
        self::assertSame($first, $collection->first());
        self::assertCount(2, $collection);

        $iterated = [];
        foreach ($collection as $itinerary) {
            $iterated[] = $itinerary;
        }

        self::assertSame([$first, $second], $iterated);
    }
}
