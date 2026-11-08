<?php

declare(strict_types=1);

namespace Guiziweb\SyliusAiPlatformPlugin\Platform;

use Guiziweb\SyliusAiPlatformPlugin\Entity\Provider\ProviderInterface;
use Symfony\AI\Platform\PlatformInterface;

final class ProviderPlatformFactory implements ProviderPlatformFactoryInterface
{
    public function __construct(
        private readonly PlatformRecipeReader $recipeReader,
        private readonly PlatformRecipeExecutor $recipeExecutor,
    ) {
    }

    public function create(ProviderInterface $provider): PlatformInterface
    {
        return $this->recipeExecutor->execute($this->recipeReader->read($provider));
    }
}
