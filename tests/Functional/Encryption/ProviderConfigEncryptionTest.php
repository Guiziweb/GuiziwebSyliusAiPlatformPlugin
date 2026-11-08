<?php

declare(strict_types=1);

namespace Tests\Guiziweb\SyliusAiPlatformPlugin\Functional\Encryption;

use Doctrine\ORM\EntityManagerInterface;
use Guiziweb\SyliusAiPlatformPlugin\Entity\Provider\Provider;
use Sylius\Component\Payment\Encryption\EncrypterInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ProviderConfigEncryptionTest extends KernelTestCase
{
    private const SECRET = 'sk-this-must-not-be-readable-in-the-database';

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
        $this->entityManager->getConnection()->rollBack();
        $this->entityManager->clear();

        parent::tearDown();
    }

    public function test_the_database_holds_encrypted_values_and_never_the_secret(): void
    {
        $this->save($this->connection('openai-eu', ['api_key' => self::SECRET, 'region' => 'EU']));

        $stored = $this->storedConfig('openai-eu');

        self::assertStringNotContainsString(self::SECRET, $stored);
        self::assertStringNotContainsString('"EU"', $stored);

        foreach ($this->decode($stored) as $value) {
            self::assertIsString($value);
            self::assertStringEndsWith(EncrypterInterface::ENCRYPTION_SUFFIX, $value);
        }
    }

    public function test_the_names_of_the_settings_stay_readable_in_the_database(): void
    {
        $this->save($this->connection('openai-eu', ['api_key' => self::SECRET, 'region' => 'EU']));

        self::assertEqualsCanonicalizing(['api_key', 'region'], array_keys($this->decode($this->storedConfig('openai-eu'))));
    }

    public function test_the_values_are_decrypted_when_the_connection_is_loaded(): void
    {
        $this->save($this->connection('openai-eu', ['api_key' => self::SECRET, 'region' => 'EU']));

        self::assertEqualsCanonicalizing(['api_key' => self::SECRET, 'region' => 'EU'], $this->find('openai-eu')->getConfig());
    }

    public function test_the_values_keep_their_type_when_the_connection_is_loaded(): void
    {
        $config = ['base_url' => 'https://llm.example.test', 'supports_completions' => false, 'timeout' => 30];
        $this->save($this->connection('mine', $config));

        self::assertEqualsCanonicalizing($config, $this->find('mine')->getConfig());
    }

    public function test_the_connection_is_readable_right_after_it_is_saved(): void
    {
        $connection = $this->connection('openai-eu', ['api_key' => self::SECRET]);
        $this->entityManager->persist($connection);
        $this->entityManager->flush();

        self::assertSame(['api_key' => self::SECRET], $connection->getConfig());
    }

    public function test_a_changed_secret_replaces_the_stored_one(): void
    {
        $this->save($this->connection('openai-eu', ['api_key' => self::SECRET]));

        $connection = $this->find('openai-eu');
        $connection->setConfig(['api_key' => 'sk-the-new-secret']);
        $this->entityManager->flush();
        $this->entityManager->clear();

        $stored = $this->storedConfig('openai-eu');
        self::assertStringNotContainsString(self::SECRET, $stored);
        self::assertStringNotContainsString('sk-the-new-secret', $stored);
        self::assertSame(['api_key' => 'sk-the-new-secret'], $this->find('openai-eu')->getConfig());
    }

    /**
     * @param array<string, mixed> $config
     */
    private function connection(string $code, array $config): Provider
    {
        $connection = new Provider();
        $connection->setCode($code);
        $connection->setType('openai');
        $connection->setConfig($config);

        return $connection;
    }

    private function save(Provider $connection): void
    {
        $this->entityManager->persist($connection);
        $this->entityManager->flush();
        $this->entityManager->clear();
    }

    private function find(string $code): Provider
    {
        $found = $this->entityManager->getRepository(Provider::class)->findOneBy(['code' => $code]);
        self::assertInstanceOf(Provider::class, $found);

        return $found;
    }

    private function storedConfig(string $code): string
    {
        $stored = $this->entityManager->getConnection()->fetchOne('SELECT config FROM guiziweb_sylius_ai_platform_provider WHERE code = ?', [$code]);
        self::assertIsString($stored);

        return $stored;
    }

    /**
     * @return array<string, mixed>
     */
    private function decode(string $json): array
    {
        $decoded = json_decode($json, true, 512, \JSON_THROW_ON_ERROR);
        self::assertIsArray($decoded);

        return $decoded;
    }
}
