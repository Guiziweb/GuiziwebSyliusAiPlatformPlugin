<?php

declare(strict_types=1);

namespace Tests\Guiziweb\SyliusAiPlatformPlugin\SampleAssistant;

use Doctrine\Persistence\ObjectManager;
use Guiziweb\SyliusAiPlatformPlugin\Entity\Agent\AgentInterface;
use Guiziweb\SyliusAiPlatformPlugin\Factory\AgentFactoryInterface;
use Sylius\Bundle\FixturesBundle\Fixture\AbstractFixture;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;

final class SampleAssistantFixture extends AbstractFixture
{
    private const CODE = 'sample_assistant';

    private const NAME = 'Sample assistant';

    private const DEFAULT_PROMPT = 'You are the sample assistant of the test application.';

    /**
     * @param RepositoryInterface<AgentInterface> $agentRepository
     */
    public function __construct(
        private readonly RepositoryInterface $agentRepository,
        private readonly AgentFactoryInterface $agentFactory,
        private readonly ObjectManager $objectManager,
    ) {
    }

    public function getName(): string
    {
        return 'sample_assistant';
    }

    /**
     * @param array<string, mixed> $options
     */
    public function load(array $options): void
    {
        $agent = $this->agentRepository->findOneBy(['code' => self::CODE]);

        if ($agent instanceof AgentInterface) {
            $agent->refreshDeclaration(self::NAME, self::DEFAULT_PROMPT);
        } else {
            $agent = $this->agentFactory->createDeclared(self::CODE, self::NAME, self::DEFAULT_PROMPT);
            $this->objectManager->persist($agent);
        }

        $this->objectManager->flush();
    }
}
