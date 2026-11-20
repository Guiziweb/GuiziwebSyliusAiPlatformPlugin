<?php

declare(strict_types=1);

namespace Guiziweb\SyliusAiPlatformPlugin\DependencyInjection\Compiler;

use Guiziweb\SyliusAiPlatformPlugin\Agent\AgentRowProcessor;
use Guiziweb\SyliusAiPlatformPlugin\DependencyInjection\GuiziwebSyliusAiPlatformExtension;
use Symfony\AI\Agent\MultiAgent\MultiAgent;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Reference;
use Webmozart\Assert\Assert;

final class AttachAgentRowProcessorPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasParameter(GuiziwebSyliusAiPlatformExtension::MANAGED_AGENTS_PARAMETER)) {
            return;
        }

        $managedAgents = $container->getParameter(GuiziwebSyliusAiPlatformExtension::MANAGED_AGENTS_PARAMETER);
        Assert::isList($managedAgents);
        Assert::allString($managedAgents);

        $agentServiceIds = $this->agentServiceIdsByName($container);

        foreach ($managedAgents as $agentName) {
            $agentServiceId = $agentServiceIds[$agentName] ?? throw new InvalidArgumentException(sprintf(
                'The agent "%s" is listed in "guiziweb_sylius_ai_platform.agents" but is not declared in the "ai.agent" configuration.',
                $agentName,
            ));

            if (MultiAgent::class === $container->getDefinition($agentServiceId)->getClass()) {
                throw new InvalidArgumentException(sprintf('The agent "%s" is a multi-agent, whose prompt and model cannot be managed in the admin.', $agentName));
            }

            $container->setDefinition(
                'guiziweb_sylius_ai_platform.agent.row_processor.' . $agentName,
                (new Definition(AgentRowProcessor::class, [
                    $agentName,
                    new Reference('guiziweb_sylius_ai_platform.agent.row_reader'),
                ]))->addTag('ai.agent.input_processor', ['agent' => $agentServiceId, 'priority' => -10]),
            );
        }
    }

    /** @return array<string, string> */
    private function agentServiceIdsByName(ContainerBuilder $container): array
    {
        $serviceIds = [];

        foreach ($container->findTaggedServiceIds('ai.agent') as $serviceId => $tags) {
            $name = $tags[0]['name'] ?? null;

            if (\is_string($name)) {
                $serviceIds[$name] = $serviceId;
            }
        }

        return $serviceIds;
    }
}
