<?php

declare(strict_types=1);

namespace Sneakyx\MotisSymfonyClient\Tests\Unit;

use Psr\Cache\CacheItemPoolInterface;
use Sneakyx\MotisPhpClient\MotisClient;
use Sneakyx\MotisSymfonyClient\MotisManager;
use Sneakyx\MotisSymfonyClient\Tests\BundleTestCase;

final class BundleWiringTest extends BundleTestCase
{
    protected function setUp(): void
    {
        self::$cacheEnabled = true;
    }

    public function testServicesAreRegistered(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        self::assertInstanceOf(MotisClient::class, $container->get('motis.client'));
        self::assertInstanceOf(MotisManager::class, $container->get('motis.manager'));
        self::assertSame('https://motis.test', $container->getParameter('motis.base_url'));
    }

    public function testManagerGetsTheCachePoolFromTheContainer(): void
    {
        self::bootKernel();

        $manager = self::getContainer()->get('motis.manager');
        $property = new \ReflectionProperty(MotisManager::class, 'cache');

        self::assertInstanceOf(CacheItemPoolInterface::class, $property->getValue($manager));
    }

    public function testCacheCanBeDisabledViaConfig(): void
    {
        self::$cacheEnabled = false;
        self::bootKernel();

        $manager = self::getContainer()->get('motis.manager');
        $property = new \ReflectionProperty(MotisManager::class, 'cache');

        self::assertNull($property->getValue($manager));
    }
}
