<?php

declare(strict_types=1);

namespace Tests\Guiziweb\SyliusAiPlatformPlugin\Behat\Page\Admin\Provider;

use Sylius\Behat\Page\Admin\Crud\CreatePage as BaseCreatePage;

final class CreatePage extends BaseCreatePage
{
    use ProviderFormTrait;

    protected function getDefinedElements(): array
    {
        return array_merge(parent::getDefinedElements(), $this->getFormElements());
    }
}
