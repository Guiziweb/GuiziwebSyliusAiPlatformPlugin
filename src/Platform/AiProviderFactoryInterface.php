<?php

declare(strict_types=1);

namespace Guiziweb\SyliusAiPlatformPlugin\Platform;

use Guiziweb\SyliusAiPlatformPlugin\Entity\Provider\ProviderInterface;
use Symfony\AI\Platform\ProviderInterface as AiProviderInterface;

interface AiProviderFactoryInterface
{
    public function create(ProviderInterface $provider): AiProviderInterface;
}
