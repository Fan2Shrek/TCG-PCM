<?php

declare(strict_types=1);

namespace App\Domain\Command\Room;

use App\Entity\Room;
use App\Repository\RoomRepository;
use App\Service\Auth\CurrentUserProviderInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
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

        if (!($deck = $user->getDecks()->first())) {
            throw HttpException::fromStatusCode(Response::HTTP_BAD_REQUEST, 'User has no deck to create a room.');
        }

        $room = new Room($user);
        $room->setOwnerDeck($deck);

        $this->roomRepository->save($room);

        return $room;
    }
}
