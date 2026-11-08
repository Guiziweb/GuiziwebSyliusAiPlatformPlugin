<?php

declare(strict_types=1);

namespace Guiziweb\SyliusAiPlatformPlugin\Grid;

use Guiziweb\SyliusAiPlatformPlugin\Configuration\AiConfigurationSchema;
use Guiziweb\SyliusAiPlatformPlugin\Configuration\ProviderSchema;
use Sylius\Bundle\GridBundle\Builder\Action\Action;
use Sylius\Bundle\GridBundle\Builder\Action\DeleteAction;
use Sylius\Bundle\GridBundle\Builder\Action\UpdateAction;
use Sylius\Bundle\GridBundle\Builder\Field\StringField;
use Sylius\Bundle\GridBundle\Builder\Field\TwigField;
use Sylius\Bundle\GridBundle\Builder\Filter\Filter;
use Sylius\Bundle\GridBundle\Builder\Filter\SelectFilter;
use Sylius\Bundle\GridBundle\Builder\GridBuilderInterface;
use Sylius\Component\Grid\Attribute\AsGrid;

#[AsGrid(resourceClass: '%guiziweb_sylius_ai_platform.model.provider.class%', name: ProviderGridInterface::NAME)]
final class ProviderGrid implements ProviderGridInterface
{
    public function __construct(private readonly AiConfigurationSchema $schema)
    {
    }

    public function __invoke(GridBuilderInterface $gridBuilder): void
    {
        $providerTypes = array_map(static fn (ProviderSchema $provider): string => $provider->type, $this->schema->providers());

        $gridBuilder
            ->setLimits([10, 25, 50])
            ->addOrderBy('code', 'asc')

            ->withFields(
                StringField::create('code')
                    ->setLabel('guiziweb_sylius_ai_platform.grid.provider.code')
                    ->setSortable(true),
                TwigField::create('type', '@SyliusUi/grid/field/humanized.html.twig')
                    ->setLabel('guiziweb_sylius_ai_platform.grid.provider.type')
                    ->setSortable(true),
                TwigField::create('enabled', '@SyliusAdmin/shared/grid/field/boolean.html.twig')
                    ->setLabel('guiziweb_sylius_ai_platform.grid.provider.enabled')
                    ->setSortable(true)
                    ->withOptions([
                        'vars' => [
                            'th_class' => 'w-1 text-center',
                            'td_class' => 'text-center',
                        ],
                    ]),
            )

            ->withFilters(
                Filter::create('code', 'string')
                    ->setLabel('guiziweb_sylius_ai_platform.grid.provider.code'),
                SelectFilter::create('type', array_combine($providerTypes, $providerTypes))
                    ->setLabel('guiziweb_sylius_ai_platform.grid.provider.type'),
                Filter::create('enabled', 'boolean')
                    ->setLabel('guiziweb_sylius_ai_platform.grid.provider.enabled'),
            )

            ->withMainActions(
                Action::create('create', 'create_provider'),
            )
            ->withItemActions(
                Action::create('test', 'default')
                    ->setLabel('guiziweb_sylius_ai_platform.grid.provider.test')
                    ->setIcon('tabler:plug-connected')
                    ->setOptions([
                        'link' => [
                            'route' => 'guiziweb_sylius_ai_platform_admin_provider_test',
                            'parameters' => ['id' => 'resource.id'],
                        ],
                    ]),
                UpdateAction::create(),
                DeleteAction::create(),
            )
            ->withBulkActions(
                DeleteAction::create(),
            );
    }
}
