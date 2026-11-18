# motis-symfony-client

A Symfony integration for [MOTIS](https://motis-project.de), built on top of
[motis-php-client](https://github.com/sneakyx/motis-php-client). It adds
Symfony-flavoured ergonomics to the plain php client: a bundle with container
configuration, PSR-6 geocode caching and a fluent route search.

## Features

- Bundle with a config tree (`base_url`, geocode cache: `enabled`/`pool`/`ttl`)
- `MotisManager` service with `geocode()`, `route()` and `health()`
- Fluent route search that auto-geocodes place names:
  `$manager->route()->from('Aachen Hbf')->to('Aachen Rothe Erde')->at($time)->get()`
- Geocode results cached through a PSR-6 pool (e.g. `cache.app`)
- `ItineraryCollection` return type with `first()`, `all()` and `fastest()`

## Requirements

- PHP 8.2+
- Symfony 7.4 or 8.x
- A PSR-6 cache pool when geocode caching is enabled (e.g. `cache.app` from the framework)

## Installation

```bash
composer require sneakyx/motis-symfony-client
```

Register the bundle if your application does not use Symfony Flex:

```php
// config/bundles.php
return [
    // ...
    Sneakyx\MotisSymfonyClient\MotisSymfonyClientBundle::class => ['all' => true],
];
```

## Configuration

```yaml
# config/packages/motis.yaml
motis:
    base_url: 'https://api.transitous.org'
    geocode_cache:
        enabled: true
        pool: cache.app
        ttl: 86400
```

When caching is disabled (or the configured pool does not exist), geocode
requests always hit the MOTIS server.

## Usage

```php
use Sneakyx\MotisSymfonyClient\MotisManager;

/** @var MotisManager $manager */
$itineraries = $manager
    ->route()
    ->from('Aachen Hbf')
    ->to('Aachen Rothe Erde')
    ->at('2026-11-18T10:00:00+01:00')
    ->get();

foreach ($itineraries as $itinerary) {
    // ...
}

$fastest = $itineraries->fastest();

// Raw access to the underlying php client (stops, stop times, trips, ...):
$client = $manager->client();

// Server health:
$health = $manager->health();
```

Place inputs accept plain `lat,lon` strings, place names (geocoded
automatically) and `GeocodeMatch` objects from the php client.

## Tests

```bash
composer test
```

## License

MIT