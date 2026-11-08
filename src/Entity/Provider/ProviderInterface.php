<?php

declare(strict_types=1);

namespace Guiziweb\SyliusAiPlatformPlugin\Entity\Provider;

use Sylius\Component\Payment\Encryption\EncryptionAwareInterface;
use Sylius\Resource\Model\ResourceInterface;

interface ProviderInterface extends ResourceInterface, EncryptionAwareInterface
{
    public function getId(): ?int;

    public function getCode(): ?string;

    public function setCode(?string $code): void;

    public function getType(): ?string;

    public function setType(?string $type): void;

    /**
     * @return array<string, mixed>
     */
    public function getConfig(): array;

    /**
     * @param array<string, mixed> $config
     */
    public function setConfig(array $config): void;

    public function isEnabled(): bool;

    public function setEnabled(bool $enabled): void;
}
