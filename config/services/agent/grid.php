<?php

declare(strict_types=1);

use Guiziweb\SyliusAiPlatformPlugin\Grid\AgentGrid;
use Guiziweb\SyliusAiPlatformPlugin\Grid\AgentGridInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('guiziweb_sylius_ai_platform.grid.agent', AgentGrid::class)
        ->tag('sylius.invokable_grid');

    $services->alias(AgentGridInterface::class, 'guiziweb_sylius_ai_platform.grid.agent');
};
