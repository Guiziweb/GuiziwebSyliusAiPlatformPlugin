<?php

declare(strict_types=1);

use Guiziweb\SyliusAiPlatformPlugin\Factory\AgentFactory;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('guiziweb_sylius_ai_platform.custom_factory.agent', AgentFactory::class)
        ->decorate('guiziweb_sylius_ai_platform.factory.agent')
        ->args([service('.inner')]);
};
