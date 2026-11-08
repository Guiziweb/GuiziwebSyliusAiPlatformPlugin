<?php

declare(strict_types=1);

namespace Tests\Guiziweb\SyliusAiPlatformPlugin\Unit\Platform;

use Composer\InstalledVersions;
use Guiziweb\SyliusAiPlatformPlugin\Entity\Provider\Provider;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

trait PlatformTestSupportTrait
{
    /**
     * The bundle only accepts a bridge that is a regular dependency of the project. The CI installs them that
     * way and sets REQUIRE_PROVIDER_BRIDGES, so that a missing bridge fails there instead of being skipped.
     */
    private static function requireBridges(string ...$packages): void
    {
        foreach ($packages as $package) {
            if (InstalledVersions::isInstalled($package, false)) {
                continue;
            }

            $message = sprintf('"%s" is not a regular dependency: run "composer require %s" to run this test.', $package, $package);

            if ('1' === getenv('REQUIRE_PROVIDER_BRIDGES')) {
                self::fail($message);
            }

            self::markTestSkipped($message);
        }
    }

    private static function kernelParameters(): ParameterBag
    {
        return new ParameterBag([
            'kernel.debug' => false,
            'kernel.environment' => 'test',
            'kernel.project_dir' => sys_get_temp_dir(),
            'kernel.cache_dir' => sys_get_temp_dir(),
            'kernel.build_dir' => sys_get_temp_dir(),
            'kernel.container_class' => 'TestContainer',
            'kernel.bundles' => [],
            'kernel.charset' => 'UTF-8',
        ]);
    }

    /**
     * @param array<string, mixed> $config
     */
    private static function connection(string $code, string $type, array $config = []): Provider
    {
        $connection = new Provider();
        $connection->setCode($code);
        $connection->setType($type);
        $connection->setConfig($config);

        return $connection;
    }
}
