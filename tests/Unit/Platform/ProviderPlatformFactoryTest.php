<?php

declare(strict_types=1);

namespace Tests\Guiziweb\SyliusAiPlatformPlugin\Unit\Platform;

use Guiziweb\SyliusAiPlatformPlugin\Configuration\AiConfigurationSchema;
use Guiziweb\SyliusAiPlatformPlugin\Platform\PlatformRecipeExecutor;
use Guiziweb\SyliusAiPlatformPlugin\Platform\PlatformRecipeReader;
use Guiziweb\SyliusAiPlatformPlugin\Platform\ProviderPlatformFactory;
use PHPUnit\Framework\TestCase;
use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\Message\MessageBag;
use Symfony\AI\Platform\PlatformInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class ProviderPlatformFactoryTest extends TestCase
{
    use PlatformTestSupportTrait;

    /** @var list<array{method: string, url: string, headers: array<string, list<string>>}> */
    private array $requests = [];

    public function test_a_connection_of_a_provider_with_a_region_sends_its_requests_to_that_region(): void
    {
        self::requireBridges('symfony/ai-open-ai-platform');

        $this->ask($this->factory()->create(self::connection('openai-eu', 'openai', ['api_key' => 'sk-1', 'region' => 'EU'])), 'gpt-4o-mini');
        $this->ask($this->factory()->create(self::connection('openai-us', 'openai', ['api_key' => 'sk-2', 'region' => 'US'])), 'gpt-4o-mini');

        self::assertSame(
            [['https://eu.api.openai.com/v1/responses', 'Authorization: Bearer sk-1'], ['https://us.api.openai.com/v1/responses', 'Authorization: Bearer sk-2']],
            array_map(fn (array $request): array => [$request['url'], $request['headers']['authorization'][0]], $this->requests),
        );
    }

    public function test_a_connection_without_region_uses_the_default_address_of_the_provider(): void
    {
        self::requireBridges('symfony/ai-open-ai-platform');

        $this->ask($this->factory()->create(self::connection('openai', 'openai', ['api_key' => 'sk-1'])), 'gpt-4o-mini');

        self::assertSame('https://api.openai.com/v1/responses', $this->requests[0]['url']);
    }

    public function test_the_key_of_a_connection_is_sent_the_way_its_provider_expects_it(): void
    {
        self::requireBridges('symfony/ai-mistral-platform', 'symfony/ai-anthropic-platform');

        $this->ask($this->factory()->create(self::connection('mistral', 'mistral', ['api_key' => 'mistral-key'])), 'mistral-small-latest');
        $this->ask($this->factory()->create(self::connection('claude', 'anthropic', ['api_key' => 'claude-key', 'cache_retention' => 'long'])), 'claude-sonnet-4-5');

        self::assertSame('https://api.mistral.ai/v1/chat/completions', $this->requests[0]['url']);
        self::assertSame('Authorization: Bearer mistral-key', $this->requests[0]['headers']['authorization'][0]);
        self::assertSame('https://api.anthropic.com/v1/messages', $this->requests[1]['url']);
        self::assertSame('x-api-key: claude-key', $this->requests[1]['headers']['x-api-key'][0]);
    }

    public function test_a_provider_configured_by_name_with_a_dash_in_its_code_sends_its_requests_to_its_own_address(): void
    {
        self::requireBridges('symfony/ai-generic-platform');

        $this->ask($this->factory()->create(self::connection('my-llm', 'generic', ['base_url' => 'https://llm.example.test', 'api_key' => 'key'])), 'my-model');

        self::assertSame('https://llm.example.test/v1/chat/completions', $this->requests[0]['url']);
    }

    public function test_the_connection_is_refused_with_the_message_of_the_bundle_when_its_bridge_is_not_installed(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('composer require symfony/ai-gemini-platform');

        $this->factory()->create(self::connection('gemini-main', 'gemini', ['api_key' => 'key']));
    }

    private function factory(): ProviderPlatformFactory
    {
        $httpClient = new MockHttpClient(function (string $method, string $url, array $options): MockResponse {
            $this->requests[] = ['method' => $method, 'url' => $url, 'headers' => $options['normalized_headers']];

            return new MockResponse('{}', ['response_headers' => ['content-type: application/json']]);
        });

        return new ProviderPlatformFactory(
            new PlatformRecipeReader(new AiConfigurationSchema(), self::kernelParameters()),
            new PlatformRecipeExecutor(['http_client' => $httpClient, 'event_dispatcher' => new EventDispatcher()]),
        );
    }

    /**
     * @param non-empty-string $model
     */
    private function ask(PlatformInterface $platform, string $model): void
    {
        $platform->invoke($model, new MessageBag(Message::ofUser('Hello')));
    }
}
