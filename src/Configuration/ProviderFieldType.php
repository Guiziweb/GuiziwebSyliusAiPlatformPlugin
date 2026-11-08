<?php

declare(strict_types=1);

namespace Guiziweb\SyliusAiPlatformPlugin\Configuration;

enum ProviderFieldType: string
{
    case String = 'string';
    case Integer = 'integer';
    case Boolean = 'boolean';
}
