<?php

declare(strict_types=1);

namespace Tests\Guiziweb\SyliusAiPlatformPlugin\Functional;

use Doctrine\ORM\EntityManagerInterface;
use Guiziweb\SyliusAiPlatformPlugin\Entity\Provider\Provider;
use Guiziweb\SyliusAiPlatformPlugin\Entity\Provider\ProviderInterface;
use Guiziweb\SyliusAiPlatformPlugin\Factory\ProviderFactoryInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ProviderConnectionTest extends KernelTestCase
{
    private const CODE_ALREADY_USED = 'guiziweb_sylius_ai_platform.provider.code.unique';

    private const CODE_NOT_ALLOWED = 'guiziweb_sylius_ai_platform.provider.code.regex';

    private const TYPE_UNKNOWN = 'guiziweb_sylius_ai_platform.provider.type.unknown';

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

    public function test_a_connection_is_saved_and_read_back(): void
    {
        $this->save($this->connection('openai-eu', 'openai', ['api_key' => 'sk-1', 'region' => 'EU']));

        $found = $this->find('openai-eu');

        self::assertSame('openai', $found->getType());
        self::assertSame(['api_key' => 'sk-1', 'region' => 'EU'], $this->sortedByName($found->getConfig()));
        self::assertTrue($found->isEnabled());
    }

    public function test_a_connection_is_updated(): void
    {
        $this->save($this->connection('openai-eu', 'openai', ['api_key' => 'sk-1']));

        $connection = $this->find('openai-eu');
        $connection->setConfig(['api_key' => 'sk-2', 'region' => 'US']);
        $connection->setEnabled(false);
        $this->entityManager->flush();
        $this->entityManager->clear();

        $found = $this->find('openai-eu');
        self::assertSame(['api_key' => 'sk-2', 'region' => 'US'], $this->sortedByName($found->getConfig()));
        self::assertFalse($found->isEnabled());
    }

    public function test_a_connection_is_deleted(): void
    {
        $this->save($this->connection('openai-eu', 'openai', ['api_key' => 'sk-1']));

        $this->entityManager->remove($this->find('openai-eu'));
        $this->entityManager->flush();
        $this->entityManager->clear();

        self::assertNull($this->entityManager->getRepository(Provider::class)->findOneBy(['code' => 'openai-eu']));
    }

    public function test_two_connections_can_use_the_same_provider(): void
    {
        $this->save($this->connection('openai-eu', 'openai', ['api_key' => 'sk-1', 'region' => 'EU']));

        self::assertSame([], $this->violations($this->connection('openai-us', 'openai', ['api_key' => 'sk-2', 'region' => 'US'])));
    }

    public function test_a_code_that_is_already_used_is_refused(): void
    {
        $this->save($this->connection('openai-eu', 'openai', ['api_key' => 'sk-1']));

        self::assertSame(
            [self::CODE_ALREADY_USED],
            $this->violations($this->connection('openai-eu', 'mistral', ['api_key' => 'k'])),
        );
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function codesThatAreNotAllowed(): iterable
    {
        yield 'a space' => ['Bad Code'];
        yield 'a punctuation mark' => ['openai!'];
        yield 'only punctuation' => [';,,,'];
        yield 'an accent' => ['compté'];
    }

    #[DataProvider('codesThatAreNotAllowed')]
    public function test_a_code_with_forbidden_characters_is_refused(string $code): void
    {
        self::assertSame([self::CODE_NOT_ALLOWED], $this->violations($this->connection($code, 'openai', [])));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function codesThatAreAllowed(): iterable
    {
        yield 'lower case' => ['openai'];
        yield 'with a dash' => ['openai-eu'];
        yield 'with an underscore' => ['openai_eu'];
        yield 'with capitals' => ['MyConnection'];
        yield 'with digits' => ['openai2'];
    }

    #[DataProvider('codesThatAreAllowed')]
    public function test_a_code_with_letters_digits_dashes_and_underscores_is_accepted(string $code): void
    {
        self::assertSame([], $this->violations($this->connection($code, 'openai', [])));
    }

    public function test_a_connection_without_code_is_refused(): void
    {
        $violations = $this->violations($this->connection('', 'openai', []));

        self::assertContains('This value should not be blank.', $violations);
    }

    public function test_an_unknown_provider_type_is_refused(): void
    {
        self::assertSame([self::TYPE_UNKNOWN], $this->violations($this->connection('mine', 'not-a-provider', [])));
    }

    public function test_the_factory_creates_a_connection_of_a_known_provider_type(): void
    {
        self::assertSame('mistral', $this->factory()->createForType('mistral')->getType());
    }

    public function test_the_factory_refuses_an_unknown_provider_type(): void
    {
        $this->expectException(NotFoundHttpException::class);

        $this->factory()->createForType('not-a-provider');
    }

    /**
     * @param array<string, mixed> $config
     */
    private function connection(string $code, string $type, array $config): ProviderInterface
    {
        $connection = new Provider();
        $connection->setCode($code);
        $connection->setType($type);
        $connection->setConfig($config);

        return $connection;
    }

    /**
     * @param array<string, mixed> $config
     *
     * @return array<string, mixed>
     */
    private function sortedByName(array $config): array
    {
        ksort($config);

        return $config;
    }

    private function save(ProviderInterface $connection): void
    {
        $this->entityManager->persist($connection);
        $this->entityManager->flush();
        $this->entityManager->clear();
    }

    private function find(string $code): ProviderInterface
    {
        $found = $this->entityManager->getRepository(Provider::class)->findOneBy(['code' => $code]);
        self::assertInstanceOf(ProviderInterface::class, $found);

        return $found;
    }

    /**
     * @return list<string> the message template of each violation, which is the translation key
     */
    private function violations(ProviderInterface $connection): array
    {
        $validator = self::getContainer()->get('validator');
        self::assertInstanceOf(ValidatorInterface::class, $validator);

        $templates = [];

        foreach ($validator->validate($connection) as $violation) {
            $templates[] = (string) $violation->getMessageTemplate();
        }

        return $templates;
    }

    private function factory(): ProviderFactoryInterface
    {
        $factory = self::getContainer()->get('guiziweb_sylius_ai_platform.custom_factory.provider');
        self::assertInstanceOf(ProviderFactoryInterface::class, $factory);

        return $factory;
    }
}
