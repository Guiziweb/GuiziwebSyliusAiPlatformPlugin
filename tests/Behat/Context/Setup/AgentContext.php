<?php

declare(strict_types=1);

namespace Tests\Guiziweb\SyliusAiPlatformPlugin\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use Behat\Step\Given;
use Doctrine\ORM\EntityManagerInterface;
use Guiziweb\SyliusAiPlatformPlugin\Entity\Agent\Agent;
use Guiziweb\SyliusAiPlatformPlugin\Entity\Provider\Provider;
use Webmozart\Assert\Assert;

final readonly class AgentContext implements Context
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    #[Given('there is an agent named :name')]
    public function thereIsAnAgent(string $name): void
    {
        $agent = new Agent();
        $agent->setCode(strtolower(str_replace(' ', '_', $name)));
        $agent->setName($name);
        $agent->setPrompt('You are an assistant.');
        $agent->setDefaultPrompt('You are an assistant.');

        $this->entityManager->persist($agent);
        $this->entityManager->flush();
    }

    #[Given('the agent :name uses the connection :code with the model :model')]
    public function theAgentUsesTheConnection(string $name, string $code, string $model): void
    {
        $agent = $this->entityManager->getRepository(Agent::class)->findOneBy(['name' => $name]);
        $connection = $this->entityManager->getRepository(Provider::class)->findOneBy(['code' => $code]);
        Assert::notNull($agent);
        Assert::notNull($connection);

        $agent->setProvider($connection);
        $agent->setModel($model);
        $this->entityManager->flush();
    }
}
