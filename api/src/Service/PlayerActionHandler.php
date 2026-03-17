<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Room;
use App\Enum\GameEventTypeEnum;
use App\Game\Exception\CardNotInHandException;
use App\Game\Exception\GameAlreadyFinishedException;
use App\Game\Exception\NotYourTurnException;
use App\Game\Exception\UnknowActionException;
use App\Game\PlayerAction;
use App\Game\State\GameEvent;
use App\Game\State\GameState;
use App\Service\Game\GameManager;
use App\Service\Game\ResolutionResult;
use App\Service\Game\State\GameEventRepositoryInterface;
use App\Service\Game\State\GameStateRepositoryInterface;

final class PlayerActionHandler
{
    public function __construct(
        private GameManager $gameManager,
        private GameStateRepositoryInterface $gameStateRepository,
        private GameEventRepositoryInterface $gameEventRepository,
        private GameEventPublisher $gameEventPublisher,
    ) {}

    public function handle(PlayerAction $action, Room $room): void
    {
        $state = $this->gameStateRepository->get($room);
        if (!$state) {
            throw new \RuntimeException('Game state not found for room '.(string) $room->getId());
        }
        $resolution = $this->handleAction($action, $state);

        $lastId = null;
        foreach ($resolution->events as $event) {
            if (!$event->shouldBePersisted()) {
                continue;
            }

            $event = $this->gameEventRepository->save($event, $room->getId()->toString());

            $lastId = $event->id ? $event->id : null;
        }

        $state = $resolution->state;

        if ($lastId) {
            $state = $state->withLastEventId($lastId);
        }

        $this->gameStateRepository->save($state, $room);

        $this->gameEventPublisher->publish($resolution->events, $state, $room);
    }

    private function handleAction(PlayerAction $action, GameState $state): ResolutionResult
    {
        if ($state->getCurrentPlayer()->id !== $action->authorId) {
            throw new NotYourTurnException();
        }

        if ($state->isFinished()) {
            throw new GameAlreadyFinishedException();
        }

        return match ($action->actionId) {
            PlayerAction::PLAY_CARD => $this->playCardAction($action, $state),
            PlayerAction::END_TURN => $this->endTurnAction($action, $state),
            PlayerAction::ATTACK => $this->attackAction($action, $state),
            default => throw new UnknowActionException(),
        };
    }

    private function playCardAction(PlayerAction $action, GameState $state): ResolutionResult
    {
        $card = $action->payload['cardId'] ?? null;
        if (!\is_string($card)) {
            throw new \InvalidArgumentException('cardId is required in payload');
        }

        if (!$state->getCurrentPlayerState()->hasCardInHand($card)) {
            throw new CardNotInHandException($state->getCurrentPlayerState()->player, $card);
        }

        $event = GameEvent::player(GameEventTypeEnum::CARD_PLAYED, [
            'playerId' => $action->authorId,
            'cardId' => $card,
        ]);

        return $this->gameManager->resolve($event, $state);
    }

    private function endTurnAction(PlayerAction $action, GameState $state): ResolutionResult
    {
        $event = GameEvent::player(GameEventTypeEnum::TURN_ENDED, [
            'playerId' => $action->authorId,
        ]);

        return $this->gameManager->resolve($event, $state);
    }

    private function attackAction(PlayerAction $action, GameState $state): ResolutionResult
    {
        $cardId = $action->payload['cardId'] ?? null;

        if (!\is_string($cardId)) {
            throw new \InvalidArgumentException('cardId is required in payload');
        }

        if (!$state->getCurrentPlayerState()->playArea->hasMonsterCard($cardId)) {
            throw new CardNotInHandException($state->getCurrentPlayerState()->player, $cardId);
        }

        if (!($targetId = $action->payload['targetId'] ?? null)) {
            throw new \InvalidArgumentException('targetId is required in payload');
        }

        $event = GameEvent::player(GameEventTypeEnum::ATTACK, [
            'attackerId' => $cardId,
            'targetId' => $targetId,
        ]);

        return $this->gameManager->resolve($event, $state);
    }
}
