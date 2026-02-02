<?php

declare(strict_types=1);

namespace App\Domain\Command\Room;

use App\Entity\Room;
use App\Repository\RoomRepository;
use App\Service\Auth\CurrentUserProviderInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class CreateRoomHandler
{
    public function __construct(
        private CurrentUserProviderInterface $currentUserProvider,
        private RoomRepository $roomRepository,
    ) {}

    public function __invoke(CreateRoomCommand $command): Room
    {
        $user = $this->currentUserProvider->getCurrentUser();
        $room = new Room($user);

        $this->roomRepository->save($room);

        return $room;
    }
}
