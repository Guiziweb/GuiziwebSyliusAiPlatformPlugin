<?php

declare(strict_types=1);

namespace Guiziweb\SyliusAiPlatformPlugin\Platform;

use Guiziweb\SyliusAiPlatformPlugin\Entity\Provider\ProviderInterface;
use Symfony\AI\Platform\ProviderInterface as AiProviderInterface;

final class AiProviderFactory implements AiProviderFactoryInterface
{
    /** @var array<int, array{settings: string, aiProvider: AiProviderInterface}> */
    private array $builtByProviderId = [];

    public function __construct(
        private readonly PlatformRecipeReader $recipeReader,
        private readonly PlatformRecipeExecutor $recipeExecutor,
    ) {
    }

    // Building a provider loads the AI bundle's extension, too slow to repeat on every call
    public function create(ProviderInterface $provider): AiProviderInterface
    {
        $id = $provider->getId();

        if (null === $id) {
            return $this->build($provider);
        }

        $settings = serialize([$provider->getCode(), $provider->getType(), $provider->getConfig()]);
        $built = $this->builtByProviderId[$id] ?? null;

        if (null !== $built && $built['settings'] === $settings) {
            return $built['aiProvider'];
        }

        $aiProvider = $this->build($provider);
        $this->builtByProviderId[$id] = ['settings' => $settings, 'aiProvider' => $aiProvider];

        return $aiProvider;
    }

    private function build(ProviderInterface $provider): AiProviderInterface
    {
        return $this->recipeExecutor->execute($this->recipeReader->read($provider));
    }
}
