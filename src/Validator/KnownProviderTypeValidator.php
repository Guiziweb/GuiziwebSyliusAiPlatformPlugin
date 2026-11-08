<?php

declare(strict_types=1);

namespace Guiziweb\SyliusAiPlatformPlugin\Validator;

use Guiziweb\SyliusAiPlatformPlugin\Configuration\AiConfigurationSchema;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

final class KnownProviderTypeValidator extends ConstraintValidator
{
    public function __construct(private readonly AiConfigurationSchema $schema)
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, KnownProviderType::class);

        if (!is_string($value) || '' === $value || null !== $this->schema->provider($value)) {
            return;
        }

        $this->context->buildViolation($constraint->message)->setParameter('{{ type }}', $value)->addViolation();
    }
}
