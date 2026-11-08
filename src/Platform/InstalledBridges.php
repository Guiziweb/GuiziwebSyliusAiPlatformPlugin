<?php

declare(strict_types=1);

namespace Guiziweb\SyliusAiPlatformPlugin\Platform;

use Composer\InstalledVersions;

final class InstalledBridges
{
    private const COMPOSER_PACKAGE_TYPE = 'symfony-ai-platform';

    /** @var array<string, class-string>|null */
    private ?array $factoriesByProviderType = null;

    /**
     * @return class-string|null
     */
    public function factoryOf(string $providerType): ?string
    {
        return ($this->factoriesByProviderType ??= $this->readFactories())[$providerType] ?? null;
    }

    /**
     * @return array<string, class-string>
     */
    private function readFactories(): array
    {
        $factories = [];

        foreach (InstalledVersions::getInstalledPackagesByType(self::COMPOSER_PACKAGE_TYPE) as $package) {
            foreach ($this->namespacesOf($package) as $namespace) {
                $factory = $namespace . 'Factory';

                if (class_exists($factory)) {
                    $factories[strtolower(basename(str_replace('\\', '/', rtrim($namespace, '\\'))))] = $factory;
                }
            }
        }

        return $factories;
    }

    /**
     * @return list<string>
     */
    private function namespacesOf(string $package): array
    {
        $installPath = InstalledVersions::getInstallPath($package);
        $composerJson = null === $installPath ? false : file_get_contents($installPath . '/composer.json');
        $definition = false === $composerJson ? [] : json_decode($composerJson, true);
        $psr4 = is_array($definition) && is_array($definition['autoload']['psr-4'] ?? null) ? $definition['autoload']['psr-4'] : [];

        return array_map(strval(...), array_keys($psr4));
    }
}
