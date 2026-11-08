<?php

declare(strict_types=1);

namespace Tests\Guiziweb\SyliusAiPlatformPlugin\Behat\Context\Ui\Admin;

use Behat\Behat\Context\Context;
use Behat\Step\Then;
use Behat\Step\When;
use Doctrine\ORM\EntityManagerInterface;
use Guiziweb\SyliusAiPlatformPlugin\Entity\Provider\Provider;
use Sylius\Behat\Element\Admin\NotificationsElementInterface;
use Sylius\Behat\NotificationType;
use Sylius\Behat\Page\Admin\Crud\IndexPageInterface;
use Tests\Guiziweb\SyliusAiPlatformPlugin\Behat\Page\Admin\Provider\CreatePage;
use Tests\Guiziweb\SyliusAiPlatformPlugin\Behat\Page\Admin\Provider\UpdatePage;
use Webmozart\Assert\Assert;

final readonly class ManagingProvidersContext implements Context
{
    public function __construct(
        private CreatePage $createPage,
        private UpdatePage $updatePage,
        private IndexPageInterface $indexPage,
        private NotificationsElementInterface $notificationsElement,
        private EntityManagerInterface $entityManager,
    ) {
    }

    #[When('I want to connect a provider of type :type')]
    public function iWantToConnectAProviderOfType(string $type): void
    {
        $this->createPage->open(['type' => $type]);
    }

    #[When('I name it :code')]
    public function iNameIt(string $code): void
    {
        $this->createPage->nameIt($code);
    }

    #[When('I set its :setting setting to :value')]
    public function iSetItsSetting(string $setting, string $value): void
    {
        $this->createPage->specifySetting($setting, $value);
    }

    #[When('I choose :value for its :setting setting')]
    public function iChooseForItsSetting(string $value, string $setting): void
    {
        $this->createPage->chooseSetting($setting, $value);
    }

    #[When('I add it')]
    public function iAddIt(): void
    {
        $this->createPage->create();
    }

    #[When('I want to modify the connection :code')]
    public function iWantToModifyTheConnection(string $code): void
    {
        $this->updatePage->open(['id' => $this->findConnection($code)->getId()]);
    }

    #[When('I change its :setting setting to :value')]
    public function iChangeItsSettingTo(string $setting, string $value): void
    {
        $this->updatePage->specifySetting($setting, $value);
    }

    #[When('I save my changes')]
    public function iSaveMyChanges(): void
    {
        $this->updatePage->saveChanges();
    }

    #[When('I delete the connection :code')]
    public function iDeleteTheConnection(string $code): void
    {
        $this->indexPage->open();
        $this->indexPage->deleteResourceOnPage(['code' => $code]);
    }

    #[When('I check the platform of the connection :code')]
    public function iCheckThePlatformOfTheConnection(string $code): void
    {
        $this->indexPage->open();
        $this->indexPage->getActionsForResource(['code' => $code])->clickLink('Check platform');
    }

    #[Then('the connection :code should appear in the registry')]
    public function theConnectionShouldAppearInTheRegistry(string $code): void
    {
        $this->indexPage->open();

        Assert::true($this->indexPage->isSingleResourceOnPage(['code' => $code]));
    }

    #[Then('the connection :code should no longer exist in the registry')]
    public function theConnectionShouldNoLongerExistInTheRegistry(string $code): void
    {
        $this->indexPage->open();

        Assert::false($this->indexPage->isSingleResourceOnPage(['code' => $code]));
    }

    #[Then('there should be :count connection(s) in the registry')]
    public function thereShouldBeConnectionsInTheRegistry(int $count): void
    {
        $this->indexPage->open();

        Assert::same($this->indexPage->countItems(), $count);
    }

    #[Then('I should be told that :message')]
    public function iShouldBeToldThat(string $message): void
    {
        Assert::oneOf($message, $this->createPage->getValidationMessages());
    }

    #[Then('the form should still show its :setting setting as :value')]
    public function theFormShouldStillShowItsSettingAs(string $setting, string $value): void
    {
        Assert::same($this->createPage->getSettingValue($setting), $value);
    }

    #[Then('the form should still show the name :code')]
    public function theFormShouldStillShowTheName(string $code): void
    {
        Assert::same($this->createPage->getCode(), $code);
    }

    #[Then('the form should have a :setting setting')]
    public function theFormShouldHaveASetting(string $setting): void
    {
        Assert::true($this->createPage->hasSetting($setting));
    }

    #[Then('the form should not have a :setting setting')]
    public function theFormShouldNotHaveASetting(string $setting): void
    {
        Assert::false($this->createPage->hasSetting($setting));
    }

    #[Then('the :setting setting should offer :values')]
    public function theSettingShouldOffer(string $setting, string $values): void
    {
        Assert::same($this->createPage->getSettingChoices($setting), array_map('trim', explode(',', $values)));
    }

    #[Then('its :setting setting should be :value')]
    public function itsSettingShouldBe(string $setting, string $value): void
    {
        Assert::same($this->updatePage->getSettingValue($setting), $value);
    }

    #[Then('its name should not be editable')]
    public function itsNameShouldNotBeEditable(): void
    {
        Assert::false($this->updatePage->isCodeEditable());
    }

    #[Then('its provider type should be :type')]
    public function itsProviderTypeShouldBe(string $type): void
    {
        Assert::same($this->updatePage->getProviderType(), $type);
    }

    #[Then('I should be notified that the platform can be built')]
    public function iShouldBeNotifiedThatThePlatformCanBeBuilt(): void
    {
        Assert::true($this->notificationsElement->hasNotification((string) NotificationType::success(), 'Platform can be built.'));
    }

    #[Then('I should be notified that the bridge of the :package package is missing')]
    public function iShouldBeNotifiedThatTheBridgeIsMissing(string $package): void
    {
        Assert::true($this->notificationsElement->hasNotification((string) NotificationType::error(), sprintf('composer require %s', $package)));
    }

    private function findConnection(string $code): Provider
    {
        $connection = $this->entityManager->getRepository(Provider::class)->findOneBy(['code' => $code]);
        Assert::isInstanceOf($connection, Provider::class);

        return $connection;
    }
}
