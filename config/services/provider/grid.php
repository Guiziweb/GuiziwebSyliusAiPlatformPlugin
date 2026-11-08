<?php

declare(strict_types=1);

use Guiziweb\SyliusAiPlatformPlugin\Grid\ProviderGrid;
use Guiziweb\SyliusAiPlatformPlugin\Grid\ProviderGridInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('guiziweb_sylius_ai_platform.grid.provider', ProviderGrid::class)
        ->args([service('guiziweb_sylius_ai_platform.configuration.schema')])
        ->tag('sylius.invokable_grid');

    $services->alias(ProviderGridInterface::class, 'guiziweb_sylius_ai_platform.grid.provider');
};
