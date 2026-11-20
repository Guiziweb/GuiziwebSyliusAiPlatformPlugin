<?php

declare(strict_types=1);

namespace Guiziweb\SyliusAiPlatformPlugin\Agent;

use Guiziweb\SyliusAiPlatformPlugin\Entity\Agent\AgentInterface;

interface AgentRowReaderInterface
{
    public function read(string $code): ?AgentInterface;
}
