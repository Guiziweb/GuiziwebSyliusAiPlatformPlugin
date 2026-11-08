<?php

declare(strict_types=1);

namespace Tests\Guiziweb\SyliusAiPlatformPlugin\Unit\Platform;

use Guiziweb\SyliusAiPlatformPlugin\Platform\PlatformRecipe;
use Guiziweb\SyliusAiPlatformPlugin\Platform\PlatformRecipeExecutor;
use PHPUnit\Framework\TestCase;
use Tests\Guiziweb\SyliusAiPlatformPlugin\Unit\Platform\Fixtures\RecordingPlatformFactory;

final class PlatformRecipeExecutorTest extends TestCase
{
    protected function setUp(): void
    {
        RecordingPlatformFactory::$arguments = [];
    }

    public function test_the_values_of_the_recipe_are_given_to_the_factory_by_name(): void
    {
        $executor = new PlatformRecipeExecutor([]);

        $executor->execute($this->recipe(values: ['name' => 'openai-eu', 'region' => 'EU']));

        self::assertSame('openai-eu', RecordingPlatformFactory::$arguments['name']);
        self::assertSame('EU', RecordingPlatformFactory::$arguments['region']);
    }

    public function test_a_service_asked_by_the_recipe_is_replaced_by_the_one_of_the_application(): void
    {
        $httpClient = new \stdClass();
        $executor = new PlatformRecipeExecutor(['http_client' => $httpClient, 'event_dispatcher' => new \stdClass()]);

        $executor->execute($this->recipe(values: ['name' => 'openai-eu'], services: ['httpClient' => 'http_client']));

        self::assertSame($httpClient, RecordingPlatformFactory::$arguments['httpClient']);
    }

    public function test_a_service_that_the_application_does_not_provide_is_refused(): void
    {
        $executor = new PlatformRecipeExecutor(['http_client' => new \stdClass()]);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The recipe needs the service "logger", which is not available.');

        $executor->execute($this->recipe(values: ['name' => 'openai-eu'], services: ['httpClient' => 'logger']));
    }

    public function test_a_factory_that_does_not_build_a_platform_is_refused(): void
    {
        $executor = new PlatformRecipeExecutor([]);

        $this->expectException(\InvalidArgumentException::class);

        $executor->execute(new PlatformRecipe(RecordingPlatformFactory::class, 'createNothing', [], []));
    }

    public function test_an_unknown_argument_is_refused_by_the_factory(): void
    {
        $executor = new PlatformRecipeExecutor([]);

        $this->expectException(\Error::class);

        $executor->execute($this->recipe(values: ['name' => 'openai-eu', 'not_an_argument' => 'x']));
    }

    /**
     * @param array<string, mixed>  $values
     * @param array<string, string> $services
     */
    private function recipe(array $values, array $services = []): PlatformRecipe
    {
        return new PlatformRecipe(RecordingPlatformFactory::class, 'createPlatform', $values, $services);
    }
}
