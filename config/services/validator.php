<?php

declare(strict_types=1);

use Guiziweb\SyliusAiPlatformPlugin\Validator\KnownProviderTypeValidator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

$services->set('guiziweb_sylius_ai_platform.validator.known_provider_type', KnownProviderTypeValidator::class)
        ->args([service('guiziweb_sylius_ai_platform.platform.installed_bridges')])
        ->tag('validator.constraint_validator');
};
