<?php

declare(strict_types=1);

namespace Guiziweb\SyliusAiPlatformPlugin\Menu;

use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;

final class AiMenuListener
{
    public function __invoke(MenuBuilderEvent $event): void
    {
        $section = $event->getMenu()
            ->addChild('guiziweb_ai')
            ->setLabel('guiziweb_sylius_ai_platform.menu.ai')
            ->setLabelAttribute('icon', 'tabler:sparkles')
        ;

        $section
            ->addChild('providers', ['route' => 'guiziweb_sylius_ai_platform_admin_provider_index', 'extras' => ['routes' => [
                ['route' => 'guiziweb_sylius_ai_platform_admin_provider_create'],
                ['route' => 'guiziweb_sylius_ai_platform_admin_provider_update'],
            ]]])
            ->setLabel('guiziweb_sylius_ai_platform.menu.providers')
            ->setLabelAttribute('icon', 'tabler:plug-connected')
        ;
    }
}
