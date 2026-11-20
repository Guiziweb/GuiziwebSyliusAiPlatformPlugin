<?php

declare(strict_types=1);

namespace Tests\Guiziweb\SyliusAiPlatformPlugin\Unit\DependencyInjection;

use Guiziweb\SyliusAiPlatformPlugin\DependencyInjection\Compiler\AttachAgentRowProcessorPass;
use Guiziweb\SyliusAiPlatformPlugin\DependencyInjection\GuiziwebSyliusAiPlatformExtension;
use PHPUnit\Framework\TestCase;
use Symfony\AI\Agent\Agent;
use Symfony\AI\Agent\MultiAgent\MultiAgent;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

final class AttachAgentRowProcessorPassTest extends TestCase
{
    public function test_a_managed_agent_gets_the_row_processor_before_the_bundle_system_prompt(): void
    {
        $container = self::containerWithAgents(managed: ['shopping_assistant']);

        (new AttachAgentRowProcessorPass())->process($container);

        $processor = $container->getDefinition('guiziweb_sylius_ai_platform.agent.row_processor.shopping_assistant');
        self::assertSame([['agent' => 'ai.agent.shopping_assistant', 'priority' => -10]], $processor->getTag('ai.agent.input_processor'));
        self::assertSame('shopping_assistant', $processor->getArgument(0));
    }

    public function test_an_agent_that_is_not_managed_is_left_untouched(): void
    {
        $container = self::containerWithAgents(managed: ['shopping_assistant']);

        (new AttachAgentRowProcessorPass())->process($container);

        self::assertFalse($container->hasDefinition('guiziweb_sylius_ai_platform.agent.row_processor.support'));
    }

    public function test_a_managed_agent_missing_from_the_ai_configuration_is_refused(): void
    {
        $container = self::containerWithAgents(managed: ['unknown_assistant']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The agent "unknown_assistant" is listed in "guiziweb_sylius_ai_platform.agents" but is not declared');

        (new AttachAgentRowProcessorPass())->process($container);
    }

    public function test_a_managed_multi_agent_is_refused(): void
    {
        $container = self::containerWithAgents(managed: ['orchestrator']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('multi-agent');

        (new AttachAgentRowProcessorPass())->process($container);
    }

    /**
     * @param list<string> $managed
     */
    private static function containerWithAgents(array $managed): ContainerBuilder
    {
        $container = new ContainerBuilder();
        $container->setParameter(GuiziwebSyliusAiPlatformExtension::MANAGED_AGENTS_PARAMETER, $managed);

        foreach (['shopping_assistant' => Agent::class, 'support' => Agent::class, 'orchestrator' => MultiAgent::class] as $name => $class) {
            $container->setDefinition('ai.agent.' . $name, (new Definition($class))->addTag('ai.agent', ['name' => $name]));
        }

        return $container;
    }
}
