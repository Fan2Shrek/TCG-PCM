<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserBadge;
use App\Enum\BadgeEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserBadge>
 */
class UserBadgeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserBadge::class);
    }

    public function findByUserAndBadge(User $user, BadgeEnum $badge): ?UserBadge
    {
        return $this
            ->createQueryBuilder('ub')
            ->andWhere('ub.user = :user')
            ->andWhere('ub.badgeName = :badge')
            ->setParameter('user', $user)
            ->setParameter('badge', $badge)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function save(UserBadge $userBadge, bool $flush = true): void
    {
        $this->getEntityManager()->persist($userBadge);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
