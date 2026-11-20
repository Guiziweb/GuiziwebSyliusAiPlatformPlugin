<?php

declare(strict_types=1);

use Guiziweb\SyliusAiPlatformPlugin\Form\Type\AgentType;
use Guiziweb\SyliusAiPlatformPlugin\Grid\AgentGrid;
use Guiziweb\SyliusAiPlatformPlugin\Grid\AgentGridInterface;
use Guiziweb\SyliusAiPlatformPlugin\Platform\ChatModelNames;
use Guiziweb\SyliusAiPlatformPlugin\Platform\ChatModelNamesInterface;
use Sylius\Bundle\UiBundle\Twig\Component\ResourceFormComponent;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('guiziweb_sylius_ai_platform.platform.chat_model_names', ChatModelNames::class)
        ->args([service('guiziweb_sylius_ai_platform.platform.ai_provider_factory'), service('logger')->nullOnInvalid()]);

    $services->alias(ChatModelNamesInterface::class, 'guiziweb_sylius_ai_platform.platform.chat_model_names');

    $services->set('guiziweb_sylius_ai_platform.form.type.agent', AgentType::class)
        ->args([
            param('guiziweb_sylius_ai_platform.model.provider.class'),
            service('guiziweb_sylius_ai_platform.repository.provider'),
            service('guiziweb_sylius_ai_platform.platform.chat_model_names'),
        ])
        ->tag('form.type');

    $services->set('guiziweb_sylius_ai_platform.twig.component.agent.form', ResourceFormComponent::class)
        ->args([
            service('guiziweb_sylius_ai_platform.repository.agent'),
            service('form.factory'),
            '%guiziweb_sylius_ai_platform.model.agent.class%',
            AgentType::class,
        ])
        ->tag('sylius.live_component.admin', ['key' => 'guiziweb_sylius_ai_platform:agent:form']);

    $services->set('guiziweb_sylius_ai_platform.grid.agent', AgentGrid::class)
        ->tag('sylius.invokable_grid');

    $services->alias(AgentGridInterface::class, 'guiziweb_sylius_ai_platform.grid.agent');
};
