<?php

declare(strict_types=1);

namespace App\Domain\Command\Room;

use App\Entity\Room;
use App\Service\Auth\CurrentUserProviderInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class JoinRoomHandler
{
    public function __construct(
        private CurrentUserProviderInterface $currentUserProvider,
    ) {}

    public function __invoke(JoinRoomCommand $command): Room
    {
        $user = $this->currentUserProvider->getCurrentUser();

        if (!($deck = $user->getDecks()->first())) {
            throw HttpException::fromStatusCode(Response::HTTP_BAD_REQUEST, 'User has no deck to join a room.');
        }

        $room = $command->getCurrentResource();

        if ($user === $room->getOwner()) {
            throw HttpException::fromStatusCode(Response::HTTP_BAD_REQUEST, 'User cannot join their own room.');
        }

        $room->setOpponent($user);
        $room->setOpponentDeck($deck);

        return $room;
    }
}
