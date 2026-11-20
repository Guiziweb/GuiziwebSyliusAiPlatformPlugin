<?php

declare(strict_types=1);

namespace Tests\Guiziweb\SyliusAiPlatformPlugin\Behat\Context\Ui\Admin;

use Behat\Behat\Context\Context;
use Behat\Step\Then;
use Behat\Step\When;
use Doctrine\ORM\EntityManagerInterface;
use Guiziweb\SyliusAiPlatformPlugin\Entity\Agent\Agent;
use Guiziweb\SyliusAiPlatformPlugin\Entity\Provider\Provider;
use Sylius\Behat\Page\Admin\Crud\IndexPageInterface;
use Tests\Guiziweb\SyliusAiPlatformPlugin\Behat\Page\Admin\Agent\UpdatePage;
use Webmozart\Assert\Assert;

final readonly class ManagingAgentsContext implements Context
{
    public function __construct(
        private UpdatePage $updatePage,
        private IndexPageInterface $indexPage,
        private EntityManagerInterface $entityManager,
    ) {
    }

    #[When('I browse the AI agents')]
    public function iBrowseTheAiAgents(): void
    {
        $this->indexPage->open();
    }

    #[When('I browse the AI agents that use the connection :code')]
    public function iBrowseTheAiAgentsThatUseTheConnection(string $code): void
    {
        $connection = $this->entityManager->getRepository(Provider::class)->findOneBy(['code' => $code]);
        Assert::notNull($connection);

        $this->indexPage->open(['criteria' => ['provider' => $connection->getId()]]);
    }

    #[Then('I should not see the agent :name')]
    public function iShouldNotSeeTheAgent(string $name): void
    {
        Assert::false($this->indexPage->isSingleResourceOnPage(['name' => $name]));
    }

    #[When('I want to modify the agent :name')]
    public function iWantToModifyTheAgent(string $name): void
    {
        $agent = $this->entityManager->getRepository(Agent::class)->findOneBy(['name' => $name]);
        Assert::notNull($agent);

        $this->entityManager->refresh($agent);
        $this->updatePage->open(['id' => $agent->getId()]);
    }

    #[When('I change its prompt to :prompt')]
    public function iChangeItsPromptTo(string $prompt): void
    {
        $this->updatePage->changePrompt($prompt);
    }

    #[When('I clear its prompt')]
    public function iClearItsPrompt(): void
    {
        $this->updatePage->changePrompt('');
    }

    #[When('I choose the connection :code for it')]
    public function iChooseTheConnectionForIt(string $code): void
    {
        $this->updatePage->chooseProvider($code);
    }

    #[When('I save my changes')]
    public function iSaveMyChanges(): void
    {
        $this->updatePage->saveChanges();
    }

    #[Then('I should see the agent :name')]
    public function iShouldSeeTheAgent(string $name): void
    {
        Assert::true($this->indexPage->isSingleResourceOnPage(['name' => $name]));
    }

    #[Then('the agent :name should use the default configuration')]
    public function theAgentShouldUseTheDefaultConfiguration(string $name): void
    {
        Assert::true($this->indexPage->isSingleResourceOnPage(['name' => $name, 'provider' => 'Default configuration']));
    }

    #[Then('the agent :name should use the connection :code with the model :model')]
    public function theAgentShouldUseTheConnectionWithTheModel(string $name, string $code, string $model): void
    {
        Assert::true($this->indexPage->isSingleResourceOnPage(['name' => $name, 'provider' => $code, 'model' => $model]));
    }

    #[Then('its prompt should be :prompt')]
    public function itsPromptShouldBe(string $prompt): void
    {
        Assert::same($this->updatePage->getPrompt(), $prompt);
    }

    #[Then('its connection should be :code')]
    public function itsConnectionShouldBe(string $code): void
    {
        Assert::same($this->updatePage->getSelectedProvider(), $code);
    }

    #[Then('its model should be :model')]
    public function itsModelShouldBe(string $model): void
    {
        Assert::same($this->updatePage->getSelectedModel(), $model);
    }

    #[Then('the connection :code should be offered')]
    public function theConnectionShouldBeOffered(string $code): void
    {
        Assert::inArray($code, $this->updatePage->getProviderChoices());
    }

    #[Then('the connection :code should not be offered')]
    public function theConnectionShouldNotBeOffered(string $code): void
    {
        Assert::false(\in_array($code, $this->updatePage->getProviderChoices(), true));
    }

    #[Then('the model :model should still be offered')]
    public function theModelShouldStillBeOffered(string $model): void
    {
        Assert::inArray($model, $this->updatePage->getModelChoices());
    }

    #[Then('I should be told that :message')]
    public function iShouldBeToldThat(string $message): void
    {
        Assert::oneOf($message, $this->updatePage->getValidationMessages());
    }
}
