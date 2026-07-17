<?php

namespace App\Repository;

use App\Entity\Friendship;
use App\Entity\User;
use App\Enum\FriendshipStatusEnum;
use App\Interface\DeployAwareInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Friendship>
 */
class FriendshipRepository extends ServiceEntityRepository implements DeployAwareInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Friendship::class);
    }

    public function save(Friendship $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findBetween(User $a, User $b): ?Friendship
    {
        /** @var Friendship|null $result */
        return $this
            ->createQueryBuilder('f')
            ->where('(f.requester = :a AND f.addressee = :b) OR (f.requester = :b AND f.addressee = :a)')
            ->setParameter('a', $a)
            ->setParameter('b', $b)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return Friendship[]
     */
    public function findAcceptedForUser(User $user): array
    {
        $result = $this
            ->createQueryBuilder('f')
            ->where('(f.requester = :user OR f.addressee = :user)')
            ->andWhere('f.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', FriendshipStatusEnum::ACCEPTED)
            ->getQuery()
            ->getResult();

        if (!\is_array($result)) {
            throw new \LogicException('Expected friendship query result to be an array.');
        }

        /** @var Friendship[] $result */
        return $result;
    }

    /**
     * @return Friendship[]
     */
    public function findPendingIncomingForUser(User $user): array
    {
        $result = $this
            ->createQueryBuilder('f')
            ->where('f.addressee = :user')
            ->andWhere('f.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', FriendshipStatusEnum::PENDING)
            ->orderBy('f.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        if (!\is_array($result)) {
            throw new \LogicException('Expected friendship query result to be an array.');
        }

        /** @var Friendship[] $result */
        return $result;
    }

    public function areFriends(User $a, User $b): bool
    {
        $friendship = $this->findBetween($a, $b);

        return null !== $friendship && FriendshipStatusEnum::ACCEPTED === $friendship->getStatus();
    }

    public function onDeploy(): void
    {
        $this->createQueryBuilder('f')->delete()->getQuery()->execute();
    }
}
