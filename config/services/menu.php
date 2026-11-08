<?php

declare(strict_types=1);

use Guiziweb\SyliusAiPlatformPlugin\Menu\AiMenuListener;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

$services->set('guiziweb_sylius_ai_platform.menu.ai', AiMenuListener::class)
        ->tag('kernel.event_listener', ['event' => 'sylius.menu.admin.main']);
};
