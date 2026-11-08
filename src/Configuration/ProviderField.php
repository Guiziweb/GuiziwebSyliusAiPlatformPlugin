<?php

declare(strict_types=1);

namespace Guiziweb\SyliusAiPlatformPlugin\Configuration;

final readonly class ProviderField
{
    public function __construct(
        public string $name,
        public ProviderFieldType $type,
        public bool $required,
        public ?string $description,
    ) {
    }
}
