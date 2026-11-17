<?php

declare(strict_types=1);

namespace Sneakyx\MotisSymfonyClient;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Sneakyx\MotisPhpClient\Model\Itinerary;
use Traversable;

/**
 * Collection of itineraries with convenience helpers.
 *
 * @implements IteratorAggregate<int, Itinerary>
 */
final class ItineraryCollection implements Countable, IteratorAggregate
{
    /** @param list<Itinerary> $itineraries */
    public function __construct(private readonly array $itineraries)
    {
    }

    /**
     * @return Traversable<int, Itinerary>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->itineraries);
    }

    public function count(): int
    {
        return \count($this->itineraries);
    }

    /**
     * @return list<Itinerary>
     */
    public function all(): array
    {
        return $this->itineraries;
    }

    public function first(): ?Itinerary
    {
        return $this->itineraries[0] ?? null;
    }

    /**
     * Returns the itinerary with the shortest duration.
     */
    public function fastest(): ?Itinerary
    {
        $fastest = null;
        $fastestDuration = PHP_INT_MAX;

        foreach ($this->itineraries as $itinerary) {
            $duration = (int) ($itinerary->getDuration() ?? PHP_INT_MAX);
            if ($duration < $fastestDuration) {
                $fastest = $itinerary;
                $fastestDuration = $duration;
            }
        }

        return $fastest;
    }
}
