<?php

declare(strict_types=1);

namespace Guiziweb\SyliusAiPlatformPlugin\Grid;

use Sylius\Bundle\GridBundle\Builder\GridBuilderInterface;

interface AgentGridInterface
{
    public const NAME = 'guiziweb_sylius_ai_platform_admin_agent';

    public function __invoke(GridBuilderInterface $gridBuilder): void;
}
