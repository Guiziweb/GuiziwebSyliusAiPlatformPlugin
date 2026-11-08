<?php

declare(strict_types=1);

namespace Guiziweb\SyliusAiPlatformPlugin\Configuration;

final readonly class ProviderField
{
    /**
     * @param list<string> $choices
     */
    public function __construct(
        public string $name,
        public ProviderFieldType $type,
        public bool $required,
        public ?string $description,
        public array $choices = [],
    ) {
    }
}
