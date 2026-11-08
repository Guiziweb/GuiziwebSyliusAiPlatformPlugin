<?php

declare(strict_types=1);

use Guiziweb\SyliusAiPlatformPlugin\Configuration\AiConfigurationSchema;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

$services->set('guiziweb_sylius_ai_platform.configuration.schema', AiConfigurationSchema::class);
    $services->alias(AiConfigurationSchema::class, 'guiziweb_sylius_ai_platform.configuration.schema');
};
