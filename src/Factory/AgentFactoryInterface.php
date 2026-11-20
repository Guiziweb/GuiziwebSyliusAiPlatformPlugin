<?php

declare(strict_types=1);

namespace Guiziweb\SyliusAiPlatformPlugin\Factory;

use Guiziweb\SyliusAiPlatformPlugin\Entity\Agent\AgentInterface;
use Sylius\Resource\Factory\FactoryInterface;

/**
 * @extends FactoryInterface<AgentInterface>
 */
interface AgentFactoryInterface extends FactoryInterface
{
    public function createDeclared(string $code, string $plugin, string $name, string $defaultPrompt): AgentInterface;
}
