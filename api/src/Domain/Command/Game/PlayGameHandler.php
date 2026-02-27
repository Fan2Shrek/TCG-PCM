<?php

declare(strict_types=1);

namespace App\Domain\Command\Game;

use App\Game\Exception\GameException;
use App\Game\PlayerAction;
use App\Service\Auth\CurrentUserProviderInterface;
use App\Service\Game\GameManager;
use App\Service\Game\State\GameEventRepositoryInterface;
use App\Service\Game\State\GameStateRepositoryInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class PlayGameHandler
{
    public function __construct(
        private GameManager $gameManager,
        private GameStateRepositoryInterface $gameStateRepository,
        private GameEventRepositoryInterface $gameEventRepository,
        private CurrentUserProviderInterface $currentUserProvider,
    ) {}

    public function __invoke(PlayGameCommand $command): void
    {
        $user = $this->currentUserProvider->getCurrentUser();

        if (!\in_array($command->actionId, PlayerAction::ACTIONS, true)) {
            throw HttpException::fromStatusCode(Response::HTTP_BAD_REQUEST, 'Invalid action id');
        }

        $room = $command->getCurrentResource();
        $state = $this->gameStateRepository->get($room);

        if (!$state) {
            throw HttpException::fromStatusCode(Response::HTTP_NOT_FOUND, 'Game state not found');
        }

        $authorState = $state->getPlayer((string) $user->getId());
        $action = new PlayerAction($authorState->player, $command->actionId, $command->payload);

        try {
            $events = $this->gameManager->handleAction($action, $state);
        } catch (GameException $e) {
            // do something here

            throw $e;
        }

        foreach ($events as &$event) {
            $event = $this->gameEventRepository->save($event, $room->getId()->toString());
        }

        foreach ($events as $gameEvent) {
            $state = $this->gameManager->play($gameEvent, $state);
        }

        $this->gameStateRepository->save($state, $room);

        // sse $event;
    }
}
