<?php

declare(strict_types=1);

namespace Tests\Guiziweb\SyliusAiPlatformPlugin\Unit\Platform\Fixtures;

use Symfony\AI\Platform\ProviderInterface;
use Symfony\AI\Platform\Test\MockPlatformFactory;

final class RecordingPlatformFactory
{
    /** @var array<string, mixed> */
    public static array $arguments = [];

    /**
     * @param non-empty-string $name
     */
    public static function createProvider(string $name, ?object $httpClient = null, string $region = 'default'): ProviderInterface
    {
        self::$arguments = ['name' => $name, 'httpClient' => $httpClient, 'region' => $region];

        return MockPlatformFactory::createProvider('The fake provider does not answer.', name: $name);
    }

    public static function createNothing(): object
    {
        return new \stdClass();
    }
}
