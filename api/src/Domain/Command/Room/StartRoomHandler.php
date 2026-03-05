<?php

declare(strict_types=1);

namespace App\Domain\Command\Room;

use App\Service\Auth\CurrentUserProviderInterface;
use App\Service\Game\GameEventApplierInterface;
use App\Service\Game\GameManager;
use App\Service\Game\State\GameStateRepositoryInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class StartRoomHandler
{
    public function __construct(
        private CurrentUserProviderInterface $currentUserProvider,
        private GameStateRepositoryInterface $gameStateRepository,
        private GameManager $gameManager,
        private GameEventApplierInterface $gameEventApplier,
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

        $gameState = $this->gameManager->setupRoom($room);
        $events = $this->gameManager->startGame($gameState);

        $gameState = $this->gameEventApplier->applyMultiple($events, $gameState);

        $this->gameStateRepository->save($gameState, $room);
    }
}
