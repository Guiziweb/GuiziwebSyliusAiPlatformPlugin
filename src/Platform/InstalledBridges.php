<?php

declare(strict_types=1);

namespace Guiziweb\SyliusAiPlatformPlugin\Platform;

use Composer\InstalledVersions;

final class InstalledBridges
{
    private const COMPOSER_PACKAGE_TYPE = 'symfony-ai-platform';

    private const PROVIDED_BY_THE_APPLICATION = ['httpClient', 'modelCatalog', 'contract', 'eventDispatcher', 'name', 'modelRouter'];

    /** @var array<string, Bridge>|null */
    private ?array $bridgesByPackage = null;

    /**
     * @return list<Bridge>
     */
    public function all(): array
    {
        return array_values($this->bridgesByPackage ??= $this->read());
    }

    public function get(string $package): ?Bridge
    {
        return ($this->bridgesByPackage ??= $this->read())[$package] ?? null;
    }

    /**
     * @return array<string, Bridge>
     */
    private function read(): array
    {
        $bridges = [];

        foreach (InstalledVersions::getInstalledPackagesByType(self::COMPOSER_PACKAGE_TYPE) as $package) {
            $factories = $this->factoriesOf($package);

            // A package with several factories (Azure has two) does not say which one a connection uses.
            if (1 !== count($factories)) {
                continue;
            }

            $bridge = $this->describe($package, $factories[0]);

            if (null !== $bridge) {
                $bridges[$package] = $bridge;
            }
        }

        return $bridges;
    }

    /**
     * @return list<class-string>
     */
    private function factoriesOf(string $package): array
    {
        $installPath = InstalledVersions::getInstallPath($package);

        if (null === $installPath) {
            return [];
        }

        $composerJson = file_get_contents($installPath . '/composer.json');
        $definition = false === $composerJson ? [] : json_decode($composerJson, true);
        $psr4 = is_array($definition) && is_array($definition['autoload']['psr-4'] ?? null) ? $definition['autoload']['psr-4'] : [];
        $factories = [];

        foreach ($psr4 as $namespace => $directory) {
            $root = rtrim($installPath . '/' . $directory, '/');

            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS)) as $file) {
                if (!$file instanceof \SplFileInfo || 'Factory.php' !== $file->getFilename() || str_contains($file->getPathname(), '/Tests/')) {
                    continue;
                }

                $relativeDirectory = trim(substr($file->getPath(), strlen($root)), '/');
                $class = $namespace . ('' === $relativeDirectory ? '' : str_replace('/', '\\', $relativeDirectory) . '\\') . 'Factory';

                if (class_exists($class) && method_exists($class, 'createPlatform')) {
                    $factories[] = $class;
                }
            }
        }

        return $factories;
    }

    /**
     * @param class-string $factory
     */
    private function describe(string $package, string $factory): ?Bridge
    {
        $fields = [];
        $parameters = [];

        foreach ((new \ReflectionMethod($factory, 'createPlatform'))->getParameters() as $parameter) {
            $parameters[] = $parameter->getName();

            if (in_array($parameter->getName(), self::PROVIDED_BY_THE_APPLICATION, true)) {
                continue;
            }

            $type = $parameter->getType();
            $fieldType = $type instanceof \ReflectionNamedType ? BridgeFieldType::tryFrom($type->getName()) : null;
            $optional = $parameter->isDefaultValueAvailable() || (null !== $type && $type->allowsNull());

            if (null === $fieldType) {
                if (!$optional) {
                    return null;
                }

                continue;
            }

            $default = $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null;
            $fields[] = new BridgeField(
                $parameter->getName(),
                $fieldType,
                !$optional,
                [] !== $parameter->getAttributes(\SensitiveParameter::class),
                is_scalar($default) ? $default : null,
            );
        }

        return new Bridge($package, $factory, $fields, $parameters);
    }
}
