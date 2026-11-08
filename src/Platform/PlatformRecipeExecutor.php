<?php

declare(strict_types=1);

namespace Guiziweb\SyliusAiPlatformPlugin\Platform;

use Symfony\AI\Platform\PlatformInterface;
use Webmozart\Assert\Assert;

final class PlatformRecipeExecutor
{
    /**
     * @param array<string, object> $services the services a recipe may ask for, by service id
     */
    public function __construct(private readonly array $services)
    {
    }

    public function execute(PlatformRecipe $recipe): PlatformInterface
    {
        $arguments = $recipe->values;

        foreach ($recipe->services as $argument => $serviceId) {
            $arguments[$argument] = $this->services[$serviceId]
                ?? throw new \LogicException(sprintf('The recipe needs the service "%s", which is not available.', $serviceId));
        }

        $factory = [$recipe->factory, $recipe->method];
        Assert::isCallable($factory);

        $platform = $factory(...$arguments);
        Assert::isInstanceOf($platform, PlatformInterface::class);

        return $platform;
    }
}
