<?php

declare(strict_types=1);

namespace Sneakyx\MotisSymfonyClient\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Loads the bundle configuration and wires the services.
 *
 * Services are defined in PHP instead of XML: the XML loader was removed
 * in Symfony 8, so the PHP format keeps the bundle compatible with both
 * Symfony 7.4 and 8.x.
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

        (new PhpFileLoader($container, new FileLocator(__DIR__ . '/../../config')))->load('services.php');

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
