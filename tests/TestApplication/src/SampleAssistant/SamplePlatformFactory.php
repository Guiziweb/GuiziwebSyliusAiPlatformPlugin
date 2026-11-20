<?php

declare(strict_types=1);

namespace Tests\Guiziweb\SyliusAiPlatformPlugin\SampleAssistant;

use Symfony\AI\Platform\Model;
use Symfony\AI\Platform\PlatformInterface;
use Symfony\AI\Platform\Test\MockPlatformFactory;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class SamplePlatformFactory
{
    // Without a dispatcher the platform never asks the routing listener, so the admin's provider would be ignored
    public static function create(EventDispatcherInterface $eventDispatcher): PlatformInterface
    {
        return MockPlatformFactory::createPlatform(
            static fn (Model $model, array|string|object $input): string => sprintf(
                "model: %s\nreceived: %s",
                $model->getName(),
                json_encode($input, \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES),
            ),
            eventDispatcher: $eventDispatcher,
        );
    }
}
