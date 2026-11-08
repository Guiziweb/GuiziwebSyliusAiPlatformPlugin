<?php

declare(strict_types=1);

namespace Guiziweb\SyliusAiPlatformPlugin\Encryption;

use Guiziweb\SyliusAiPlatformPlugin\Entity\Provider\ProviderInterface;
use Sylius\Component\Payment\Encryption\EncrypterInterface;
use Sylius\Component\Payment\Encryption\EncryptionAwareInterface;
use Sylius\Component\Payment\Encryption\EntityEncrypterInterface;
use Webmozart\Assert\Assert;

/**
 * @implements EntityEncrypterInterface<ProviderInterface>
 */
final readonly class ProviderConfigEncrypter implements EntityEncrypterInterface
{
    public function __construct(private EncrypterInterface $encrypter)
    {
    }

    public function encrypt(EncryptionAwareInterface $resource): void
    {
        Assert::isInstanceOf($resource, ProviderInterface::class);

        $encryptedConfig = [];

        foreach ($resource->getConfig() as $field => $value) {
            $encryptedConfig[$field] = $this->encrypter->encrypt(serialize($value));
        }

        $resource->setConfig($encryptedConfig);
    }

    public function decrypt(EncryptionAwareInterface $resource): void
    {
        Assert::isInstanceOf($resource, ProviderInterface::class);

        $decryptedConfig = [];

        foreach ($resource->getConfig() as $field => $value) {
            $decryptedConfig[$field] = is_string($value) && str_ends_with($value, EncrypterInterface::ENCRYPTION_SUFFIX)
                ? unserialize($this->encrypter->decrypt($value), ['allowed_classes' => false])
                : $value;
        }

        $resource->setConfig($decryptedConfig);
    }
}
