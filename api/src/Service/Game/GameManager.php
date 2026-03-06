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
use App\Game\Card\Interface\CardAwareInterface;
use App\Game\Card\Interface\TurnAwareInterface;
use App\Game\Card\Monster\AbstractMonsterCard;
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
        private CardRuntimeMap $cardRuntimeMap,
        private GameContextFactoryInterface $gameContextFactory,
        private GameEventApplierInterface $gameEventApplier,
    ) {}

    public function setGameContextFactory(GameContextFactoryInterface $factory): GameContextFactoryInterface
    {
        $previousFactory = $this->gameContextFactory;

        $this->gameContextFactory = $factory;

        return $previousFactory;
    }

    public function setupRoom(Room $room): GameState
    {
        $room->setStatus(RoomStatusEnum::PLAYING);

        if (!($opponent = $room->getOpponent()) || !($opponentDeck = $room->getOpponentDeck())) {
            throw new \RuntimeException('Room has no opponent');
        }

        return $this->initializeGameState($room, $opponent, $opponentDeck);
    }

    public function startGame(GameState $initialGameState): ResolutionResult
    {
        $events = [];
        foreach ($initialGameState->getPlayers() as $player) {
            for ($i = 0; $i < self::INITIAL_HAND_SIZE; $i++) {
                $events[] = GameEvent::game(GameEventTypeEnum::CARD_DRAWN, ['playerId' => $player->id]);
            }
        }

        $state = $this->gameEventApplier->applyMultiple($events, $initialGameState);

        $roundStartedEvent = GameEvent::game(GameEventTypeEnum::ROUND_STARTED, []);

        $result = $this->resolve($roundStartedEvent, $state);

        return new ResolutionResult(array_merge($events, $result->events), $result->state);
    }

    public function handleAction(PlayerAction $action, GameState $state): ResolutionResult
    {
        if ($state->getCurrentPlayer() !== $action->author) {
            throw new NotYourTurnException();
        }

        if ($state->isFinished()) {
            throw new GameAlreadyFinishedException();
        }

        return match ($action->actionId) {
            PlayerAction::PLAY_CARD => $this->playCardAction($action, $state),
            PlayerAction::END_TURN => $this->endTurnAction($action, $state),
            default => throw new UnknowActionException(),
        };
    }

    public function resolve(GameEvent $mainEvent, GameState $state): ResolutionResult
    {
        $firstLevelEvents = $allEvents = array_merge([$mainEvent], $this->generateReactions($mainEvent, $state));

        // @ŧodo modify this if we want to do depth events resolution instead of breadth
        foreach ($firstLevelEvents as $event) {
            $state = $this->gameEventApplier->apply($event, $state);
            $allEvents = array_merge($allEvents, $events = $this->collectEventsFromAwareCards($event, $state));
            $state = $this->gameEventApplier->applyMultiple($events, $state);
        }

        return new ResolutionResult($allEvents, $state);
    }

    /**
     * @return GameEvent[]
     */
    private function generateReactions(GameEvent $event, GameState $state): array
    {
        return match ($event->type) {
            GameEventTypeEnum::TURN_ENDED => array_filter([
                $this->isNewRound($state, $state->getNextPlayer()->id) ? GameEvent::game(GameEventTypeEnum::ROUND_STARTED, []) : null,
                GameEvent::game(GameEventTypeEnum::TURN_STARTED, [
                    'playerId' => $state->getNextPlayer()->id,
                ]),
                GameEvent::game(GameEventTypeEnum::CARD_DRAWN, [
                    'playerId' => $state->getNextPlayer()->id,
                ]),
            ]),
            GameEventTypeEnum::CARD_PLAYED => $this->doGenerateReactionsForCardPlayed($event, $state),
            default => [],
        };
    }

    /**
     * @return GameEvent[]
     */
    private function doGenerateReactionsForCardPlayed(GameEvent $event, GameState $state): array
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

        $card = $this->cardRuntimeMap->create($cardState->templateId);
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
        } elseif ($card instanceof AbstractMonsterCard) {
            $card->onMonsterPlayed($ctx);

            $events[] = GameEvent::game(GameEventTypeEnum::CARD_PLACE_IN_MONSTER_AREA, [
                'playerId' => $event->data['playerId'],
                'cardId' => $event->data['cardId'],
                'cardHealthPoints' => $card->getHealPoints(),
            ]);
        } else {
            throw new \LogicException('Card must be either a playable or passive card');
        }

        return array_merge($events, $ctx->flushEvents());
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
            'playerId' => $action->author->id,
            'cardId' => $card,
        ]);

        return $this->resolve($event, $state);
    }

    private function endTurnAction(PlayerAction $action, GameState $state): ResolutionResult
    {
        $event = GameEvent::player(GameEventTypeEnum::TURN_ENDED, [
            'playerId' => $action->author->id,
        ]);

        return $this->resolve($event, $state);
    }

    /**
     * @return GameEvent[]
     */
    private function collectEventsFromAwareCards(GameEvent $event, GameState $state): array
    {
        $events = [];
        $ctx = $this->gameContextFactory->createGameContext($state, $state->currentPlayer);

        switch ($event->type) {
            case GameEventTypeEnum::TURN_ENDED:
                $cards = $this->getTurnAwareCards($state);

                foreach ($cards as $card) {
                    $card->onTurnEnd($ctx);
                    $events = array_merge($events, $ctx->flushEvents());
                }

                break;
            case GameEventTypeEnum::TURN_STARTED:
                $cards = $this->getTurnAwareCards($state);

                foreach ($cards as $card) {
                    $card->onTurnStart($ctx);
                    $events = array_merge($events, $ctx->flushEvents());
                }

                break;
            case GameEventTypeEnum::CARD_DRAWN:
                $cards = $this->getCardAwareCards($state);

                $cardId = $state->getLastAddedCardId();

                if (!$cardId) {
                    throw new \LogicException('No card drawn for CARD_DRAWN event');
                }

                foreach ($cards as $card) {
                    $card->onCardDrawn($cardId, $ctx);
                    $events = array_merge($events, $ctx->flushEvents());
                }

                break;
            case GameEventTypeEnum::CARD_PLAYED:
                $cards = $this->getCardAwareCards($state);
                $cardId = $event->data['cardId'] ?? null;
                if (!\is_string($cardId)) {
                    throw new \LogicException('cardId is required for CARD_DRAWN event');
                }
                $state = $state->getCardState($cardId) ?? throw new \LogicException('Card state not found for cardId '.$cardId);
                $playedCard = $this->cardRuntimeMap->getByState($state);

                foreach ($cards as $card) {
                    $card->onCardPlayed($playedCard, $ctx);
                    $events = array_merge($events, $ctx->flushEvents());
                }
                break;
            default:

            // @todo maybe log unknown event type
        }

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
     * @return array<AbstractCard&CardAwareInterface>
     */
    private function getCardAwareCards(GameState $gameState): array
    {
        $cards = [];

        foreach ($this->getAllActiveCards($gameState) as $card) {
            if (!$card instanceof CardAwareInterface) {
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

            yield $this->cardRuntimeMap->getByState($state);
        }
    }

    private function initializeGameState(Room $room, User $opponent, Deck $opponentDeck): GameState
    {
        $player1CharacterCard = $this->cardRuntimeMap->create($room->getOwnerDeck()->getCharacterCard());
        $player2CharacterCard = $this->cardRuntimeMap->create($opponentDeck->getCharacterCard());

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
