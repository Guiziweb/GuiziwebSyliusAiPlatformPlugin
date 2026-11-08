<?php

declare(strict_types=1);

namespace Tests\Guiziweb\SyliusAiPlatformPlugin\Functional\Form;

use Guiziweb\SyliusAiPlatformPlugin\Entity\Provider\Provider;
use Guiziweb\SyliusAiPlatformPlugin\Form\Type\ProviderType;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

final class ProviderFormTest extends KernelTestCase
{
    public function test_the_form_has_the_fields_of_the_chosen_provider(): void
    {
        $config = $this->configOf('openai');

        self::assertSame(['api_key', 'region'], array_keys($config->all()));
    }

    public function test_a_required_setting_is_a_required_field_and_an_optional_one_is_not(): void
    {
        $config = $this->configOf('openai');

        self::assertTrue($config->get('api_key')->isRequired());
        self::assertFalse($config->get('region')->isRequired());
    }

    public function test_a_text_setting_is_a_text_field(): void
    {
        self::assertInstanceOf(TextType::class, $this->configOf('openai')->get('api_key')->getConfig()->getType()->getInnerType());
    }

    public function test_a_boolean_setting_is_a_checkbox(): void
    {
        $field = $this->configOf('generic')->get('supports_completions');

        self::assertInstanceOf(CheckboxType::class, $field->getConfig()->getType()->getInnerType());
    }

    public function test_an_enum_setting_is_a_choice_with_its_allowed_values(): void
    {
        $field = $this->configOf('anthropic')->get('cache_retention');

        self::assertInstanceOf(ChoiceType::class, $field->getConfig()->getType()->getInnerType());
        self::assertSame(['none' => 'none', 'short' => 'short', 'long' => 'long'], $field->getConfig()->getOption('choices'));
    }

    public function test_the_technical_settings_are_not_fields(): void
    {
        foreach (['openai', 'generic', 'anthropic'] as $type) {
            self::assertFalse($this->configOf($type)->has('http_client'), $type);
            self::assertFalse($this->configOf($type)->has('model_catalog'), $type);
        }
    }

    public function test_the_provider_type_is_shown_but_cannot_be_edited(): void
    {
        $form = $this->form('openai');

        self::assertTrue($form->get('type')->isDisabled());
    }

    private function configOf(string $type): FormInterface
    {
        return $this->form($type)->get('config');
    }

    private function form(string $type): FormInterface
    {
        self::bootKernel();
        $formFactory = self::getContainer()->get('form.factory');
        self::assertInstanceOf(FormFactoryInterface::class, $formFactory);

        $connection = new Provider();
        $connection->setType($type);

        return $formFactory->create(ProviderType::class, $connection);
    }
}
