<?php

declare(strict_types=1);

namespace Tests\Guiziweb\SyliusAiPlatformPlugin\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use Behat\Hook\AfterScenario;
use Behat\Step\Given;
use Doctrine\ORM\EntityManagerInterface;
use Guiziweb\SyliusAiPlatformPlugin\Entity\Agent\Agent;
use Guiziweb\SyliusAiPlatformPlugin\Entity\Provider\Provider;
use Webmozart\Assert\Assert;

final readonly class ProviderContext implements Context
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    #[AfterScenario]
    public function removeWhatTheScenarioCreated(): void
    {
        $this->entityManager->clear();
        $connection = $this->entityManager->getConnection();

        foreach ([Agent::class, Provider::class] as $class) {
            $connection->executeStatement('DELETE FROM ' . $this->entityManager->getClassMetadata($class)->getTableName());
        }
    }

    #[Given('there is a connection named :code for the :type provider')]
    public function thereIsAConnection(string $code, string $type): void
    {
        $this->save($code, $type, ['api_key' => 'sk-test']);
    }

    #[Given('there is a connection named :code for the :type provider with the :setting setting set to :value')]
    public function thereIsAConnectionWithASetting(string $code, string $type, string $setting, string $value): void
    {
        $this->save($code, $type, ['api_key' => 'sk-test', $setting => $value]);
    }

    #[Given('the connection :code is used by an agent')]
    public function theConnectionIsUsedByAnAgent(string $code): void
    {
        $agent = new Agent();
        $agent->setCode('agent_on_' . $code);
        $agent->setName('Agent on ' . $code);
        $agent->setPrompt('Prompt');
        $agent->setDefaultPrompt('Prompt');
        $agent->setProvider($this->entityManager->getRepository(Provider::class)->findOneBy(['code' => $code]));
        $agent->setModel('gpt-4o-mini');

        $this->entityManager->persist($agent);
        $this->entityManager->flush();
    }

    #[Given('the connection :code is disabled')]
    public function theConnectionIsDisabled(string $code): void
    {
        $connection = $this->entityManager->getRepository(Provider::class)->findOneBy(['code' => $code]);
        Assert::notNull($connection);

        $connection->setEnabled(false);
        $this->entityManager->flush();
    }

    /**
     * @param array<string, mixed> $config
     */
    private function save(string $code, string $type, array $config): void
    {
        $connection = new Provider();
        $connection->setCode($code);
        $connection->setType($type);
        $connection->setConfig($config);

        $this->entityManager->persist($connection);
        $this->entityManager->flush();
    }
}
