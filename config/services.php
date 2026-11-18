<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Sneakyx\MotisPhpClient\MotisClient;
use Sneakyx\MotisSymfonyClient\MotisManager;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('motis.client', MotisClient::class)
        ->args(['%motis.base_url%']);

    $services->alias(MotisClient::class, 'motis.client');

    $services->set('motis.manager', MotisManager::class)
        ->public()
        ->args([service('motis.client')]);

    $services->alias(MotisManager::class, 'motis.manager');
};
