<?php

declare(strict_types=1);

namespace Guiziweb\SyliusAiPlatformPlugin\Platform;

final readonly class Bridge
{
    /**
     * @param class-string      $factory
     * @param list<BridgeField> $fields
     * @param list<string>      $parameters
     */
    public function __construct(
        public string $package,
        public string $factory,
        public array $fields,
        public array $parameters,
    ) {
    }

    public function field(string $name): ?BridgeField
    {
        foreach ($this->fields as $field) {
            if ($field->name === $name) {
                return $field;
            }
        }

        return null;
    }
}
