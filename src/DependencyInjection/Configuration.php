<?php

declare(strict_types=1);

namespace Sneakyx\MotisSymfonyClient\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Validates the configuration under the "motis" root node.
 */
final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('motis');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->scalarNode('base_url')
                    ->info('Base URL of the MOTIS server.')
                    ->defaultValue('https://api.transitous.org')
                    ->cannotBeEmpty()
                ->end()
                ->arrayNode('geocode_cache')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')
                            ->info('Whether geocode results are cached.')
                            ->defaultTrue()
                        ->end()
                        ->scalarNode('pool')
                            ->info('PSR-6 cache pool service id used for geocode results.')
                            ->defaultValue('cache.app')
                        ->end()
                        ->integerNode('ttl')
                            ->info('Cache lifetime of geocode results, in seconds.')
                            ->defaultValue(86400)
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
