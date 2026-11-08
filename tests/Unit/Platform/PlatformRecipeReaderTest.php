<?php

declare(strict_types=1);

namespace Tests\Guiziweb\SyliusAiPlatformPlugin\Unit\Platform;

use Guiziweb\SyliusAiPlatformPlugin\Configuration\AiConfigurationSchema;
use Guiziweb\SyliusAiPlatformPlugin\Platform\PlatformRecipeReader;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\AI\Platform\Bridge\Anthropic\Factory as AnthropicFactory;
use Symfony\AI\Platform\Bridge\Generic\Factory as GenericFactory;
use Symfony\AI\Platform\Bridge\OpenAi\Factory as OpenAiFactory;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

final class PlatformRecipeReaderTest extends TestCase
{
    use PlatformTestSupportTrait;

    private PlatformRecipeReader $reader;

    protected function setUp(): void
    {
        $this->reader = new PlatformRecipeReader(new AiConfigurationSchema(), self::kernelParameters());
    }

    public function test_a_connection_of_an_unknown_provider_type_is_refused(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown AI provider type "not-a-provider".');

        $this->reader->read(self::connection('mine', 'not-a-provider'));
    }

    public function test_a_connection_without_provider_type_is_refused(): void
    {
        $connection = self::connection('mine', 'openai');
        $connection->setType(null);

        $this->expectException(\InvalidArgumentException::class);

        $this->reader->read($connection);
    }

    public function test_a_provider_whose_bridge_is_not_installed_gives_the_command_to_install_it(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('composer require symfony/ai-gemini-platform');

        $this->reader->read(self::connection('gemini-main', 'gemini', ['api_key' => 'key']));
    }

    public function test_the_recipe_of_a_connection_names_the_factory_and_gives_its_values(): void
    {
        self::requireBridges('symfony/ai-open-ai-platform');

        $recipe = $this->reader->read(self::connection('openai-eu', 'openai', ['api_key' => 'sk-1', 'region' => 'EU']));

        self::assertSame(OpenAiFactory::class, $recipe->factory);
        self::assertSame('createPlatform', $recipe->method);
        self::assertSame(['apiKey' => 'sk-1', 'name' => 'openai-eu', 'region' => 'EU'], $this->sortedByName($recipe->values));
    }

    public function test_the_recipe_only_asks_for_the_services_the_application_provides(): void
    {
        self::requireBridges('symfony/ai-open-ai-platform');

        $recipe = $this->reader->read(self::connection('openai-eu', 'openai', ['api_key' => 'sk-1']));

        self::assertSame(['eventDispatcher' => 'event_dispatcher', 'httpClient' => 'http_client'], $this->sortedByName($recipe->services));
    }

    public function test_two_connections_of_the_same_provider_keep_their_own_settings(): void
    {
        self::requireBridges('symfony/ai-open-ai-platform');

        $europe = $this->reader->read(self::connection('openai-eu', 'openai', ['api_key' => 'sk-1', 'region' => 'EU']));
        $unitedStates = $this->reader->read(self::connection('openai-us', 'openai', ['api_key' => 'sk-2', 'region' => 'US']));

        self::assertSame(['sk-1', 'EU', 'openai-eu'], [$europe->values['apiKey'], $europe->values['region'], $europe->values['name']]);
        self::assertSame(['sk-2', 'US', 'openai-us'], [$unitedStates->values['apiKey'], $unitedStates->values['region'], $unitedStates->values['name']]);
    }

    public function test_an_enum_setting_reaches_the_factory(): void
    {
        self::requireBridges('symfony/ai-anthropic-platform');

        $recipe = $this->reader->read(self::connection('claude', 'anthropic', ['api_key' => 'key', 'cache_retention' => 'long']));

        self::assertSame(AnthropicFactory::class, $recipe->factory);
        self::assertSame('long', $recipe->values['cacheRetention']);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function codesOfAProviderConfiguredByName(): iterable
    {
        yield 'lower case' => ['mine'];
        yield 'with a dash' => ['my-conn'];
        yield 'with an underscore' => ['my_conn'];
        yield 'with capitals' => ['MyConn'];
        yield 'with capitals and a dash' => ['Mon-Compte'];
    }

    #[DataProvider('codesOfAProviderConfiguredByName')]
    public function test_a_provider_configured_by_name_accepts_every_allowed_code(string $code): void
    {
        self::requireBridges('symfony/ai-generic-platform');

        $recipe = $this->reader->read(self::connection($code, 'generic', ['base_url' => 'https://llm.example.test', 'api_key' => 'key']));

        self::assertSame(GenericFactory::class, $recipe->factory);
        self::assertSame($code, $recipe->values['name']);
        self::assertSame('https://llm.example.test', $recipe->values['baseUrl']);
    }

    public function test_a_required_setting_that_is_missing_gives_the_message_of_the_bundle(): void
    {
        self::requireBridges('symfony/ai-open-ai-platform');

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('The child config "api_key" under "ai.platform.openai" must be configured.');

        $this->reader->read(self::connection('openai-eu', 'openai', ['api_key' => '', 'region' => 'EU']));
    }

    public function test_a_setting_the_provider_does_not_have_gives_the_message_of_the_bundle(): void
    {
        self::requireBridges('symfony/ai-open-ai-platform');

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('Unrecognized option "not_a_setting" under "ai.platform.openai"');

        $this->reader->read(self::connection('openai-eu', 'openai', ['api_key' => 'sk-1', 'not_a_setting' => 'x']));
    }

    /**
     * @param array<string, mixed> $values
     *
     * @return array<string, mixed>
     */
    private function sortedByName(array $values): array
    {
        ksort($values);

        return $values;
    }
}
