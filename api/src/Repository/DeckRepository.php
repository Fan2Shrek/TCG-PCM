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
     * @return list<Deck>
     */
    public function findActiveByUser(User $user): array
    {
        return $this
            ->createQueryBuilder('d')
            ->where('d.user = :user')
            ->andWhere('d.isDeleted = false')
            ->setParameter('user', $user)
            ->orderBy('d.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findFirstActiveByUser(User $user): ?Deck
    {
        return $this
            ->createQueryBuilder('d')
            ->where('d.user = :user')
            ->andWhere('d.isDeleted = false')
            ->andWhere('d.isFavorite = true')
            ->setParameter('user', $user)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
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
