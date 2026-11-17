<?php

declare(strict_types=1);

namespace Sneakyx\MotisSymfonyClient;

use Psr\Cache\CacheItemPoolInterface;
use Sneakyx\MotisPhpClient\Model\GeocodeMatch;
use Sneakyx\MotisPhpClient\Model\HealthResponse;
use Sneakyx\MotisPhpClient\MotisClient;

/**
 * Service wrapping the plain php client and adding Symfony-flavoured
 * helpers: PSR-6 geocode caching and a fluent route search.
 */
final class MotisManager
{
    public function __construct(
        private readonly MotisClient $client,
        private readonly ?CacheItemPoolInterface $cache = null,
        private readonly bool $cacheEnabled = true,
        private readonly int $cacheTtl = 86400,
    ) {
    }

    /**
     * Returns the underlying php client.
     */
    public function client(): MotisClient
    {
        return $this->client;
    }

    /**
     * Geocodes a free-text query and returns the matches.
     *
     * Results are cached through the configured PSR-6 pool when enabled.
     *
     * @param list<non-empty-string> $type
     * @return list<GeocodeMatch>
     */
    public function geocode(string $query, array $type = []): array
    {
        if (!$this->cacheEnabled || $this->cache === null) {
            return $this->client->geocode($query, type: $type);
        }

        $key = 'motis_geocode_' . hash('sha256', $query . '|' . implode(',', $type));
        $item = $this->cache->getItem($key);

        if ($item->isHit()) {
            /** @var list<GeocodeMatch> $matches */
            return $item->get();
        }

        $matches = $this->client->geocode($query, type: $type);
        $item->set($matches);
        $item->expiresAfter($this->cacheTtl);
        $this->cache->save($item);

        return $matches;
    }

    /**
     * Starts a fluent route search.
     */
    public function route(): RouteSearch
    {
        return new RouteSearch($this);
    }

    /**
     * Returns the health status of the configured MOTIS server.
     */
    public function health(): HealthResponse
    {
        return $this->client->health();
    }
}
