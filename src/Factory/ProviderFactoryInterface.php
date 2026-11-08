<?php

declare(strict_types=1);

namespace Guiziweb\SyliusAiPlatformPlugin\Factory;

use Guiziweb\SyliusAiPlatformPlugin\Entity\Provider\ProviderInterface;
use Sylius\Resource\Factory\FactoryInterface;

/**
 * @extends FactoryInterface<ProviderInterface>
 */
interface ProviderFactoryInterface extends FactoryInterface
{
    public function createForType(string $type): ProviderInterface;
}
