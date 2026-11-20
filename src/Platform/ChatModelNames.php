<?php

declare(strict_types=1);

namespace Guiziweb\SyliusAiPlatformPlugin\Platform;

use Guiziweb\SyliusAiPlatformPlugin\Entity\Provider\ProviderInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\AI\Platform\Capability;

final class ChatModelNames implements ChatModelNamesInterface
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

        $required = [Capability::INPUT_MESSAGES, Capability::OUTPUT_TEXT];

        $names = [];

        foreach ($models as $name => $model) {
            $missing = array_filter($required, static fn (Capability $capability): bool => !\in_array($capability, $model['capabilities'], true));

            if ([] === $missing) {
                $names[] = (string) $name;
            }
        }

        sort($names);

        return $names;
    }
}
