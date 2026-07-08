<?php

declare(strict_types=1);

namespace App\Api\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Room;
use App\Enum\RoomStatusEnum;
use App\Repository\RoomRepository;

final class WaitingRoomProvider implements ProviderInterface
{
    public function __construct(
        private RoomRepository $roomRepository,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $rooms = $this->roomRepository->findBy(
            [
                'status' => RoomStatusEnum::WAITING,
                'opponent' => null,
                'isPrivate' => false,
            ],
            ['createdAt' => 'DESC']
        );

        return array_map(function (Room $room) {
            return [
                'id' => (string) $room->getId(),
                'status' => $room->getStatus()->value,
                'isPrivate' => $room->isPrivate(),
                'createdAt' => $room->getCreatedAt()->format('c'),
                'owner' => [
                    'id' => $room->getOwner()->getId(),
                    'username' => $room->getOwner()->getUsername(),
                ],
                'opponent' => null,
            ];
        }, $rooms);
    }
}
