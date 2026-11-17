<?php

declare(strict_types=1);

namespace Sneakyx\MotisSymfonyClient\Tests\Support;

use Sneakyx\MotisSymfonyClient\MotisSymfonyClientBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

/**
 * Minimal kernel used by the bundle tests: FrameworkBundle plus the
 * MOTIS bundle, with a predictable configuration.
 */
final class MotisTestKernel extends Kernel
{
    use MicroKernelTrait;

    public function __construct(private readonly bool $cacheEnabled)
    {
        parent::__construct('test', false);
    }

    /**
     * The cache directory encodes the kernel configuration so that boots
     * with different settings never reuse each other's compiled container.
     */
    public function getCacheDir(): string
    {
        return \sys_get_temp_dir() . '/motis-test-kernel/' . $this->environment
            . '/' . ($this->cacheEnabled ? 'cache' : 'nocache');
    }

    public function registerBundles(): iterable
    {
        return [
            new FrameworkBundle(),
            new MotisSymfonyClientBundle(),
        ];
    }

    private function configureContainer(ContainerConfigurator $container): void
    {
        $container->extension('framework', [
            'test' => true,
            'secret' => 'MotisTestKernel',
        ]);

        $container->extension('motis', [
            'base_url' => 'https://motis.test',
            'geocode_cache' => ['enabled' => $this->cacheEnabled],
        ]);
    }

    private function configureRoutes(RoutingConfigurator $routes): void
    {
    }
}
