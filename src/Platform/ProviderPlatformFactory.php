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
        $package = $provider->getType() ?? throw new \InvalidArgumentException('The connection has no bridge.');
        $code = $provider->getCode() ?? throw new \InvalidArgumentException('The connection has no code.');

        $bridge = $this->bridges->get($package)
            ?? throw new \RuntimeException(sprintf('The bridge "%s" is not installed or cannot be used. Install it with "composer require %s".', $package, $package));

        $platform = $bridge->factory::createPlatform(...$this->argumentsFor($bridge, $code, $provider->getConfig()));
        Assert::isInstanceOf($platform, PlatformInterface::class);

        return $platform;
    }

    /**
     * @param array<string, mixed> $config
     *
     * @return array<string, mixed>
     */
    private function argumentsFor(Bridge $bridge, string $code, array $config): array
    {
        $arguments = array_intersect_key(
            ['httpClient' => $this->httpClient, 'eventDispatcher' => $this->eventDispatcher, 'name' => $code],
            array_flip($bridge->parameters),
        );

        foreach ($config as $name => $value) {
            if (null === $bridge->field($name)) {
                throw new \InvalidArgumentException(sprintf('"%s" is not a setting of %s.', $name, $bridge->package));
            }

            if (null !== $value && '' !== $value) {
                $arguments[$name] = $value;
            }
        }

        foreach ($bridge->fields as $field) {
            if ($field->required && !isset($arguments[$field->name])) {
                throw new \InvalidArgumentException(sprintf('"%s" is required by %s.', $field->name, $bridge->package));
            }
        }

        return $arguments;
    }
}
