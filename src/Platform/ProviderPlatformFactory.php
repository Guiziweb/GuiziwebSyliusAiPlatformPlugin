<?php

declare(strict_types=1);

namespace Guiziweb\SyliusAiPlatformPlugin\Platform;

use Guiziweb\SyliusAiPlatformPlugin\Entity\Provider\ProviderInterface;
use Symfony\AI\Platform\PlatformInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Webmozart\Assert\Assert;

final class ProviderPlatformFactory
{
    public function __construct(
        private readonly InstalledBridges $bridges,
        private readonly HttpClientInterface $httpClient,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function create(ProviderInterface $provider): PlatformInterface
    {
        $type = $provider->getType() ?? throw new \InvalidArgumentException('The connection has no provider type.');
        $code = $provider->getCode() ?? throw new \InvalidArgumentException('The connection has no code.');

        $factory = $this->bridges->factoryOf($type)
            ?? throw new \RuntimeException(sprintf('No Symfony AI bridge is installed for "%s". Install the matching "symfony/ai-*-platform" package.', $type));

        $platform = $factory::createPlatform(...$this->argumentsFor($factory, $code, $provider->getConfig()));
        Assert::isInstanceOf($platform, PlatformInterface::class);

        return $platform;
    }

    /**
     * @param class-string        $factory
     * @param array<string, mixed> $config
     *
     * @return array<string, mixed>
     */
    private function argumentsFor(string $factory, string $code, array $config): array
    {
        $arguments = ['httpClient' => $this->httpClient, 'eventDispatcher' => $this->eventDispatcher, 'name' => $code];

        foreach ($config as $field => $value) {
            if (null !== $value && '' !== $value) {
                $arguments[lcfirst(str_replace('_', '', ucwords($field, '_')))] = $value;
            }
        }

        $accepted = array_map(
            static fn (\ReflectionParameter $parameter): string => $parameter->getName(),
            (new \ReflectionMethod($factory, 'createPlatform'))->getParameters(),
        );

        return array_intersect_key($arguments, array_flip($accepted));
    }
}
