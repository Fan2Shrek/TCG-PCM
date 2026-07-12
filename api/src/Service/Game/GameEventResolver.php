<?php

declare(strict_types=1);

namespace App\Service\Game;

use App\Enum\GameEventTypeEnum;
use App\Game\AbstractCard;
use App\Game\Card\AbstractPassiveCard;
use App\Game\Card\AbstractPlayableCard;
use App\Game\Card\Interface\CardAwareInterface;
use App\Game\Card\Interface\DeathAwareInterface;
use App\Game\Card\Interface\TurnAwareInterface;
use App\Game\Card\Monster\AbstractMonsterCard;
use App\Game\Card\MonsterCardState;
use App\Game\Exception\CardCannotAttackExpcetion;
use App\Game\Exception\NotEnoughCoinsException;
use App\Game\State\GameEvent;
use App\Game\State\GameState;
use App\Service\Game\Factory\GameContextFactoryInterface;

class GameEventResolver
{
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

    public function resolve(GameEvent $mainEvent, GameState $state): ResolutionResult
    {
        $firstLevelEvents = $allEvents = array_merge([$mainEvent], $this->generateReactions($mainEvent, $state));

        // @ŧodo modify this if we want to do depth events resolution instead of breadth
        foreach ($firstLevelEvents as $event) {
            // First we apply the first level event
            $state = $this->gameEventApplier->apply($event, $state);

            // Then we calculate systems events just in case
            $systemEvents = $this->generateSystemsEvent($state);
            $state = $this->gameEventApplier->applyMultiple($systemEvents, $state);
            $allEvents = array_merge($allEvents, $systemEvents);

            // Then we process aware cards
            $allEvents = array_merge($allEvents, $events = $this->collectEventsFromAwareCards($event, $state));
            $state = $this->gameEventApplier->applyMultiple($events, $state);
        }

        return new ResolutionResult($allEvents, $state);
    }

    /**
     * This method generates system events that should be checked after each event resolution, such as player death, monster death, etc.
     *
     * @return GameEvent[]
     */
    private function generateSystemsEvent(GameState $state): array
    {
        $events = [];

        foreach ([$state->player1, $state->player2] as $playerState) {
            if ($playerState->healthPoints > 0) {
                continue;
            }

            $events[] = GameEvent::game(GameEventTypeEnum::PLAYER_DIED, [
                'playerId' => $playerState->player->id,
            ]);
        }

        foreach ($state->getAllMonsters() as $monsterCardId) {
            $cardState = $state->getCardState($monsterCardId);
            if (!$cardState) {
                continue;
            }

            $card = $this->cardRuntimeMap->getByState($cardState);

            if (!$card instanceof AbstractMonsterCard) {
                continue;
            }

            if ($card->getCurrentHealthPoints() <= 0) {
                $events[] = GameEvent::game(GameEventTypeEnum::MONSTER_DIED, [
                    'playerId' => $cardState->ownerId,
                    'cardId' => $monsterCardId,
                ]);
            }
        }

        $subEvents = [];
        foreach ($events as $deathEvent) {
            $subEvents = array_merge($subEvents, $this->generateReactions($deathEvent, $state));
        }

        return array_merge($events, $subEvents);
    }

    /**
     * @return GameEvent[]
     */
    private function generateReactions(GameEvent $event, GameState $state): array
    {
        $events = [];
        $playerId = $state->currentPlayer;

        switch ($event->type) {
            case GameEventTypeEnum::TURN_ENDED:
                $playerId = $state->getNextPlayer()->id;
                if ($this->isNewRound($state, $state->getNextPlayer()->id)) {
                    $events[] = GameEvent::game(GameEventTypeEnum::ROUND_STARTED, []);
                }
                $events[] = GameEvent::game(GameEventTypeEnum::TURN_STARTED, [
                    'playerId' => $playerId,
                ]);
            // no-break
            case GameEventTypeEnum::TURN_STARTED:
                $events[] = GameEvent::game(GameEventTypeEnum::COINS_GAINED, [
                    'playerId' => $playerId,
                    'amount' => $this->calculateCoinsGain($state),
                ]);
                $events[] = GameEvent::game(GameEventTypeEnum::CARD_DRAWN, [
                    'playerId' => $playerId,
                ]);
                $events = array_merge($events, $this->restoreMonstersAttack($state, $playerId));
                break;
            case GameEventTypeEnum::CARD_PLAYED:
                $events = $this->doGenerateReactionsForCardPlayed($event, $state);
                break;
            case GameEventTypeEnum::ATTACK:
                $events = $this->doGenerateReactionsForAttack($event, $state);
                break;
            case GameEventTypeEnum::PLAYER_DIED:
            case GameEventTypeEnum::MONSTER_DIED:
                $events = $this->doGenerareReactionsForDeath($event, $state);
            default:
                break;
        }

        return $events;
    }

