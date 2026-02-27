<?php

declare(strict_types=1);

namespace App\Service\Game;

use App\Entity\Deck;
use App\Entity\Room;
use App\Entity\User;
use App\Enum\GameEventTypeEnum;
use App\Enum\RoomStatusEnum;
use App\Game\Card\Character\AbstractCharacterCard;
use App\Game\Exception\CardNotInHandException;
use App\Game\Exception\GameAlreadyFinishedException;
use App\Game\Exception\NotYourTurnException;
use App\Game\Exception\UnknowActionException;
use App\Game\Player;
use App\Game\PlayerAction;
use App\Game\State\GameEvent;
use App\Game\State\GameState;
use App\Game\State\PlayerState;

class GameManager
{
    private const INITIAL_HAND_SIZE = 5;

    public function __construct(
        private CardRegistry $cardsRegistry,
        private GameEventApplierInterface $gameEventApplier,
    ) {}

    public function startGame(Room $room): GameState
    {
        $room->setStatus(RoomStatusEnum::PLAYING);

        if (!($opponent = $room->getOpponent()) || !($opponentDeck = $room->getOpponentDeck())) {
            throw new \RuntimeException('Room has no opponent');
        }

        $player1InitialState = $this->createPlayerStateFromUser($room->getOwner(), $room->getOwnerDeck());
        $player2InitialState = $this->createPlayerStateFromUser($opponent, $opponentDeck);

        $initialGameState = new GameState($player1InitialState, $player2InitialState, null);

        $events = [];
        foreach ($initialGameState->getPlayers() as $player) {
            for ($i = 0; $i < self::INITIAL_HAND_SIZE; $i++) {
                $events[] = GameEvent::game(GameEventTypeEnum::CARD_DRAWN, ['playerId' => $player->id]);
            }
        }

        return $this->gameEventApplier->applyMultiple($events, $initialGameState);
    }

    /**
     * @return GameEvent[]
     */
    public function handleAction(PlayerAction $action, GameState $state): array
    {
        if ($state->getCurrentPlayer() !== $action->author) {
            throw new NotYourTurnException();
        }

        if ($state->isFinished()) {
            throw new GameAlreadyFinishedException();
        }

        return match ($action->actionId) {
            PlayerAction::PLAY_CARD => $this->playCard($action, $state),
            PlayerAction::END_TURN => $this->endTurn($action, $state),
            default => throw new UnknowActionException(),
        };
    }

    public function play(GameEvent $event, GameState $gameState): GameState
    {
        $newState = $this->gameEventApplier->apply($event, $gameState);

        return $newState->withLastEventId($event->id);
    }

    private function createPlayerStateFromUser(User $user, Deck $deck): PlayerState
    {
        $characterCard = $this->cardsRegistry->getCardInstanceById($deck->getCharacterCard());

        if (!$characterCard instanceof AbstractCharacterCard) {
            throw new \RuntimeException('Deck character card is not a character card');
        }

        $player = Player::fromUser($user);

        return new PlayerState($player, $characterCard->getHealthPoints(), [], $deck->getCards());
    }

    /**
     * @return GameEvent[]
     */
    private function playCard(PlayerAction $action, GameState $state): array
    {
        $card = $action->payload['cardId'] ?? null;
        if (!\is_string($card)) {
            throw new \InvalidArgumentException('cardId is required in payload');
        }

        if (!$state->getCurrentPlayerState()->hasCardInHand($card)) {
            throw new CardNotInHandException($state->getCurrentPlayerState()->player, $card);
        }

        return [
            GameEvent::player(GameEventTypeEnum::CARD_PLAYED, [
                'playerId' => $action->author->id,
                'cardId' => $card,
            ]),
        ];
    }

    /**
     * @return GameEvent[]
     */
    private function endTurn(PlayerAction $action, GameState $state): array
    {
        $events = [];

        $events[] = GameEvent::player(GameEventTypeEnum::TURN_ENDED, [
            'playerId' => $action->author->id,
        ]);

        if ($this->isNewRound($state, $state->getNextPlayer()->id)) {
            $events[] = GameEvent::game(GameEventTypeEnum::ROUND_STARTED, []);
        }

        $events[] = GameEvent::game(GameEventTypeEnum::TURN_STARTED, [
            'playerId' => $state->getNextPlayer()->id,
        ]);

        return $events;
    }

    private function isNewRound(GameState $state, string $nextPlayerId): bool
    {
        return $nextPlayerId === $state->player1->player->id;
    }
}
