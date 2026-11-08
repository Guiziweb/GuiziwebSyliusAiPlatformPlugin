<?php

declare(strict_types=1);

namespace Guiziweb\SyliusAiPlatformPlugin\Factory;

use Guiziweb\SyliusAiPlatformPlugin\Configuration\AiConfigurationSchema;
use Guiziweb\SyliusAiPlatformPlugin\Entity\Provider\ProviderInterface;
use Sylius\Resource\Factory\FactoryInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ProviderFactory implements ProviderFactoryInterface
{
    /**
     * @param FactoryInterface<ProviderInterface> $decoratedFactory
     */
    public function __construct(
        private readonly FactoryInterface $decoratedFactory,
        private readonly AiConfigurationSchema $schema,
    ) {
    }

    public function createNew(): ProviderInterface
    {
        $provider = $this->decoratedFactory->createNew();
        assert($provider instanceof ProviderInterface);

        return $provider;
    }

    public function createForType(string $type): ProviderInterface
    {
        if (null === $this->schema->provider($type)) {
            throw new NotFoundHttpException(sprintf('Unknown AI provider type "%s".', $type));
        }

        $provider = $this->createNew();
        $provider->setType($type);

        return $provider;
    }
}
