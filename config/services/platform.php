<?php

declare(strict_types=1);

use Guiziweb\SyliusAiPlatformPlugin\Platform\InstalledBridges;
use Guiziweb\SyliusAiPlatformPlugin\Platform\ProviderPlatformFactory;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

$services->set('guiziweb_sylius_ai_platform.platform.installed_bridges', InstalledBridges::class);

    $services->set('guiziweb_sylius_ai_platform.platform.factory', ProviderPlatformFactory::class)
        ->args([
            service('guiziweb_sylius_ai_platform.platform.installed_bridges'),
            service('http_client'),
            service('event_dispatcher'),
        ]);
};
