<?php

declare(strict_types=1);

namespace Guiziweb\SyliusAiPlatformPlugin\Entity\Provider;

use Doctrine\ORM\Mapping as ORM;
use Guiziweb\SyliusAiPlatformPlugin\Validator\KnownProviderType;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'guiziweb_sylius_ai_platform_provider')]
#[ORM\UniqueConstraint(name: 'guiziweb_ai_provider_code', columns: ['code'])]
#[UniqueEntity(fields: ['code'], message: 'guiziweb_sylius_ai_platform.provider.code.unique')]
class Provider implements ProviderInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected ?int $id = null;

    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[Assert\Regex(pattern: '/^[\w-]+$/', message: 'guiziweb_sylius_ai_platform.provider.code.regex')]
    #[ORM\Column(length: 255)]
    protected ?string $code = null;

    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[KnownProviderType]
    #[ORM\Column(length: 255)]
    protected ?string $type = null;

    /** @var array<string, mixed> */
    #[ORM\Column(type: 'json')]
    protected array $config = [];

    #[ORM\Column(type: 'boolean')]
    protected bool $enabled = true;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): void
    {
        $this->code = $code;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): void
    {
        $this->type = $type;
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function setConfig(array $config): void
    {
        $this->config = $config;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }
}
