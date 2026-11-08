<?php

declare(strict_types=1);

use Guiziweb\SyliusAiPlatformPlugin\Factory\ProviderFactory;
use Guiziweb\SyliusAiPlatformPlugin\Twig\ProviderTypesExtension;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('guiziweb_sylius_ai_platform.custom_factory.provider', ProviderFactory::class)
        ->decorate('guiziweb_sylius_ai_platform.factory.provider')
        ->args([service('.inner'), service('guiziweb_sylius_ai_platform.configuration.schema')]);

    $services->set('guiziweb_sylius_ai_platform.twig.extension.provider_types', ProviderTypesExtension::class)
        ->args([service('guiziweb_sylius_ai_platform.configuration.schema')])
        ->tag('twig.extension');
};
