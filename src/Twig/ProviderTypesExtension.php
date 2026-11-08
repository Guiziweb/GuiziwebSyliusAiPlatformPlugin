<?php

declare(strict_types=1);

namespace Guiziweb\SyliusAiPlatformPlugin\Twig;

use Guiziweb\SyliusAiPlatformPlugin\Configuration\AiConfigurationSchema;
use Guiziweb\SyliusAiPlatformPlugin\Configuration\ProviderSchema;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class ProviderTypesExtension extends AbstractExtension
{
    public function __construct(private readonly AiConfigurationSchema $schema)
    {
    }

    public function getFunctions(): array
    {
        return [new TwigFunction('guiziweb_ai_platform_provider_types', $this->providerTypes(...))];
    }

    /**
     * @return list<string>
     */
    public function providerTypes(): array
    {
        return array_map(static fn (ProviderSchema $provider): string => $provider->code, $this->schema->providers());
    }
}
