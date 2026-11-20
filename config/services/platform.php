<?php

declare(strict_types=1);

use Guiziweb\SyliusAiPlatformPlugin\Platform\AiProviderFactory;
use Guiziweb\SyliusAiPlatformPlugin\Platform\AiProviderFactoryInterface;
use Guiziweb\SyliusAiPlatformPlugin\Platform\ModelNames;
use Guiziweb\SyliusAiPlatformPlugin\Platform\ModelNamesInterface;
use Guiziweb\SyliusAiPlatformPlugin\Platform\PlatformRecipeExecutor;
use Guiziweb\SyliusAiPlatformPlugin\Platform\PlatformRecipeReader;
use Guiziweb\SyliusAiPlatformPlugin\Platform\ProviderRoutingListener;
use Symfony\AI\Platform\Event\ModelRoutingEvent;
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

    $services->set('guiziweb_sylius_ai_platform.platform.ai_provider_factory', AiProviderFactory::class)
        ->args([
            service('guiziweb_sylius_ai_platform.platform.recipe_reader'),
            service('guiziweb_sylius_ai_platform.platform.recipe_executor'),
        ]);

    $services->alias(AiProviderFactoryInterface::class, 'guiziweb_sylius_ai_platform.platform.ai_provider_factory');

    $services->set('guiziweb_sylius_ai_platform.platform.model_names', ModelNames::class)
        ->args([service('guiziweb_sylius_ai_platform.platform.ai_provider_factory'), service('logger')->nullOnInvalid()]);

    $services->alias(ModelNamesInterface::class, 'guiziweb_sylius_ai_platform.platform.model_names');

    $services->set('guiziweb_sylius_ai_platform.platform.provider_routing_listener', ProviderRoutingListener::class)
        ->args([
            service('guiziweb_sylius_ai_platform.platform.ai_provider_factory'),
            service('guiziweb_sylius_ai_platform.repository.provider'),
        ])
        ->tag('kernel.event_listener', ['event' => ModelRoutingEvent::class]);
};
