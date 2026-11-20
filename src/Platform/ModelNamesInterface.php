<?php

declare(strict_types=1);

namespace Guiziweb\SyliusAiPlatformPlugin\Platform;

use Guiziweb\SyliusAiPlatformPlugin\Entity\Provider\ProviderInterface;

interface ModelNamesInterface
{
    /**
     * @return list<string> the models of the provider's catalog
     */
    public function forProvider(ProviderInterface $provider): array;
}
