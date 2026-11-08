<?php

declare(strict_types=1);

namespace Guiziweb\SyliusAiPlatformPlugin\Platform;

final readonly class PlatformRecipe
{
    /**
     * @param class-string         $factory
     * @param array<string, mixed> $values   factory arguments given as plain values
     * @param array<string, string> $services factory arguments given as services, argument name to service id
     */
    public function __construct(
        public string $factory,
        public string $method,
        public array $values,
        public array $services,
    ) {
    }
}
