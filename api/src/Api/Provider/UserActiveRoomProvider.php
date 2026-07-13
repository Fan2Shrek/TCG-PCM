<?php

declare(strict_types=1);

namespace App\Api\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Room;
use App\Enum\RoomStatusEnum;
use App\Repository\RoomRepository;
use App\Service\Auth\CurrentUserProviderInterface;

/**
 * @implements ProviderInterface<Room>
 */
final class UserActiveRoomProvider implements ProviderInterface
{
    public function __construct(
        private RoomRepository $roomRepository,
        private CurrentUserProviderInterface $currentUserProvider,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $user = $this->currentUserProvider->getCurrentUser();
        $qb = $this->roomRepository->createQueryBuilder('r');

        $rooms = $qb
            ->where('(r.owner = :user OR r.opponent = :user)')
            ->andWhere('r.status IN (:statuses)')
            ->setParameter('user', $user)
            ->setParameter('statuses', [RoomStatusEnum::WAITING, RoomStatusEnum::PLAYING])
            ->setMaxResults(1)
            ->getQuery()
            ->getResult();

        if (!\is_array($rooms)) {
            throw new \LogicException('Expected room query result to be an array.');
        }

        /** @var Room[] $rooms */
        return $rooms;
    }
}
