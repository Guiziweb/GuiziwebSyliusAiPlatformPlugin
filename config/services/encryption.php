<?php

declare(strict_types=1);

use Guiziweb\SyliusAiPlatformPlugin\Encryption\ProviderConfigEncrypter;
use Sylius\Bundle\PaymentBundle\Listener\EntityEncryptionListener;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

$services->set('guiziweb_sylius_ai_platform.encrypter.provider', ProviderConfigEncrypter::class)
        ->args([service('sylius.encrypter')]);

    $services->set('guiziweb_sylius_ai_platform.listener.provider_encryption', EntityEncryptionListener::class)
        ->args([
            service('guiziweb_sylius_ai_platform.encrypter.provider'),
            '%guiziweb_sylius_ai_platform.model.provider.class%',
        ])
        ->tag('doctrine.event_listener', ['event' => 'onFlush'])
        ->tag('doctrine.event_listener', ['event' => 'postFlush'])
        ->tag('doctrine.event_listener', ['event' => 'postLoad']);
};
