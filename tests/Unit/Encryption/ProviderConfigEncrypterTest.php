<?php

declare(strict_types=1);

namespace Tests\Guiziweb\SyliusAiPlatformPlugin\Unit\Encryption;

use Guiziweb\SyliusAiPlatformPlugin\Encryption\ProviderConfigEncrypter;
use Guiziweb\SyliusAiPlatformPlugin\Entity\Provider\Provider;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Payment\Encryption\EncrypterInterface;

final class ProviderConfigEncrypterTest extends TestCase
{
    private ProviderConfigEncrypter $encrypter;

    protected function setUp(): void
    {
        $this->encrypter = new ProviderConfigEncrypter(new class() implements EncrypterInterface {
            public function encrypt(string $data): string
            {
                return strrev($data) . self::ENCRYPTION_SUFFIX;
            }

            public function decrypt(string $data): string
            {
                return strrev(substr($data, 0, -self::ENCRYPTION_SUFFIX_LENGTH));
            }
        });
    }

    public function test_every_value_is_encrypted_and_the_names_of_the_settings_stay_readable(): void
    {
        $connection = $this->connection(['api_key' => 'sk-secret', 'region' => 'EU']);

        $this->encrypter->encrypt($connection);

        self::assertSame(['api_key', 'region'], array_keys($connection->getConfig()));

        foreach ($connection->getConfig() as $value) {
            self::assertIsString($value);
            self::assertStringEndsWith(EncrypterInterface::ENCRYPTION_SUFFIX, $value);
        }

        self::assertNotContains('sk-secret', $connection->getConfig());
    }

    public function test_a_value_keeps_its_type_after_a_round_trip(): void
    {
        $config = ['text' => 'sk-secret', 'empty' => '', 'integer' => 30, 'true' => true, 'false' => false, 'decimal' => 1.5, 'nothing' => null];
        $connection = $this->connection($config);

        $this->encrypter->encrypt($connection);
        $this->encrypter->decrypt($connection);

        self::assertSame($config, $connection->getConfig());
    }

    public function test_a_value_that_is_not_encrypted_is_left_as_it_is(): void
    {
        $connection = $this->connection(['api_key' => 'sk-plain', 'timeout' => 30]);

        $this->encrypter->decrypt($connection);

        self::assertSame(['api_key' => 'sk-plain', 'timeout' => 30], $connection->getConfig());
    }

    public function test_a_decrypted_value_cannot_create_an_object(): void
    {
        $connection = $this->connection(['api_key' => strrev(serialize(new \stdClass())) . EncrypterInterface::ENCRYPTION_SUFFIX]);

        $this->encrypter->decrypt($connection);

        self::assertNotInstanceOf(\stdClass::class, $connection->getConfig()['api_key']);
    }

    /**
     * @param array<string, mixed> $config
     */
    private function connection(array $config): Provider
    {
        $connection = new Provider();
        $connection->setConfig($config);

        return $connection;
    }
}
