<?php

declare(strict_types=1);

namespace Guiziweb\SyliusAiPlatformPlugin\Form\Type;

use Guiziweb\SyliusAiPlatformPlugin\Configuration\AiConfigurationSchema;
use Guiziweb\SyliusAiPlatformPlugin\Configuration\ProviderField;
use Guiziweb\SyliusAiPlatformPlugin\Configuration\ProviderFieldType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Webmozart\Assert\Assert;

final class ProviderConfigurationType extends AbstractType
{
    public function __construct(private readonly AiConfigurationSchema $schema)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $providerType = $options['provider_type'];
        Assert::string($providerType);

        $provider = $this->schema->provider($providerType);

        if (null === $provider) {
            throw new \InvalidArgumentException(sprintf('Unknown AI provider type "%s".', $providerType));
        }

        foreach ($provider->fields as $field) {
            $builder->add($field->name, $this->formTypeOf($field), $this->optionsOf($field));
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('provider_type');
        $resolver->setAllowedTypes('provider_type', 'string');
    }

    public function getBlockPrefix(): string
    {
        return 'guiziweb_sylius_ai_platform_provider_configuration';
    }

    /**
     * @return class-string<FormTypeInterface>
     */
    private function formTypeOf(ProviderField $field): string
    {
        return match ($field->type) {
            ProviderFieldType::Boolean => CheckboxType::class,
            ProviderFieldType::Integer => IntegerType::class,
            ProviderFieldType::Choice => ChoiceType::class,
            ProviderFieldType::String => TextType::class,
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function optionsOf(ProviderField $field): array
    {
        $options = [
            'label' => $field->name,
            'help' => $field->description,
            'required' => false,
        ];

        if (ProviderFieldType::Choice === $field->type) {
            $options['choices'] = array_combine($field->choices, $field->choices);
            $options['placeholder'] = $field->required ? false : '';
        }

        if ($field->required && ProviderFieldType::Boolean !== $field->type) {
            $options['constraints'] = [new NotBlank()];
            $options['required'] = true;
        }

        return $options;
    }
}
