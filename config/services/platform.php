<?php

declare(strict_types=1);

use Guiziweb\SyliusAiPlatformPlugin\Platform\PlatformRecipeExecutor;
use Guiziweb\SyliusAiPlatformPlugin\Platform\PlatformRecipeReader;
use Guiziweb\SyliusAiPlatformPlugin\Platform\ProviderPlatformFactory;
use Guiziweb\SyliusAiPlatformPlugin\Platform\ProviderPlatformFactoryInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('guiziweb_sylius_ai_platform.platform.recipe_reader', PlatformRecipeReader::class)
        ->args([
            service('guiziweb_sylius_ai_platform.configuration.schema'),
            service('parameter_bag'),
        ]);

    $services->set('guiziweb_sylius_ai_platform.platform.recipe_executor', PlatformRecipeExecutor::class)
        ->args([[
            'http_client' => service('http_client'),
            'event_dispatcher' => service('event_dispatcher'),
        ]]);

    $services->set('guiziweb_sylius_ai_platform.platform.factory', ProviderPlatformFactory::class)
        ->args([
            service('guiziweb_sylius_ai_platform.platform.recipe_reader'),
            service('guiziweb_sylius_ai_platform.platform.recipe_executor'),
        ]);

    $services->alias(ProviderPlatformFactoryInterface::class, 'guiziweb_sylius_ai_platform.platform.factory');
};
