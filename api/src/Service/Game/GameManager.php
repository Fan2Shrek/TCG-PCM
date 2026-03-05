<?php

declare(strict_types=1);

namespace App\Service\Game;

use App\Entity\Deck;
use App\Entity\Room;
use App\Entity\User;
use App\Enum\GameEventTypeEnum;
use App\Enum\RoomStatusEnum;
use App\Game\AbstractCard;
use App\Game\Card\AbstractPassiveCard;
use App\Game\Card\AbstractPlayableCard;
use App\Game\Card\CardState;
use App\Game\Card\Character\AbstractCharacterCard;
use App\Game\Card\Interface\TurnAwareInterface;
use App\Game\Exception\CardNotInHandException;
use App\Game\Exception\GameAlreadyFinishedException;
use App\Game\Exception\NotYourTurnException;
use App\Game\Exception\UnknowActionException;
use App\Game\Player;
use App\Game\PlayerAction;
use App\Game\State\GameEvent;
use App\Game\State\GameState;
use App\Game\State\PlayArea;
use App\Game\State\PlayerState;
use App\Service\Game\Factory\GameContextFactoryInterface;
use Symfony\Component\Uid\Uuid;

class GameManager
{
    private const INITIAL_HAND_SIZE = 5;

    public function __construct(
        private CardFactoryInterface $cardFactory,
        private GameContextFactoryInterface $gameContextFactory,
    ) {}

    public function setupRoom(Room $room): GameState
    {
        $room->setStatus(RoomStatusEnum::PLAYING);

        if (!($opponent = $room->getOpponent()) || !($opponentDeck = $room->getOpponentDeck())) {
            throw new \RuntimeException('Room has no opponent');
        }

        return $this->initializeGameState($room, $opponent, $opponentDeck);
    }

