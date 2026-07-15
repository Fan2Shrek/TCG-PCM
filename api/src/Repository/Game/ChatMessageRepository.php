<?php

declare(strict_types=1);

namespace App\Repository\Game;

use App\Entity\Game\ChatMessage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ChatMessage>
 */
class ChatMessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ChatMessage::class);
    }

    public function save(ChatMessage $chatMessage, bool $flush = true): void
    {
        $this->getEntityManager()->persist($chatMessage);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return list<ChatMessage>
     */
    public function findByRoom(string $roomId): array
    {
        return $this
            ->createQueryBuilder('m')
            ->andWhere('m.roomId = :roomId')
            ->setParameter('roomId', $roomId)
            ->orderBy('m.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
