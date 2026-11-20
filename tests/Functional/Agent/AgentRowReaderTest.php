<?php

declare(strict_types=1);

namespace Tests\Guiziweb\SyliusAiPlatformPlugin\Functional\Agent;

use Doctrine\ORM\EntityManagerInterface;
use Guiziweb\SyliusAiPlatformPlugin\Agent\AgentRowReaderInterface;
use Guiziweb\SyliusAiPlatformPlugin\Entity\Agent\Agent;
use Guiziweb\SyliusAiPlatformPlugin\Entity\Provider\Provider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class AgentRowReaderTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        self::bootKernel();
        $entityManager = self::getContainer()->get('doctrine.orm.entity_manager');
        self::assertInstanceOf(EntityManagerInterface::class, $entityManager);
        $this->entityManager = $entityManager;
        $this->entityManager->getConnection()->beginTransaction();
    }

    protected function tearDown(): void
    {
        $connection = $this->entityManager->getConnection();

        if ($connection->isTransactionActive()) {
            $connection->rollBack();
        }

        $this->entityManager->clear();

        parent::tearDown();
    }

    public function test_a_provider_disabled_since_the_last_read_is_read_disabled(): void
    {
        $providerId = $this->saveAgentOnAnEnabledProvider();
        $reader = $this->reader();

        self::assertTrue($reader->read('reader_test')?->getProvider()?->isEnabled());

        $this->entityManager->getConnection()->executeStatement(
            'UPDATE guiziweb_sylius_ai_platform_provider SET enabled = 0 WHERE id = :id',
            ['id' => $providerId],
        );

        self::assertFalse($reader->read('reader_test')?->getProvider()?->isEnabled());
    }

    private function saveAgentOnAnEnabledProvider(): int
    {
        $provider = new Provider();
        $provider->setCode('reader-test');
        $provider->setType('mistral');
        $provider->setConfig(['api_key' => 'key']);

        $agent = new Agent();
        $agent->setCode('reader_test');
        $agent->setName('Reader test');
        $agent->setPrompt('Prompt');
        $agent->setDefaultPrompt('Prompt');
        $agent->setProvider($provider);
        $agent->setModel('mistral-small-latest');

        $this->entityManager->persist($provider);
        $this->entityManager->persist($agent);
        $this->entityManager->flush();

        return (int) $provider->getId();
    }

    private function reader(): AgentRowReaderInterface
    {
        $reader = self::getContainer()->get('guiziweb_sylius_ai_platform.agent.row_reader');
        self::assertInstanceOf(AgentRowReaderInterface::class, $reader);

        return $reader;
    }
}
