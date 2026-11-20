<?php

declare(strict_types=1);

namespace Guiziweb\SyliusAiPlatformPlugin\Agent;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Guiziweb\SyliusAiPlatformPlugin\Entity\Agent\AgentInterface;

final class AgentRowReader implements AgentRowReaderInterface
{
    /**
     * @param class-string<AgentInterface> $agentClass
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly string $agentClass,
    ) {
    }

    public function read(string $code): ?AgentInterface
    {
        $agent = $this->entityManager->createQueryBuilder()
            ->select('agent', 'provider')
            ->from($this->agentClass, 'agent')
            // HINT_REFRESH only refreshes the hydrated entities, so the provider is fetched in the same query
            ->leftJoin('agent.provider', 'provider')
            ->andWhere('agent.code = :code')
            ->setParameter('code', $code)
            ->getQuery()
            ->setHint(Query::HINT_REFRESH, true)
            ->getOneOrNullResult();

        return $agent instanceof AgentInterface ? $agent : null;
    }
}
