<?php

declare(strict_types=1);

namespace Guiziweb\SyliusAiPlatformPlugin\Validator;

use Guiziweb\SyliusAiPlatformPlugin\Platform\InstalledBridges;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

final class KnownProviderTypeValidator extends ConstraintValidator
{
    public function __construct(private readonly InstalledBridges $bridges)
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, KnownProviderType::class);

        if (!is_string($value) || '' === $value || null !== $this->bridges->get($value)) {
            return;
        }

        $this->context->buildViolation($constraint->message)->setParameter('{{ type }}', $value)->addViolation();
    }
}
