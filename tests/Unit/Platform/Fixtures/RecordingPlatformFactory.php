<?php

declare(strict_types=1);

namespace Tests\Guiziweb\SyliusAiPlatformPlugin\Unit\Platform\Fixtures;

use Symfony\AI\Platform\Model;
use Symfony\AI\Platform\ModelCatalog\ModelCatalogInterface;
use Symfony\AI\Platform\PlatformInterface;
use Symfony\AI\Platform\Result\DeferredResult;

final class RecordingPlatformFactory
{
    /** @var array<string, mixed> */
    public static array $arguments = [];

    public static function createPlatform(string $name, ?object $httpClient = null, string $region = 'default'): PlatformInterface
    {
        self::$arguments = ['name' => $name, 'httpClient' => $httpClient, 'region' => $region];

        return new class() implements PlatformInterface {
            public function invoke(string|Model $model, array|string|object $input, array $options = []): DeferredResult
            {
                throw new \LogicException('The fake platform does not answer.');
            }

            public function getModelCatalog(): ModelCatalogInterface
            {
                throw new \LogicException('The fake platform has no catalog.');
            }
        };
    }

    public static function createNothing(): object
    {
        return new \stdClass();
    }
}
