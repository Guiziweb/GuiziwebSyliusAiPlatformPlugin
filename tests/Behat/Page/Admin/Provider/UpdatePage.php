<?php

declare(strict_types=1);

namespace Tests\Guiziweb\SyliusAiPlatformPlugin\Behat\Page\Admin\Provider;

use Sylius\Behat\Page\Admin\Crud\UpdatePage as BaseUpdatePage;

final class UpdatePage extends BaseUpdatePage
{
    use ProviderFormTrait;

    protected function getDefinedElements(): array
    {
        return array_merge(parent::getDefinedElements(), $this->getFormElements());
    }
}
