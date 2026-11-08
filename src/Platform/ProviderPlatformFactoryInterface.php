<?php

declare(strict_types=1);

namespace Guiziweb\SyliusAiPlatformPlugin\Platform;

use Guiziweb\SyliusAiPlatformPlugin\Entity\Provider\ProviderInterface;
use Symfony\AI\Platform\PlatformInterface;

interface ProviderPlatformFactoryInterface
{
    public function create(ProviderInterface $provider): PlatformInterface;
}
