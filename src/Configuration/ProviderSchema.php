<?php

declare(strict_types=1);

namespace Guiziweb\SyliusAiPlatformPlugin\Configuration;

final readonly class ProviderSchema
{
    /**
     * @param list<ProviderField> $fields
     */
    public function __construct(
        public string $type,
        public bool $configuredByName,
        public array $fields,
    ) {
    }
}
