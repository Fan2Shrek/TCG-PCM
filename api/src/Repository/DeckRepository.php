<?php

namespace App\Repository;

use App\Entity\Deck;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Deck>
 */
class DeckRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Deck::class);
    }

    /**
     * @return Deck[]
     */
    public function findActiveByUser(User $user): array
    {
        /** @var Deck[] $result */
        $result = $this
            ->createQueryBuilder('d')
            ->where('d.user = :user')
            ->andWhere('d.isDeleted = false')
            ->setParameter('user', $user)
            ->orderBy('d.id', 'ASC')
            ->getQuery()
            ->getResult();

        return $result;
    }

    public function findFirstActiveByUser(User $user): ?Deck
    {
        /** @var Deck|null $result */
        $result = $this
            ->createQueryBuilder('d')
            ->where('d.user = :user')
            ->andWhere('d.isDeleted = false')
            ->andWhere('d.isFavorite = true')
            ->setParameter('user', $user)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $result;
    }

    public function countActiveByUser(User $user): int
    {
        return (int) $this
            ->createQueryBuilder('d')
            ->select('COUNT(d.id)')
            ->where('d.user = :user')
            ->andWhere('d.isDeleted = false')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
