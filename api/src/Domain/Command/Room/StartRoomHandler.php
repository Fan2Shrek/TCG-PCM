<?php

declare(strict_types=1);

namespace App\Domain\Command\Room;

use App\Service\Auth\CurrentUserProviderInterface;
use App\Service\Game\GameInitializer;
use App\Service\Game\State\GameStateRepositoryInterface;
use App\Service\RoomStarter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class StartRoomHandler
{
    public function __construct(
        private CurrentUserProviderInterface $currentUserProvider,
        private GameStateRepositoryInterface $gameStateRepository,
        private RoomStarter $roomStarter,
        private GameInitializer $gameInitializer,
    ) {}

    public function __invoke(StartRoomCommand $command): void
    {
        $room = $command->getCurrentResource();
        $user = $this->currentUserProvider->getCurrentUser();

        if ($room->getOwner() !== $user) {
            throw HttpException::fromStatusCode(Response::HTTP_FORBIDDEN, 'Only the room owner can start the game.');
        }

        if (!$room->getOpponent()) {
            throw HttpException::fromStatusCode(Response::HTTP_BAD_REQUEST, 'Cannot start a game without an opponent.');
        }

        $gameState = $this->roomStarter->startRoom($room);
        $result = $this->gameInitializer->startGame($gameState);

        $this->gameStateRepository->save($result->state, (string) $room->getId());

        // maybe dispatch events
    }
}
