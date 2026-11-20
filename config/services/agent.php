<?php

declare(strict_types=1);

use Guiziweb\SyliusAiPlatformPlugin\Agent\AgentRowReader;
use Guiziweb\SyliusAiPlatformPlugin\Agent\AgentRowReaderInterface;
use Guiziweb\SyliusAiPlatformPlugin\Factory\AgentFactory;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('guiziweb_sylius_ai_platform.custom_factory.agent', AgentFactory::class)
        ->decorate('guiziweb_sylius_ai_platform.factory.agent')
        ->args([service('.inner')]);

    $services->set('guiziweb_sylius_ai_platform.agent.row_reader', AgentRowReader::class)
        ->args([service('doctrine.orm.entity_manager'), param('guiziweb_sylius_ai_platform.model.agent.class')]);

    $services->alias(AgentRowReaderInterface::class, 'guiziweb_sylius_ai_platform.agent.row_reader');
};
