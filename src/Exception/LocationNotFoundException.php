<?php

declare(strict_types=1);

namespace Sneakyx\MotisSymfonyClient\Exception;

use RuntimeException;

/**
 * Thrown when a place name cannot be geocoded during a route search.
 */
final class LocationNotFoundException extends RuntimeException
{
}
