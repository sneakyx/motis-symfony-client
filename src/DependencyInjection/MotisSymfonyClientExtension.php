<?php

declare(strict_types=1);

namespace Sneakyx\MotisSymfonyClient\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Loads the bundle configuration and wires the services.
 *
 * The cache pool may not exist yet while extensions load (framework pools
 * are created later during compilation), so the reference uses the
 * ignore-on-invalid behavior and resolves to null when the pool is
 * missing.
 */
final class MotisSymfonyClientExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $container->setParameter('motis.base_url', $config['base_url']);

        (new XmlFileLoader($container, new FileLocator(__DIR__ . '/../../config')))->load('services.xml');

        $cacheEnabled = (bool) $config['geocode_cache']['enabled'];
        $poolId = (string) $config['geocode_cache']['pool'];

        $container->getDefinition('motis.manager')
            ->setArgument(
                '$cache',
                $cacheEnabled && $poolId !== ''
                    ? new Reference($poolId, ContainerBuilder::IGNORE_ON_INVALID_REFERENCE)
                    : null,
            )
            ->setArgument('$cacheEnabled', $cacheEnabled && $poolId !== '')
            ->setArgument('$cacheTtl', (int) $config['geocode_cache']['ttl']);
    }

    public function getAlias(): string
    {
        return 'motis';
    }
}
