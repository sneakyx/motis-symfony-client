<?php

declare(strict_types=1);

namespace Sneakyx\MotisSymfonyClient;

use DateTimeImmutable;
use DateTimeInterface;
use Sneakyx\MotisPhpClient\Model\GeocodeMatch;
use Sneakyx\MotisPhpClient\MotisClient;
use Sneakyx\MotisSymfonyClient\Exception\LocationNotFoundException;

/**
 * Fluent builder for MOTIS route searches.
 *
 * String inputs (place names or addresses) are geocoded automatically; plain
 * "lat,lon" strings and GeocodeMatch objects are used as-is. Example:
 *
 *     $manager->route()->from('Aachen Hbf')->to('Aachen Rothe Erde')->at($time)->get()
 */
final class RouteSearch
{
    private const COORDINATE_PATTERN = '/^\s*-?\d{1,3}\.\d+\s*,\s*-?\d{1,3}\.\d+\s*$/';

    private string|GeocodeMatch $from;
    private string|GeocodeMatch $to;
    private ?DateTimeImmutable $time = null;
    private bool $arriveBy = false;

    public function __construct(private readonly MotisManager $manager)
    {
    }

    /**
     * Sets the origin (place name, "lat,lon" string or GeocodeMatch).
     */
    public function from(string|GeocodeMatch $from): static
    {
        $this->from = $from;

        return $this;
    }

    /**
     * Sets the destination (place name, "lat,lon" string or GeocodeMatch).
     */
    public function to(string|GeocodeMatch $to): static
    {
        $this->to = $to;

        return $this;
    }

    /**
     * Sets the departure time (or the arrival time when arriveBy is used).
     */
    public function at(DateTimeInterface|string $time): static
    {
        $this->time = $time instanceof DateTimeImmutable ? $time : new DateTimeImmutable($time);

        return $this;
    }

    /**
     * Searches for connections arriving at the given time instead of departing.
     */
    public function arriveBy(bool $arriveBy = true): static
    {
        $this->arriveBy = $arriveBy;

        return $this;
    }

    /**
     * Runs the search and returns the itineraries as a collection.
     */
    public function get(): ItineraryCollection
    {
        $itineraries = $this->manager->client()->plan(
            $this->resolvePlace($this->from, 'from'),
            $this->resolvePlace($this->to, 'to'),
            $this->time ?? new DateTimeImmutable('now'),
            $this->arriveBy,
        )->getItineraries() ?? [];

        return new ItineraryCollection(array_values($itineraries));
    }

    /**
     * Resolves a place input to a "lat,lon" string for the plan endpoint.
     */
    private function resolvePlace(string|GeocodeMatch $place, string $direction): string
    {
        if ($place instanceof GeocodeMatch) {
            return MotisClient::coordinate((float) $place->getLat(), (float) $place->getLon());
        }

        if (\preg_match(self::COORDINATE_PATTERN, $place) === 1) {
            return $place;
        }

        $match = $this->manager->geocode($place)[0] ?? null;
        if (!$match instanceof GeocodeMatch) {
            throw new LocationNotFoundException(
                'Could not find a place for the ' . $direction . ' location: ' . $place,
            );
        }

        return MotisClient::coordinate((float) $match->getLat(), (float) $match->getLon());
    }
}
