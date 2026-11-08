<?php

declare(strict_types=1);

namespace Guiziweb\SyliusAiPlatformPlugin\Platform;

final readonly class BridgeField
{
    public function __construct(
        public string $name,
        public BridgeFieldType $type,
        public bool $required,
        public bool $secret,
        public string|int|float|bool|null $default,
    ) {
    }
}
