<?php

declare(strict_types=1);

namespace Guiziweb\SyliusAiPlatformPlugin\Factory;

use Guiziweb\SyliusAiPlatformPlugin\Entity\Agent\AgentInterface;
use Sylius\Resource\Factory\FactoryInterface;

final class AgentFactory implements AgentFactoryInterface
{
    /**
     * @param FactoryInterface<AgentInterface> $decoratedFactory
     */
    public function __construct(private readonly FactoryInterface $decoratedFactory)
    {
    }

    public function createNew(): AgentInterface
    {
        $agent = $this->decoratedFactory->createNew();
        assert($agent instanceof AgentInterface);

        return $agent;
    }

    public function createDeclared(string $code, string $plugin, string $name, string $defaultPrompt): AgentInterface
    {
        $agent = $this->createNew();
        $agent->setCode($code);
        $agent->refreshDeclaration($plugin, $name, $defaultPrompt);

        return $agent;
    }
}
