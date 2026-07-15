<?php

namespace App\Repository;

use App\Entity\Trade;
use App\Entity\User;
use App\Enum\TradeStatusEnum;
use App\Interface\DeployAwareInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Trade>
 */
class TradeRepository extends ServiceEntityRepository implements DeployAwareInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Trade::class);
    }

    public function save(Trade $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findActiveForUser(User $user): ?Trade
    {
        /** @var Trade|null $result */
        $result = $this
            ->createQueryBuilder('t')
            ->where('(t.initiator = :user OR t.recipient = :user)')
            ->andWhere('t.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', TradeStatusEnum::ACTIVE)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $result;
    }

    public function onDeploy(): void
    {
        $this->createQueryBuilder('t')->delete()->getQuery()->execute();
    }
}
