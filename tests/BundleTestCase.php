<?php

declare(strict_types=1);

namespace Sneakyx\MotisSymfonyClient\Tests;

use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\Response;
use Sneakyx\MotisPhpClient\MotisClient;
use Sneakyx\MotisSymfonyClient\Tests\Support\MockHttpClient;
use Sneakyx\MotisSymfonyClient\Tests\Support\MotisTestKernel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Boots the test kernel and builds mocked MotisClient instances.
 */
abstract class BundleTestCase extends KernelTestCase
{
    public static bool $cacheEnabled = true;

    protected static function createKernel(array $options = []): KernelInterface
    {
        return new MotisTestKernel(self::$cacheEnabled);
    }

    /**
     * Builds a real MotisClient on top of a mock PSR-18 client that replays
     * the given fixture files in order.
     *
     * @param list<string> $fixtures
     * @return array{0: MotisClient, 1: MockHttpClient}
     */
    protected function makeClient(array $fixtures): array
    {
        $responses = [];
        foreach ($fixtures as $fixture) {
            $body = (string) file_get_contents(__DIR__ . '/Fixtures/' . $fixture);
            $responses[] = new Response(200, ['Content-Type' => 'application/json'], $body);
        }

        $http = new MockHttpClient(...$responses);

        return [
            new MotisClient('https://motis.test', $http, new HttpFactory()),
            $http,
        ];
    }
}
