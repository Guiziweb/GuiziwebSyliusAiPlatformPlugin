<?php

declare(strict_types=1);

namespace Guiziweb\SyliusAiPlatformPlugin\Configuration;

use Symfony\AI\AiBundle\AiBundle;
use Symfony\Component\Config\Definition\ArrayNode;
use Symfony\Component\Config\Definition\BaseNode;
use Symfony\Component\Config\Definition\BooleanNode;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\IntegerNode;
use Symfony\Component\Config\Definition\NodeInterface;
use Symfony\Component\Config\Definition\PrototypedArrayNode;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ConfigurationExtensionInterface;

final class AiConfigurationSchema
{
    private const PROVIDERS_WITH_SEVERAL_FACTORIES = ['azure'];

    private const FIELDS_EDITED_ELSEWHERE = ['http_client', 'model_catalog'];

    /** @var list<ProviderSchema>|null */
    private ?array $providers = null;

    public function provider(string $code): ?ProviderSchema
    {
        foreach ($this->providers() as $provider) {
            if ($provider->code === $code) {
                return $provider;
            }
        }

        return null;
    }

    /**
     * @return list<ProviderSchema>
     */
    public function providers(): array
    {
        return $this->providers ??= $this->readProviders();
    }

    /**
     * @return list<ProviderSchema>
     */
    private function readProviders(): array
    {
        $providers = [];

        foreach ($this->platformNode()->getChildren() as $code => $node) {
            $children = $this->childrenOf($node);

            if (in_array($code, self::PROVIDERS_WITH_SEVERAL_FACTORIES, true) || $this->wrapsAnotherPlatform($children)) {
                continue;
            }

            $providers[] = new ProviderSchema($code, $this->fieldsOf($children));
        }

        return $providers;
    }

    private function platformNode(): ArrayNode
    {
        $extension = (new AiBundle())->getContainerExtension();

        if (!$extension instanceof ConfigurationExtensionInterface) {
            throw new \LogicException('The AI bundle does not expose its configuration.');
        }

        $configuration = $extension->getConfiguration([], new ContainerBuilder());

        if (!$configuration instanceof ConfigurationInterface) {
            throw new \LogicException('The AI bundle does not expose its configuration.');
        }

        $root = $configuration->getConfigTreeBuilder()->buildTree();
        $platform = $root instanceof ArrayNode ? ($root->getChildren()['platform'] ?? null) : null;

        if (!$platform instanceof ArrayNode) {
            throw new \LogicException('The AI bundle does not describe its platforms.');
        }

        return $platform;
    }

    /**
     * @return array<string, NodeInterface>
     */
    private function childrenOf(NodeInterface $providerNode): array
    {
        return match (true) {
            $providerNode instanceof PrototypedArrayNode && $providerNode->getPrototype() instanceof ArrayNode => $providerNode->getPrototype()->getChildren(),
            $providerNode instanceof ArrayNode => $providerNode->getChildren(),
            default => [],
        };
    }

    /**
     * @param array<string, NodeInterface> $children
     */
    private function wrapsAnotherPlatform(array $children): bool
    {
        return isset($children['platform']) || isset($children['platforms']);
    }

    /**
     * @param array<string, NodeInterface> $children
     *
     * @return list<ProviderField>
     */
    private function fieldsOf(array $children): array
    {
        $fields = [];

        foreach ($children as $name => $child) {
            if (in_array($name, self::FIELDS_EDITED_ELSEWHERE, true) || !$child instanceof BaseNode) {
                continue;
            }

            $info = $child->getInfo();
            $fields[] = new ProviderField($name, $this->typeOf($child), $child->isRequired(), is_string($info) && '' !== $info ? $info : null);
        }

        return $fields;
    }

    private function typeOf(BaseNode $node): string
    {
        return match (true) {
            $node instanceof BooleanNode => 'bool',
            $node instanceof IntegerNode => 'int',
            default => 'string',
        };
    }
}
