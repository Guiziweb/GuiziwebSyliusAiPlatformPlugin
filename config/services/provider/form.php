<?php

declare(strict_types=1);

use Guiziweb\SyliusAiPlatformPlugin\Form\Type\ProviderConfigurationType;
use Guiziweb\SyliusAiPlatformPlugin\Form\Type\ProviderType;
use Sylius\Bundle\UiBundle\Twig\Component\ResourceFormComponent;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

$services->set('guiziweb_sylius_ai_platform.form.type.provider', ProviderType::class)
        ->tag('form.type');

    $services->set('guiziweb_sylius_ai_platform.form.type.provider_configuration', ProviderConfigurationType::class)
        ->args([service('guiziweb_sylius_ai_platform.configuration.schema')])
        ->tag('form.type');

    $services->set('guiziweb_sylius_ai_platform.twig.component.provider.form', ResourceFormComponent::class)
        ->args([
            service('guiziweb_sylius_ai_platform.repository.provider'),
            service('form.factory'),
            '%guiziweb_sylius_ai_platform.model.provider.class%',
            ProviderType::class,
        ])
        ->tag('sylius.twig_component', ['key' => 'guiziweb_sylius_ai_platform:provider:form']);
};
