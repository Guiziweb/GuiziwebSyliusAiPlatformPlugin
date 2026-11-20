<?php

declare(strict_types=1);

namespace Tests\Guiziweb\SyliusAiPlatformPlugin\Unit\Platform;

use Guiziweb\SyliusAiPlatformPlugin\Entity\Provider\Provider;
use Guiziweb\SyliusAiPlatformPlugin\Entity\Provider\ProviderInterface;
use Guiziweb\SyliusAiPlatformPlugin\Platform\AiProviderFactoryInterface;
use Guiziweb\SyliusAiPlatformPlugin\Platform\ProviderRoutingListener;
use PHPUnit\Framework\TestCase;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;
use Symfony\AI\Platform\Event\ModelRoutingEvent;
use Symfony\AI\Platform\Model;
use Symfony\AI\Platform\PlatformInterface;
use Symfony\AI\Platform\ProviderInterface as AiProviderInterface;
use Symfony\AI\Platform\Test\MockPlatformFactory;
use Symfony\Component\EventDispatcher\EventDispatcher;

final class ProviderRoutingListenerTest extends TestCase
{
    /** @var list<array<string, mixed>> */
    private array $optionsReceivedByTheAdminProvider = [];

    public function test_a_call_without_provider_option_is_served_by_the_configured_platform(): void
    {
        self::assertSame('configured', $this->configuredPlatform()->invoke('model', 'Hello')->asText());
    }

    public function test_a_call_with_a_provider_option_is_served_by_that_provider_without_the_option(): void
    {
        $result = $this->configuredPlatform(self::provider())->invoke('model', 'Hello', [ProviderRoutingListener::PROVIDER_OPTION => 7, 'temperature' => 0.5]);

        self::assertSame('admin provider', $result->asText());
        self::assertSame([['temperature' => 0.5]], $this->optionsReceivedByTheAdminProvider);
    }

    public function test_a_call_with_the_id_of_an_unknown_provider_is_served_by_the_configured_platform_without_the_option(): void
    {
        $result = $this->configuredPlatform(null)->invoke('model', 'Hello', [ProviderRoutingListener::PROVIDER_OPTION => 7]);

        self::assertSame('configured', $result->asText());
        self::assertSame([], $this->optionsReceivedByTheAdminProvider);
    }

    private function configuredPlatform(?Provider $knownProvider = null): PlatformInterface
    {
        $adminProvider = MockPlatformFactory::createProvider(function (Model $model, array|string|object $input, array $options): string {
            $this->optionsReceivedByTheAdminProvider[] = $options;

            return 'admin provider';
        });

        $providerRepository = $this->createStub(RepositoryInterface::class);
        $providerRepository->method('find')->willReturn($knownProvider);

        $listener = new ProviderRoutingListener(new class($adminProvider) implements AiProviderFactoryInterface {
            public function __construct(private readonly AiProviderInterface $aiProvider)
            {
            }

            public function create(ProviderInterface $provider): AiProviderInterface
            {
                return $this->aiProvider;
            }
        }, $providerRepository);

        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addListener(ModelRoutingEvent::class, $listener);

        return MockPlatformFactory::createPlatform('configured', eventDispatcher: $eventDispatcher);
    }

    private static function provider(): Provider
    {
        $provider = new Provider();
        $provider->setCode('openai-eu');
        $provider->setType('openai');
        $provider->setConfig(['api_key' => 'sk-1', 'region' => 'EU']);

        return $provider;
    }
}
