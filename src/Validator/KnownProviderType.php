<?php

declare(strict_types=1);

namespace Guiziweb\SyliusAiPlatformPlugin\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class KnownProviderType extends Constraint
{
    public string $message = 'guiziweb_sylius_ai_platform.provider.type.unknown';
}
