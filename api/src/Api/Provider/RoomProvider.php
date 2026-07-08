<?php

declare(strict_types=1);

namespace App\Api\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Repository\RoomRepository;

final class RoomProvider implements ProviderInterface
{
    public function __construct(
        private RoomRepository $roomRepository,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $room = $this->roomRepository->find($uriVariables['id']);

        if (!$room) {
            return [];
        }

        return [
            'id' => (string) $room->getId(),
            'status' => $room->getStatus()->value,
            'isPrivate' => $room->isPrivate(),
            'createdAt' => $room->getCreatedAt()->format('c'),
            'owner' => [
                'id' => $room->getOwner()->getId(),
                'username' => $room->getOwner()->getUsername(),
            ],
            'opponent' => $room->getOpponent() ? [
                'id' => $room->getOpponent()->getId(),
                'username' => $room->getOpponent()->getUsername(),
            ] : null,
        ];
    }
}
