<?php

declare(strict_types=1);

namespace Tests\Guiziweb\SyliusAiPlatformPlugin\Functional\Agent;

use Doctrine\ORM\EntityManagerInterface;
use Guiziweb\SyliusAiPlatformPlugin\Entity\Agent\Agent;
use Guiziweb\SyliusAiPlatformPlugin\Entity\Provider\Provider;
use Symfony\AI\Agent\AgentInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class AgentOnAdminProviderTest extends KernelTestCase
{
    /** @var list<array{url: string, authorization: string, body: array<string, mixed>}> */
    private array $requests = [];

    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        self::bootKernel();
        $entityManager = self::getContainer()->get('doctrine.orm.entity_manager');
        self::assertInstanceOf(EntityManagerInterface::class, $entityManager);
        $this->entityManager = $entityManager;
        $this->entityManager->getConnection()->beginTransaction();

        self::getContainer()->set('http_client', new MockHttpClient(function (string $method, string $url, array $options): MockResponse {
            $body = json_decode(\is_string($options['body'] ?? null) ? $options['body'] : '{}', true);
            $this->requests[] = ['url' => $url, 'authorization' => $options['normalized_headers']['authorization'][0] ?? '', 'body' => \is_array($body) ? $body : []];

            return new MockResponse(
                (string) json_encode(['choices' => [['message' => ['role' => 'assistant', 'content' => 'Answer of the admin provider'], 'finish_reason' => 'stop']]]),
                ['response_headers' => ['content-type: application/json']],
            );
        }));
    }

    protected function tearDown(): void
    {
        $connection = $this->entityManager->getConnection();

        if ($connection->isTransactionActive()) {
            $connection->rollBack();
        }

        $this->entityManager->clear();

        parent::tearDown();
    }

    public function test_an_agent_without_admin_provider_answers_with_its_configured_platform(): void
    {
        $this->saveSampleAssistant(prompt: 'Merchant prompt', provider: null, model: null);

        $answer = (string) $this->sampleAssistant()->call('Hello')->getContent();

        self::assertStringContainsString('model: sample-model', $answer);
        self::assertStringContainsString('"content":"Merchant prompt"', $answer);
        self::assertSame([], $this->requests);
    }

    public function test_an_agent_on_an_admin_provider_sends_the_merchant_prompt_and_model_to_that_provider(): void
    {
        $provider = new Provider();
        $provider->setCode('mistral-main');
        $provider->setType('mistral');
        $provider->setConfig(['api_key' => 'mistral-key']);
        $this->saveSampleAssistant(prompt: 'Merchant prompt', provider: $provider, model: 'mistral-small-latest');

        $answer = $this->sampleAssistant()->call('Hello')->getContent();

        self::assertSame('Answer of the admin provider', $answer);
        self::assertCount(1, $this->requests);
        self::assertSame('https://api.mistral.ai/v1/chat/completions', $this->requests[0]['url']);
        self::assertSame('Authorization: Bearer mistral-key', $this->requests[0]['authorization']);
        self::assertSame('mistral-small-latest', $this->requests[0]['body']['model']);
        self::assertSame(['role' => 'system', 'content' => 'Merchant prompt'], $this->requests[0]['body']['messages'][0]);
        self::assertArrayNotHasKey('_guiziweb_ai_provider', $this->requests[0]['body']);
    }

    private function saveSampleAssistant(string $prompt, ?Provider $provider, ?string $model): void
    {
        $agent = $this->entityManager->getRepository(Agent::class)->findOneBy(['code' => 'sample_assistant']) ?? new Agent();
        $agent->setCode('sample_assistant');
        $agent->setName('Sample assistant');
        $agent->setPrompt($prompt);
        $agent->setDefaultPrompt($prompt);

        if (null !== $provider) {
            $this->entityManager->persist($provider);
        }

        $agent->setProvider($provider);
        $agent->setModel($model);
        $this->entityManager->persist($agent);
        $this->entityManager->flush();
    }

    private function sampleAssistant(): AgentInterface
    {
        $agent = self::getContainer()->get('ai.agent.sample_assistant');
        self::assertInstanceOf(AgentInterface::class, $agent);

        return $agent;
    }
}