    /**
     * @return GameEvent[]
     */
    private function restoreMonstersAttack(GameState $state, string $playerId): array
    {
        $events = [];

        foreach ($state->getPlayer($playerId)->playArea->monsterCards as $cardId) {
            $currentState = $state->getCardState($cardId);

            if (!$currentState instanceof MonsterCardState || $currentState->canAttack) {
                continue;
            }

            $events[] = GameEvent::game(GameEventTypeEnum::UPDATE_CARD_STATE, [
                'cardId' => $cardId,
                'canAttack' => true,
            ]);
        }

        return $events;
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

        $card = $this->cardRuntimeMap->getByState($cardState);
        $ctx = $this->gameContextFactory->createGameContext($state, $event->data['playerId']);
        $data = $event->data['data'] ?? [];
        $events = [];

        $cardCost = $card->getCost();

        if ($state->getCurrentPlayerState()->coins < $cardCost) {
            throw new NotEnoughCoinsException($cardCost, $state->getCurrentPlayerState()->coins);
        }

        $events[] = GameEvent::game(GameEventTypeEnum::COINS_LOST, [
            'playerId' => $event->data['playerId'],
            'amount' => $cardCost,
        ]);

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

    /**
     * @return GameEvent[]
     */
    private function doGenerateReactionsForAttack(GameEvent $event, GameState $state): array
    {
        if (!\is_string($event->data['attackerId'] ?? null)) {
            throw new \LogicException('attackerId is required for attack event');
        }

        if (!\is_string($event->data['targetId'] ?? null)) {
            throw new \LogicException('targetId is required for attack event');
        }

        $attackerCardState = $state->getCardState($attackerId = $event->data['attackerId']);

        if (!$attackerCardState) {
            throw new \LogicException('Attacker card state not found for cardId '.$event->data['attackerId']);
        }

        $card = $this->cardRuntimeMap->getByState($attackerCardState);

        if (!$card instanceof AbstractMonsterCard) {
            throw new \LogicException('Only monster cards can attack');
        }

        if (!$card->canAttack()) {
            throw new CardCannotAttackExpcetion('Card cannot attack');
        }

        $targetId = match (true) {
            \in_array($event->data['targetId'], [$state->getOtherPlayerState()->characterCardId, $state->getOtherPlayerState()->player->id], true)
                => $state->getOtherPlayerState()->player->id,
            \in_array($event->data['targetId'], $state->getOtherPlayerState()->playArea->monsterCards, true) => $event->data['targetId'],
            default => throw new \LogicException('Invalid targetId '.$event->data['targetId']),
        };

        $event = GameEvent::game(GameEventTypeEnum::DAMAGE, [
            'targetId' => $targetId,
            'damage' => $card->getAttack(),
            'sourceId' => $attackerId,
        ]);


        $ctx = $this->gameContextFactory->createGameContext($state, $attackerCardState->ownerId);
        $card->onAttack($ctx);

        return array_merge([
            $event,
            GameEvent::game(GameEventTypeEnum::UPDATE_CARD_STATE, [
                'cardId' => $attackerId,
                'canAttack' => false,
            ]),
        ], $ctx->flushEvents());
    }

    /**
     * @return GameEvent[]
     */
    private function doGenerareReactionsForDeath(GameEvent $event, GameState $state): array
    {
        $events = [];

        if (GameEventTypeEnum::PLAYER_DIED === $event->type) {
            return $this->collectEventsFromAwareCards($event, $state);
        }

        if (GameEventTypeEnum::MONSTER_DIED === $event->type) {
            $cardId = $event->data['cardId'] ?? null;

            if (!$cardId || !\is_string($cardId)) {
                throw new \LogicException('cardId is required for MONSTER_DIED event');
            }

            if (!($cardState = $state->getCardState($cardId))) {
                throw new \LogicException('Card state not found for cardId '.$cardId);
            }

            $card = $this->cardRuntimeMap->getByState($cardState);

            if (!$card instanceof AbstractMonsterCard) {
                throw new \LogicException('Card with id '.$cardId.' is not a monster card');
            }

            if (!($playerId = $event->data['playerId'] ?? null) || !\is_string($playerId)) {
                throw new \LogicException('No playerId found');
            }

            $ctx = $this->gameContextFactory->createGameContext($state, $playerId);
            $card->onMonsterDeath($ctx);

            $events = array_merge($ctx->flushEvents(), $this->collectEventsFromAwareCards($event, $state));
        }

        return $events;
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

                if ([] === $cards) {
                    return [];
                }

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
            case GameEventTypeEnum::MONSTER_DIED:
                $cards = $this->getDeathAwareCards($state);
                $cardId = $event->data['cardId'] ?? null;
                if (!\is_string($cardId)) {
                    throw new \LogicException('cardId is required for CARD_DRAWN event');
                }
                $state = $state->getCardState($cardId) ?? throw new \LogicException('Card state not found for cardId '.$cardId);
                $playedCard = $this->cardRuntimeMap->getByState($state);

                foreach ($cards as $card) {
                    $card->onCardDeath($playedCard, $ctx);
                    $events = array_merge($events, $ctx->flushEvents());
                }
                break;
            case GameEventTypeEnum::PLAYER_DIED:
                $cards = $this->getDeathAwareCards($state);
                $playerId = $event->data['playerId'] ?? null;
                if (!\is_string($playerId)) {
                    throw new \LogicException('cardId is required for PLAYER_DIED event');
                }
                foreach ($cards as $card) {
                    $card->onPlayerDeath($ctx, $playerId);
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
     * @return array<AbstractCard&DeathAwareInterface>
     */
    private function getDeathAwareCards(GameState $gameState): array
    {
        $cards = [];

        foreach ($this->getAllActiveCards($gameState) as $card) {
            if (!$card instanceof DeathAwareInterface) {
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

    private function calculateCoinsGain(GameState $state): int
    {
        // maybe round based

        return 3;
    }
}
