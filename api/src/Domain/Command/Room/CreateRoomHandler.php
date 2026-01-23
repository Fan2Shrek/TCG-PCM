<?php

declare(strict_types=1);

namespace App\Domain\Command\Room;

use App\Entity\Room;
use App\Repository\RoomRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class CreateRoomHandler
{
    public function __construct(
        private Security $security,
        private RoomRepository $roomRepository,
    ) {
    }

    public function __invoke(CreateRoomCommand $command): Room
    {
        $user = $this->security->getUser();

        $room = new Room($user);

        $this->roomRepository->save($room);

        return $room;
    }
}
