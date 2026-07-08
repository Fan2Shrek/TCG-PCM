<?php

declare(strict_types=1);

namespace App\Api\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Enum\RoomStatusEnum;
use App\Repository\RoomRepository;

final class WaitingRoomProvider implements ProviderInterface
{
    public function __construct(
        private RoomRepository $roomRepository,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        return $this->roomRepository->findBy(
            [
                'status' => RoomStatusEnum::WAITING,
                'opponent' => null,
                'isPrivate' => false,
            ],
            ['createdAt' => 'DESC']
        );
    }
}
