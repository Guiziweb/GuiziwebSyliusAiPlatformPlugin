<?php

declare(strict_types=1);

namespace Guiziweb\SyliusAiPlatformPlugin\Platform;

use Guiziweb\SyliusAiPlatformPlugin\Entity\Provider\ProviderInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class ModelNames implements ModelNamesInterface
{
    public function __construct(
        private readonly AiProviderFactoryInterface $aiProviderFactory,
        private readonly LoggerInterface $logger = new NullLogger(),
    ) {
    }

    public function forProvider(ProviderInterface $provider): array
    {
        try {
            $models = $this->aiProviderFactory->create($provider)->getModelCatalog()->getModels();
        } catch (\Throwable $exception) {
            $this->logger->warning('The models of the AI provider "{provider}" could not be listed: {message}', [
                'provider' => $provider->getCode(),
                'message' => $exception->getMessage(),
                'exception' => $exception,
            ]);

            return [];
        }

        $names = array_map(strval(...), array_keys($models));
        sort($names);

        return $names;
    }
}
