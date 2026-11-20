<?php

declare(strict_types=1);

namespace Guiziweb\SyliusAiPlatformPlugin\Grid;

use Sylius\Bundle\GridBundle\Builder\Action\UpdateAction;
use Sylius\Bundle\GridBundle\Builder\Field\StringField;
use Sylius\Bundle\GridBundle\Builder\Field\TwigField;
use Sylius\Bundle\GridBundle\Builder\Filter\EntityFilter;
use Sylius\Bundle\GridBundle\Builder\Filter\Filter;
use Sylius\Bundle\GridBundle\Builder\GridBuilderInterface;
use Sylius\Component\Grid\Attribute\AsGrid;

#[AsGrid(resourceClass: '%guiziweb_sylius_ai_platform.model.agent.class%', name: AgentGridInterface::NAME)]
final class AgentGrid implements AgentGridInterface
{
    public function __construct(private readonly string $providerClass)
    {
    }

    public function __invoke(GridBuilderInterface $gridBuilder): void
    {
        $gridBuilder
            ->setLimits([10, 25, 50])
            ->addOrderBy('name', 'asc')

            ->withFields(
                StringField::create('name')
                    ->setLabel('guiziweb_sylius_ai_platform.grid.agent.name')
                    ->setSortable(true),
                TwigField::create('provider', '@GuiziwebSyliusAiPlatformPlugin/admin/agent/grid/field/provider.html.twig')
                    ->setLabel('guiziweb_sylius_ai_platform.grid.agent.provider'),
                StringField::create('model')
                    ->setLabel('guiziweb_sylius_ai_platform.grid.agent.model')
                    ->setSortable(true),
                TwigField::create('customized', '@SyliusAdmin/shared/grid/field/boolean.html.twig')
                    ->setLabel('guiziweb_sylius_ai_platform.grid.agent.customized'),
            )

            ->withFilters(
                Filter::create('name', 'string')
                    ->setLabel('guiziweb_sylius_ai_platform.grid.agent.name'),
                EntityFilter::create('provider', $this->providerClass)
                    ->setLabel('guiziweb_sylius_ai_platform.grid.agent.provider')
                    ->addFormOption('choice_label', 'code'),
            )

            ->withItemActions(
                UpdateAction::create(),
            );
    }
}
