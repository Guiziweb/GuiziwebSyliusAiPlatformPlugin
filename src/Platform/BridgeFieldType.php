<?php

declare(strict_types=1);

namespace Guiziweb\SyliusAiPlatformPlugin\Platform;

enum BridgeFieldType: string
{
    case String = 'string';
    case Integer = 'int';
    case Boolean = 'bool';
    case Float = 'float';
}
