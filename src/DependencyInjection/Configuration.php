<?php

declare(strict_types=1);

namespace Guiziweb\SyliusAiPlatformPlugin\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('guiziweb_sylius_ai_platform');

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('agents')
                    ->info('Names of the agents of the "ai" configuration whose prompt, provider and model are managed in the admin. Their prompt comes from the database: do not give them a "prompt" in the "ai" configuration, it would be ignored. Other agents are left untouched.')
                    ->scalarPrototype()->cannotBeEmpty()->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
