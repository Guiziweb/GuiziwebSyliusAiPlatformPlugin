<?php

declare(strict_types=1);

namespace Guiziweb\SyliusAiPlatformPlugin\Platform;

use Guiziweb\SyliusAiPlatformPlugin\Entity\Provider\ProviderInterface;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;
use Symfony\AI\Platform\Event\ModelRoutingEvent;

final class ProviderRoutingListener
{
    public const PROVIDER_OPTION = '_guiziweb_ai_provider';

    /**
     * @param RepositoryInterface<ProviderInterface> $providerRepository
     */
    public function __construct(
        private readonly AiProviderFactoryInterface $aiProviderFactory,
        private readonly RepositoryInterface $providerRepository,
    ) {
    }

    public function __invoke(ModelRoutingEvent $event): void
    {
        $options = $event->getOptions();

        if (!\array_key_exists(self::PROVIDER_OPTION, $options)) {
            return;
        }

        $providerId = $options[self::PROVIDER_OPTION];
        unset($options[self::PROVIDER_OPTION]);
        $event->setOptions($options);

        $provider = \is_int($providerId) ? $this->providerRepository->find($providerId) : null;

        if ($provider instanceof ProviderInterface) {
            $event->setProvider($this->aiProviderFactory->create($provider));
        }
    }
}
