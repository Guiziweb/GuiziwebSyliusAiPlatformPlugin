<?php

declare(strict_types=1);

namespace Guiziweb\SyliusAiPlatformPlugin\Platform;

use Guiziweb\SyliusAiPlatformPlugin\Configuration\AiConfigurationSchema;
use Guiziweb\SyliusAiPlatformPlugin\Entity\Provider\ProviderInterface;
use Symfony\AI\AiBundle\AiBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\DependencyInjection\Reference;
use Webmozart\Assert\Assert;

final class PlatformRecipeReader
{
    private const BRIDGE_DEFAULT_SERVICE_PREFIXES = ['ai.platform.contract.', 'ai.platform.model_catalog.'];

    private const PROVIDER_FACTORY_METHOD = 'createProvider';

    private const ARGUMENTS_OF_THE_PLATFORM_ONLY = ['modelRouter'];

    public function __construct(
        private readonly AiConfigurationSchema $schema,
        private readonly ParameterBagInterface $parameters,
    ) {
    }

    public function read(ProviderInterface $provider): PlatformRecipe
    {
        $type = $provider->getType() ?? throw new \InvalidArgumentException('The provider has no type.');
        $code = $provider->getCode() ?? throw new \InvalidArgumentException('The provider has no code.');
        $schema = $this->schema->provider($type) ?? throw new \InvalidArgumentException(sprintf('Unknown AI provider type "%s".', $type));

        $config = array_filter($provider->getConfig(), static fn (mixed $value): bool => null !== $value && '' !== $value);
        $configKey = str_replace('-', '_', $code);
        $definition = $this->definitionOf(
            $type,
            $schema->configuredByName ? [$configKey => $config] : $config,
            $schema->configuredByName ? sprintf('ai.platform.%s.%s', $type, $configKey) : 'ai.platform.' . $type,
        );

        return $this->recipeOf($definition, $code);
    }

    /**
     * @param array<string, mixed> $platformConfig
     */
    private function definitionOf(string $type, array $platformConfig, string $serviceId): Definition
    {
        $container = new ContainerBuilder();

        foreach ($this->parameters->all() as $name => $value) {
            if (str_starts_with($name, 'kernel.')) {
                $container->setParameter($name, $value);
            }
        }

        $extension = (new AiBundle())->getContainerExtension() ?? throw new \LogicException('The AI bundle has no extension.');
        $extension->load([['platform' => [$type => $platformConfig]]], $container);

        return $container->getDefinition($serviceId);
    }

    private function recipeOf(Definition $definition, string $code): PlatformRecipe
    {
        $callable = $definition->getFactory();
        Assert::isArray($callable);
        [$factory, $method] = $callable;
        Assert::classExists($factory);
        Assert::string($method);

        $names = array_map(
            static fn (\ReflectionParameter $parameter): string => $parameter->getName(),
            (new \ReflectionMethod($factory, $method))->getParameters(),
        );
        $values = [];
        $services = [];
        $unsupported = [];

        foreach ($definition->getArguments() as $position => $argument) {
            $name = is_int($position) ? ($names[$position] ?? throw new \LogicException(sprintf('The recipe of %s has more arguments than the factory has parameters.', $factory))) : ltrim($position, '$');

            if ($argument instanceof Reference) {
                if (!$this->isBridgeDefault((string) $argument)) {
                    $services[$name] = (string) $argument;
                }
            } elseif ($argument instanceof Definition) {
                $unsupported[] = $name;
            } elseif (is_scalar($argument) || null === $argument || is_array($argument)) {
                $values[$name] = $argument;
            } else {
                throw new \LogicException(sprintf('The argument "%s" of %s is not supported.', $name, $factory));
            }
        }

        if (in_array('name', $names, true)) {
            $values['name'] ??= $code;
        }

        if (!method_exists($factory, self::PROVIDER_FACTORY_METHOD)) {
            throw new \LogicException(sprintf('The bridge factory %s has no %s() method.', $factory, self::PROVIDER_FACTORY_METHOD));
        }

        $providerArguments = array_flip(array_map(
            static fn (\ReflectionParameter $parameter): string => $parameter->getName(),
            (new \ReflectionMethod($factory, self::PROVIDER_FACTORY_METHOD))->getParameters(),
        ));

        $unsupported = array_keys(array_intersect_key(array_flip($unsupported), $providerArguments));

        if ([] !== $unsupported) {
            throw new \LogicException(sprintf('The argument "%s" of %s is built by the bundle and cannot be replayed.', $unsupported[0], $factory));
        }

        $unknown = array_diff_key([...$values, ...$services], $providerArguments, array_flip(self::ARGUMENTS_OF_THE_PLATFORM_ONLY));

        if ([] !== $unknown) {
            throw new \LogicException(sprintf('The argument "%s" of %s is not a parameter of %s().', array_key_first($unknown), $factory, self::PROVIDER_FACTORY_METHOD));
        }

        return new PlatformRecipe(
            $factory,
            self::PROVIDER_FACTORY_METHOD,
            array_intersect_key($values, $providerArguments),
            array_intersect_key($services, $providerArguments),
        );
    }

    private function isBridgeDefault(string $serviceId): bool
    {
        foreach (self::BRIDGE_DEFAULT_SERVICE_PREFIXES as $prefix) {
            if (str_starts_with($serviceId, $prefix)) {
                return true;
            }
        }

        return false;
    }
}
