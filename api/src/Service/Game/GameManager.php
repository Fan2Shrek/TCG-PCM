<?php

declare(strict_types=1);

namespace App\Service\Game;

use App\Entity\Deck;
use App\Entity\Room;
use App\Entity\User;
use App\Enum\GameEventTypeEnum;
use App\Enum\RoomStatusEnum;
use App\Game\AbstractCard;
use App\Game\Card\AbstractPlayableCard;
use App\Game\Card\CardState;
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
use App\Service\Game\Factory\GameContextFactoryInterface;
use Symfony\Component\Uid\Uuid;

class GameManager
{
    private const INITIAL_HAND_SIZE = 5;

    public function __construct(
        private CardRegistryInterface $cardRegistry,
        private GameEventApplierInterface $gameEventApplier,
        private GameContextFactoryInterface $gameContextFactory,
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
        if (GameEventTypeEnum::CARD_PLAYED === $event->type) {
            $gameState = $this->gameEventApplier->apply($event, $gameState);
            $events = $this->doPlayCard($event, $gameState);
        } else { // @mago-ignore lint:no-else-clause
            $events = [$event];
        }

        $newState = $this->gameEventApplier->applyMultiple($events, $gameState);

        return $newState->withLastEventId($event->id);
    }

    private function createPlayerStateFromUser(User $user, Deck $deck): PlayerState
    {
        $characterCard = $this->cardRegistry->getCardTemplateById($deck->getCharacterCard());

        if (!$characterCard instanceof AbstractCharacterCard) {
            throw new \RuntimeException('Deck character card is not a character card');
        }

        $player = Player::fromUser($user);
        $cardsIds = $this->createCardsFromDeck($deck);

        return new PlayerState($player, $characterCard->getHealthPoints(), [], $cardsIds);
    }

    /**
     * @return array<string, string>
     */
    private function createCardsFromDeck(Deck $deck): array
    {
        $cardsIds = [];
        foreach ($deck->getCards() as $card) {
            $cardsIds[$this->createCardId()->toString()] = $card;
        }

        return $cardsIds;
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

        $event = GameEvent::player(GameEventTypeEnum::CARD_PLAYED, [
            'playerId' => $action->author->id,
            'cardId' => $card,
        ]);

        $state = $this->gameEventApplier->apply($event, $state);

        return array_merge([$event], $this->doPlayCard($event, $state));
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

    private function createCardId(): Uuid
    {
        return Uuid::v4();
    }

    private function createCardFromState(CardState $state): AbstractCard
    {
        $card = $this->cardRegistry->getCardTemplateById($state->templateId);
        $card->setState($state);

        return $card;
    }

    /**
     * @return GameEvent[]
     */
    private function doPlayCard(GameEvent $event, GameState $state): array
    {
        if (!($cardId = $event->data['cardId'] ?? null) || !\is_string($cardId)) {
            throw new \LogicException('cardId is required to play a card');
        }

        $card = $this->createCardFromState($state->cards[$cardId]);

        if (!$card instanceof AbstractPlayableCard) {
            throw new \LogicException(\sprintf('Card with id %s is not playable', $card->getId()));
        }

        if (!\is_string($event->data['playerId'] ?? null)) {
            throw new \LogicException('playerId is required to play a card');
        }

        $ctx = $this->gameContextFactory->createGameContext($state, $event->data['playerId']);
        $data = $event->data['data'] ?? [];
        $card->play($ctx, \is_array($data) ? $data : []);

        $events = $ctx->flushEvents();

        $events[] = GameEvent::game(GameEventTypeEnum::CARD_DISCARDED, [
            'playerId' => $event->data['playerId'],
            'cardId' => $event->data['cardId'],
        ]);

        return $events;
    }
}
