<?php

declare(strict_types=1);

namespace Tests\Guiziweb\SyliusAiPlatformPlugin\Unit\Configuration;

use Guiziweb\SyliusAiPlatformPlugin\Configuration\AiConfigurationSchema;
use Guiziweb\SyliusAiPlatformPlugin\Configuration\ProviderField;
use Guiziweb\SyliusAiPlatformPlugin\Configuration\ProviderFieldType;
use Guiziweb\SyliusAiPlatformPlugin\Configuration\ProviderSchema;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class AiConfigurationSchemaTest extends TestCase
{
    private AiConfigurationSchema $schema;

    protected function setUp(): void
    {
        $this->schema = new AiConfigurationSchema();
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function providersOfTheBundle(): iterable
    {
        foreach (['openai', 'mistral', 'anthropic', 'gemini', 'generic', 'openrouter'] as $type) {
            yield $type => [$type];
        }
    }

    #[DataProvider('providersOfTheBundle')]
    public function test_it_offers_the_providers_of_the_symfony_ai_configuration(string $type): void
    {
        self::assertContains($type, $this->types());
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function platformsThatAreNotOffered(): iterable
    {
        yield 'cache wraps another platform' => ['cache'];
        yield 'failover wraps other platforms' => ['failover'];
        yield 'azure has several factories' => ['azure'];
    }

    #[DataProvider('platformsThatAreNotOffered')]
    public function test_it_does_not_offer_platforms_that_cannot_be_connected_on_their_own(string $type): void
    {
        self::assertNotContains($type, $this->types());
        self::assertNull($this->schema->provider($type));
    }

    public function test_it_describes_the_settings_of_a_provider(): void
    {
        $fields = $this->fieldsOf('openai');

        self::assertSame(['api_key', 'region'], array_keys($fields));
        self::assertTrue($fields['api_key']->required);
        self::assertSame(ProviderFieldType::String, $fields['api_key']->type);
        self::assertFalse($fields['region']->required);
    }

    public function test_it_does_not_offer_the_technical_settings(): void
    {
        foreach ($this->schema->providers() as $provider) {
            foreach ($provider->fields as $field) {
                self::assertNotContains($field->name, ['http_client', 'model_catalog'], $provider->type);
            }
        }
    }

    public function test_it_gives_the_allowed_values_of_an_enum_setting(): void
    {
        $cacheRetention = $this->fieldsOf('anthropic')['cache_retention'];

        self::assertSame(ProviderFieldType::Choice, $cacheRetention->type);
        self::assertSame(['none', 'short', 'long'], $cacheRetention->choices);
    }

    public function test_it_knows_the_types_of_the_settings(): void
    {
        $fields = $this->fieldsOf('generic');

        self::assertSame(ProviderFieldType::Boolean, $fields['supports_completions']->type);
        self::assertSame(ProviderFieldType::String, $fields['base_url']->type);
    }

    public function test_it_knows_which_providers_are_configured_by_name(): void
    {
        self::assertTrue($this->provider('generic')->configuredByName);
        self::assertFalse($this->provider('openai')->configuredByName);
    }

    public function test_it_finds_no_provider_for_an_unknown_type(): void
    {
        self::assertNull($this->schema->provider('not-a-provider'));
    }

    public function test_it_reads_the_configuration_of_the_bundle_only_once(): void
    {
        self::assertSame($this->schema->providers(), $this->schema->providers());
    }

    /**
     * @return list<string>
     */
    private function types(): array
    {
        return array_map(static fn (ProviderSchema $provider): string => $provider->type, $this->schema->providers());
    }

    private function provider(string $type): ProviderSchema
    {
        return $this->schema->provider($type) ?? self::fail(sprintf('The provider "%s" is not offered.', $type));
    }

    /**
     * @return array<string, ProviderField>
     */
    private function fieldsOf(string $type): array
    {
        $fields = [];

        foreach ($this->provider($type)->fields as $field) {
            $fields[$field->name] = $field;
        }

        return $fields;
    }
}