    /**
     * @return GameEvent[]
     */
    public function startGame(GameState $initialGameState): array
    {
        $events = [];
        foreach ($initialGameState->getPlayers() as $player) {
            for ($i = 0; $i < self::INITIAL_HAND_SIZE; $i++) {
                $events[] = GameEvent::game(GameEventTypeEnum::CARD_DRAWN, ['playerId' => $player->id]);
            }
        }

        return $events;
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

    /**
     * @return GameEvent[]
     */
    public function getEventsForCard(GameEvent $event, GameState $state): array
    {
        if (!($cardId = $event->data['cardId'] ?? null) || !\is_string($cardId)) {
            throw new \LogicException('cardId is required to play a card');
        }

        if (!($cardState = $state->cards[$cardId] ?? null)) {
            throw new \LogicException(\sprintf('Card with id %s not found in game state', $cardId));
        }

        if (!\is_string($event->data['playerId'] ?? null)) {
            throw new \LogicException('playerId is required to play a card');
        }

        $card = $this->cardFactory->createWithState($cardState->templateId, $cardState);
        $ctx = $this->gameContextFactory->createGameContext($state, $event->data['playerId']);
        $data = $event->data['data'] ?? [];

        $events = [];
        if ($card instanceof AbstractPlayableCard) {
            $card->play($ctx, \is_array($data) ? $data : []);

            $events[] = GameEvent::game(GameEventTypeEnum::CARD_DISCARDED, [
                'playerId' => $event->data['playerId'],
                'cardId' => $event->data['cardId'],
            ]);
        } elseif ($card instanceof AbstractPassiveCard) {
            $card->onCardPlace($ctx);

            $events[] = GameEvent::game(GameEventTypeEnum::CARD_PLACE_IN_PLAY_AREA, [
                'playerId' => $event->data['playerId'],
                'cardId' => $event->data['cardId'],
            ]);
        } else {
            throw new \LogicException('Card must be either a playable or passive card');
        }

        return array_merge($events, $ctx->flushEvents());
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

        return array_merge([$event], $this->getEventsForCard($event, $state));
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

        $cards = $this->getTurnAwareCards($state);

        foreach ($cards as $card) {
            $ctx = $this->gameContextFactory->createGameContext($state, $state->getNextPlayer()->id);
            $card->onTurnEnd($ctx);
            $events = array_merge($events, $ctx->flushEvents());
        }

        if ($this->isNewRound($state, $state->getNextPlayer()->id)) {
            $events[] = GameEvent::game(GameEventTypeEnum::ROUND_STARTED, []);
        }

        $nextPlayerId = $state->getNextPlayer()->id;
        $events[] = GameEvent::game(GameEventTypeEnum::TURN_STARTED, [
            'playerId' => $nextPlayerId,
        ]);

        foreach ($cards as $card) {
            $ctx = $this->gameContextFactory->createGameContext($state, $nextPlayerId);
            $card->onTurnStart($ctx);
            $events = array_merge($events, $ctx->flushEvents());
        }

        $events[] = GameEvent::game(GameEventTypeEnum::CARD_DRAWN, [
            'playerId' => $nextPlayerId,
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

    /**
     * @return array<AbstractCard&TurnAwareInterface>
     */
    private function getTurnAwareCards(GameState $gameState): array
    {
        $cards = [];

        foreach ($this->getAllActiveCards($gameState) as $card) {
            if (!$card instanceof TurnAwareInterface) {
                continue;
            }

            $cards[] = $card;
        }

        return $cards;
    }

    /**
     * @return iterable<AbstractCard>
     */
    private function getAllActiveCards(GameState $gameState): iterable
    {
        foreach ($gameState->getAllActiveCards() as $card) {
            if (!($state = $gameState->getCardState($card))) {
                // @todo maybe log
                continue;
            }

            yield $this->cardFactory->createWithState($state->templateId, $state);
        }
    }

    private function initializeGameState(Room $room, User $opponent, Deck $opponentDeck): GameState
    {
        $player1CharacterCard = $this->cardFactory->create($room->getOwnerDeck()->getCharacterCard());
        $player2CharacterCard = $this->cardFactory->create($opponentDeck->getCharacterCard());

        if (!$player1CharacterCard instanceof AbstractCharacterCard || !$player2CharacterCard instanceof AbstractCharacterCard) {
            throw new \LogicException('Character card must be an instance of AbstractCharacterCard');
        }

        $player1State = $this->createPlayerStateFromUser($room->getOwner(), $room->getOwnerDeck(), $player1CharacterCard);
        $player2State = $this->createPlayerStateFromUser($opponent, $opponentDeck, $player2CharacterCard);

        $player1CharacterCardState = new CardState($player1State->characterCardId, $player1CharacterCard->getId(), $player1State->player->id);
        $player2CharacterCardState = new CardState($player2State->characterCardId, $player2CharacterCard->getId(), $player2State->player->id);

        $player1CharacterCard->setState($player1CharacterCardState);
        $player2CharacterCard->setState($player2CharacterCardState);

        return new GameState($player1State, $player2State, null, $player1State->player->id, [
            $player1CharacterCardState->instanceId => $player1CharacterCardState,
            $player2CharacterCardState->instanceId => $player2CharacterCardState,
        ]);
    }

    private function createPlayerStateFromUser(User $user, Deck $deck, AbstractCharacterCard $characterCard): PlayerState
    {
        $player = Player::fromUser($user);
        $cardsIds = $this->createCardsFromDeck($deck);

        return new PlayerState(
            $player,
            $characterCard->getHealthPoints(),
            $characterCard->getHealthPoints(),
            $this->createCardId()->toString(),
            [],
            $cardsIds,
            new PlayArea(),
        );
    }

    /**
     * @return array<string, string>
     */
    private function createCardsFromDeck(Deck $deck): array
    {
        $cardsIds = [];
        $cards = $deck->getCards();
        shuffle($cards);

        foreach ($cards as $card) {
            $cardsIds[$this->createCardId()->toString()] = $card;
        }

        return $cardsIds;
    }
}
