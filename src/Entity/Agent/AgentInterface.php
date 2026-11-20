<?php

declare(strict_types=1);

namespace Guiziweb\SyliusAiPlatformPlugin\Entity\Agent;

use Guiziweb\SyliusAiPlatformPlugin\Entity\Provider\ProviderInterface;
use Sylius\Resource\Model\ResourceInterface;

interface AgentInterface extends ResourceInterface
{
    public function getId(): ?int;

    public function getCode(): ?string;

    public function setCode(?string $code): void;

    public function getPlugin(): ?string;

    public function setPlugin(?string $plugin): void;

    public function getName(): ?string;

    public function setName(?string $name): void;

    public function getPrompt(): ?string;

    public function setPrompt(?string $prompt): void;

    public function getDefaultPrompt(): ?string;

    public function setDefaultPrompt(?string $defaultPrompt): void;

    public function getProvider(): ?ProviderInterface;

    public function setProvider(?ProviderInterface $provider): void;

    public function getModel(): ?string;

    public function setModel(?string $model): void;

    public function usesOwnProvider(): bool;

    public function isCustomized(): bool;

    public function refreshDeclaration(string $plugin, string $name, string $defaultPrompt): void;
}
