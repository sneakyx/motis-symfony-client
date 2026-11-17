<?php

declare(strict_types=1);

namespace Sneakyx\MotisSymfonyClient;

use Sneakyx\MotisSymfonyClient\DependencyInjection\MotisSymfonyClientExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

final class MotisSymfonyClientBundle extends Bundle
{
    /**
     * The extension uses the short "motis" alias, which differs from the
     * default underscored bundle alias, so it is returned explicitly.
     */
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new MotisSymfonyClientExtension();
    }
}
