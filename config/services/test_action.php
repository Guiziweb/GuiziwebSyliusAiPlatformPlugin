<?php

declare(strict_types=1);

use Guiziweb\SyliusAiPlatformPlugin\Controller\TestProviderAction;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

$services->set('guiziweb_sylius_ai_platform.controller.test_provider', TestProviderAction::class)
        ->args([
            service('guiziweb_sylius_ai_platform.repository.provider'),
            service('guiziweb_sylius_ai_platform.platform.factory'),
        ])
        ->call('setContainer', [service(Psr\Container\ContainerInterface::class)])
        ->tag('container.service_subscriber')
        ->tag('controller.service_arguments')
        ->public();
};
