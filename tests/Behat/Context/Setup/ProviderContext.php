<?php

declare(strict_types=1);

namespace Tests\Guiziweb\SyliusAiPlatformPlugin\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use Behat\Step\Given;
use Doctrine\ORM\EntityManagerInterface;
use Guiziweb\SyliusAiPlatformPlugin\Entity\Provider\Provider;

final readonly class ProviderContext implements Context
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
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
