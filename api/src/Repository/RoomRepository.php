<?php

namespace App\Repository;

use App\Entity\Room;
use App\Interface\DeployAwareInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Room>
 */
class RoomRepository extends ServiceEntityRepository implements DeployAwareInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Room::class);
    }

    public function save(Room $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function onDeploy(): void
    {
        $this
            ->createQueryBuilder('r')
            ->delete()
            ->getQuery()
            ->execute();
    }
}
