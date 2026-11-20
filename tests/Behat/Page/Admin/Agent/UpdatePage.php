<?php

declare(strict_types=1);

namespace Tests\Guiziweb\SyliusAiPlatformPlugin\Behat\Page\Admin\Agent;

use Behat\Mink\Element\NodeElement;
use Sylius\Behat\Page\Admin\Crud\UpdatePage as BaseUpdatePage;
use Webmozart\Assert\Assert;

final class UpdatePage extends BaseUpdatePage
{
    public function chooseProvider(string $code): void
    {
        $this->getElement('provider')->selectOption($code);
    }

    public function chooseModel(string $model): void
    {
        $this->getElement('model')->selectOption($model);
    }

    public function changePrompt(string $prompt): void
    {
        $this->getElement('prompt')->setValue($prompt);
    }

    public function getPrompt(): string
    {
        $value = $this->getElement('prompt')->getValue();
        Assert::string($value);

        return $value;
    }

    public function getSelectedProvider(): string
    {
        return $this->selectedOptionOf('provider');
    }

    public function getSelectedModel(): string
    {
        return $this->selectedOptionOf('model');
    }

    /**
     * @return list<string>
     */
    public function getProviderChoices(): array
    {
        return array_values(array_map(
            static fn (NodeElement $option): string => trim($option->getText()),
            $this->getElement('provider')->findAll('css', 'option'),
        ));
    }

    /**
     * @return list<string>
     */
    public function getModelChoices(): array
    {
        return array_values(array_map(
            static fn (NodeElement $option): string => trim($option->getText()),
            $this->getElement('model')->findAll('css', 'option'),
        ));
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

    protected function getDefinedElements(): array
    {
        return array_merge(parent::getDefinedElements(), [
            'model' => '#guiziweb_sylius_ai_platform_agent_model',
            'prompt' => '#guiziweb_sylius_ai_platform_agent_prompt',
            'provider' => '#guiziweb_sylius_ai_platform_agent_provider',
        ]);
    }

    private function selectedOptionOf(string $element): string
    {
        $selected = $this->getElement($element)->find('css', 'option[selected]');

        return null === $selected ? '' : trim($selected->getText());
    }
}
