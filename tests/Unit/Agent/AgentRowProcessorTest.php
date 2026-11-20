<?php

declare(strict_types=1);

namespace Tests\Guiziweb\SyliusAiPlatformPlugin\Unit\Agent;

use Guiziweb\SyliusAiPlatformPlugin\Agent\AgentRowProcessor;
use Guiziweb\SyliusAiPlatformPlugin\Agent\AgentRowReaderInterface;
use Guiziweb\SyliusAiPlatformPlugin\Entity\Agent\Agent;
use Guiziweb\SyliusAiPlatformPlugin\Entity\Agent\AgentInterface;
use Guiziweb\SyliusAiPlatformPlugin\Entity\Provider\Provider;
use Guiziweb\SyliusAiPlatformPlugin\Platform\ProviderRoutingListener;
use PHPUnit\Framework\TestCase;
use Symfony\AI\Agent\Input;
use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\Message\MessageBag;
use Symfony\AI\Platform\Message\MessageInterface;
use Symfony\AI\Platform\Message\Template;

final class AgentRowProcessorTest extends TestCase
{
    private int $reads = 0;

    public function test_an_agent_without_row_leaves_the_call_unchanged(): void
    {
        $input = self::input(Message::forSystem('Configured prompt'), Message::ofUser('Hello'));

        $this->processor(null)->processInput($input);

        self::assertSame('Configured prompt', $input->getMessageBag()->getSystemMessage()?->getContent());
        self::assertSame('configured-model', $input->getModel());
        self::assertSame([], $input->getOptions());
    }

    public function test_the_merchant_prompt_becomes_the_system_message(): void
    {
        $input = self::input(Message::ofUser('Hello'));

        $this->processor(self::agent('Merchant prompt'))->processInput($input);

        self::assertSame('Merchant prompt', $input->getMessageBag()->getSystemMessage()?->getContent());
    }

    public function test_a_system_message_given_by_the_caller_wins_over_the_merchant_prompt_like_over_the_configured_one(): void
    {
        $callerTemplate = Template::string('Answer in {locale}');
        $input = self::input(Message::forSystem($callerTemplate), Message::ofUser('Hello'));

        $this->processor(self::agent('Merchant prompt'))->processInput($input);

        self::assertSame($callerTemplate, $input->getMessageBag()->getSystemMessage()?->getContent());
    }

    public function test_the_provider_and_model_of_the_merchant_are_handed_to_the_platform(): void
    {
        $provider = self::provider(enabled: true);
        $input = self::input(Message::ofUser('Hello'));

        $this->processor(self::agent('Merchant prompt', $provider, 'gpt-4o-mini'))->processInput($input);

        self::assertSame('gpt-4o-mini', $input->getModel());
        self::assertSame(7, $input->getOptions()[ProviderRoutingListener::PROVIDER_OPTION]);
    }

    public function test_a_disabled_provider_keeps_the_configured_platform_and_model(): void
    {
        $input = self::input(Message::ofUser('Hello'));

        $this->processor(self::agent('Merchant prompt', self::provider(enabled: false), 'gpt-4o-mini'))->processInput($input);

        self::assertSame('configured-model', $input->getModel());
        self::assertArrayNotHasKey(ProviderRoutingListener::PROVIDER_OPTION, $input->getOptions());
    }

    public function test_the_row_is_read_once_per_call(): void
    {
        $this->processor(self::agent('Merchant prompt', self::provider(enabled: true), 'gpt-4o-mini'))->processInput(self::input(Message::ofUser('Hello')));

        self::assertSame(1, $this->reads);
    }

    private function processor(?AgentInterface $agent): AgentRowProcessor
    {
        $reader = new class($agent, fn () => ++$this->reads) implements AgentRowReaderInterface {
            public function __construct(private readonly ?AgentInterface $agent, private readonly \Closure $onRead)
            {
            }

            public function read(string $code): ?AgentInterface
            {
                ($this->onRead)();

                return $this->agent;
            }
        };

        return new AgentRowProcessor('sample_assistant', $reader);
    }

    private static function input(MessageInterface ...$messages): Input
    {
        return new Input('configured-model', new MessageBag(...$messages));
    }

    private static function agent(string $prompt, ?Provider $provider = null, ?string $model = null): Agent
    {
        $agent = new Agent();
        $agent->setCode('sample_assistant');
        $agent->setPrompt($prompt);
        $agent->setDefaultPrompt($prompt);
        $agent->setProvider($provider);
        $agent->setModel($model);

        return $agent;
    }

    private static function provider(bool $enabled): Provider
    {
        $provider = new Provider();
        $provider->setCode('openai-eu');
        $provider->setType('openai');
        $provider->setEnabled($enabled);
        (new \ReflectionProperty(Provider::class, 'id'))->setValue($provider, 7);

        return $provider;
    }
}
