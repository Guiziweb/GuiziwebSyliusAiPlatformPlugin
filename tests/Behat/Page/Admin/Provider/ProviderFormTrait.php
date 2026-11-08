<?php

declare(strict_types=1);

namespace Tests\Guiziweb\SyliusAiPlatformPlugin\Behat\Page\Admin\Provider;

use Behat\Mink\Element\NodeElement;
use Webmozart\Assert\Assert;

trait ProviderFormTrait
{
    public function nameIt(string $code): void
    {
        $this->getElement('code')->setValue($code);
    }

    public function specifySetting(string $setting, string $value): void
    {
        $this->getElement('setting', ['%setting%' => $setting])->setValue($value);
    }

    public function chooseSetting(string $setting, string $value): void
    {
        $this->getElement('setting', ['%setting%' => $setting])->selectOption($value);
    }

    public function hasSetting(string $setting): bool
    {
        return $this->hasElement('setting', ['%setting%' => $setting]);
    }

    public function getSettingValue(string $setting): string
    {
        return $this->valueOf($this->getElement('setting', ['%setting%' => $setting]));
    }

    /**
     * @return list<string>
     */
    public function getSettingChoices(string $setting): array
    {
        return array_values(array_filter(array_map(
            fn (NodeElement $option): string => $this->valueOf($option),
            $this->getElement('setting', ['%setting%' => $setting])->findAll('css', 'option'),
        ), static fn (string $value): bool => '' !== $value));
    }

    public function getCode(): string
    {
        return $this->valueOf($this->getElement('code'));
    }

    public function getProviderType(): string
    {
        return $this->valueOf($this->getElement('type'));
    }

    public function isCodeEditable(): bool
    {
        return !$this->getElement('code')->hasAttribute('disabled');
    }

    /**
     * @return list<string>
     */
    public function getValidationMessages(): array
    {
        return array_values(array_map(
            static fn (NodeElement $message): string => trim($message->getText()),
            $this->getDocument()->findAll('css', '.invalid-feedback'),
        ));
    }

    private function valueOf(NodeElement $field): string
    {
        $value = $field->getValue();
        Assert::string($value);

        return $value;
    }

    /**
     * @return array<string, string>
     */
    protected function getFormElements(): array
    {
        return [
            'code' => '#guiziweb_sylius_ai_platform_provider_code',
            'setting' => '#guiziweb_sylius_ai_platform_provider_config_%setting%',
            'type' => '#guiziweb_sylius_ai_platform_provider_type',
        ];
    }
}
