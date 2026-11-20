<?php

declare(strict_types=1);

namespace Guiziweb\SyliusAiPlatformPlugin\Entity\Agent;

use Doctrine\ORM\Mapping as ORM;
use Guiziweb\SyliusAiPlatformPlugin\Entity\Provider\ProviderInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'guiziweb_sylius_ai_platform_agent')]
#[ORM\UniqueConstraint(name: 'guiziweb_ai_agent_code', columns: ['code'])]
class Agent implements AgentInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected ?int $id = null;

    #[ORM\Column(length: 255)]
    protected ?string $code = null;

    #[ORM\Column(length: 255)]
    protected ?string $plugin = null;

    #[ORM\Column(length: 255)]
    protected ?string $name = null;

    #[Assert\NotBlank]
    #[ORM\Column(type: 'text')]
    protected ?string $prompt = null;

    #[ORM\Column(name: 'default_prompt', type: 'text')]
    protected ?string $defaultPrompt = null;

    #[ORM\ManyToOne(targetEntity: ProviderInterface::class)]
    #[ORM\JoinColumn(nullable: true)]
    protected ?ProviderInterface $provider = null;

    #[Assert\Expression(
        expression: 'value !== null or this.getProvider() === null',
        message: 'guiziweb_sylius_ai_platform.agent.model.required',
    )]
    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $model = null;

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

    public function getPlugin(): ?string
    {
        return $this->plugin;
    }

    public function setPlugin(?string $plugin): void
    {
        $this->plugin = $plugin;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getPrompt(): ?string
    {
        return $this->prompt;
    }

    public function setPrompt(?string $prompt): void
    {
        $this->prompt = $prompt;
    }

    public function getDefaultPrompt(): ?string
    {
        return $this->defaultPrompt;
    }

    public function setDefaultPrompt(?string $defaultPrompt): void
    {
        $this->defaultPrompt = $defaultPrompt;
    }

    public function getProvider(): ?ProviderInterface
    {
        return $this->provider;
    }

    public function setProvider(?ProviderInterface $provider): void
    {
        $this->provider = $provider;

        if (null === $provider) {
            $this->model = null;
        }
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(?string $model): void
    {
        $this->model = $model;
    }

    public function usesOwnProvider(): bool
    {
        return $this->provider instanceof ProviderInterface &&
            $this->provider->isEnabled() &&
            null !== $this->model &&
            '' !== $this->model;
    }

    public function isCustomized(): bool
    {
        return self::withoutLineEndingNoise($this->prompt) !== self::withoutLineEndingNoise($this->defaultPrompt);
    }

    public function refreshDeclaration(string $plugin, string $name, string $defaultPrompt): void
    {
        if (!$this->isCustomized()) {
            $this->prompt = $defaultPrompt;
        }

        $this->plugin = $plugin;
        $this->name = $name;
        $this->defaultPrompt = $defaultPrompt;
    }

    private static function withoutLineEndingNoise(?string $text): ?string
    {
        return null === $text ? null : trim(str_replace("\r\n", "\n", $text));
    }
}
