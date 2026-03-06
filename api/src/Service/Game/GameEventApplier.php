<?php

declare(strict_types=1);

namespace App\Service\Game;

use App\Enum\CardEffectEnum;
use App\Enum\GameEventTypeEnum;
use App\Game\Card\CardState;
use App\Game\Card\Effect\EffectState;
use App\Game\Card\MonsterCardState;
use App\Game\State\GameEvent;
use App\Game\State\GameState;
use App\Game\State\PlayerState;

class GameEventApplier implements GameEventApplierInterface
{
    public function apply(GameEvent $event, GameState $gameState): GameState
    {
        $gameState = match ($event->type) {
            GameEventTypeEnum::CARD_DRAWN => $this->applyCardDrawn($event, $gameState),
            GameEventTypeEnum::CARD_PLAYED => $this->applyCardPlayed($event, $gameState),
            GameEventTypeEnum::DAMAGE => $this->applyDamage($event, $gameState),
            GameEventTypeEnum::HEAL => $this->applyHeal($event, $gameState),
            GameEventTypeEnum::TURN_ENDED => $this->applyTurnEnded($event, $gameState),
            GameEventTypeEnum::TURN_STARTED => $this->applyTurnStarted($event, $gameState),
            GameEventTypeEnum::ROUND_STARTED => $this->applyRoundStarted($event, $gameState),
            GameEventTypeEnum::EFFECT_ADDED => $this->applyEffectAdded($event, $gameState),
            GameEventTypeEnum::CARD_DISCARDED => $this->applyCardDiscarded($event, $gameState),
            GameEventTypeEnum::CARD_PLACE_IN_PLAY_AREA => $this->applyCardPlaceInPlayArea($event, $gameState),
            GameEventTypeEnum::CARD_PLACE_IN_MONSTER_AREA => $this->applyCardPlaceInMonsterArea($event, $gameState),
            GameEventTypeEnum::UPDATE_CARD_STATE => $this->applyCardStateUpdate($event, $gameState),
            GameEventTypeEnum::CARD_RUNTIME_VALUE, GameEventTypeEnum::DICE_ROLLED, GameEventTypeEnum::CARD_ACTION_PREVENTED => $this->noOp($event, $gameState),
        };

        return $event->id ? $gameState->withLastEventId($event->id) : $gameState;
    }

    public function applyMultiple(array $events, GameState $gameState): GameState
    {
        foreach ($events as $event) {
            $gameState = $this->apply($event, $gameState);
        }

        return $gameState;
    }

    private function applyCardDrawn(GameEvent $event, GameState $state): GameState
    {
        $playerId = $event->data['playerId'] ?? null;

        if (!\is_string($playerId)) {
            throw new \LogicException('CARD_DRAWN requires a playerId');
        }

        $player = $state->getPlayer($playerId);

        $deck = $player->drawPile;

        $instanceId = array_key_first($deck);
        $drawn = array_shift($deck);

        if (!$instanceId || !$drawn) {
            // @ŧodo voir comportement quand plus de cartes
            throw new \LogicException(\sprintf('Player %s has no more cards to draw', $playerId));
        }

        $newPlayer = $player->withNewHandAndDeck([...$player->hand, $instanceId], $deck);

        $state = $state->withUpdatedPlayer($newPlayer);

        return $state->addCard(new CardState($instanceId, $drawn, $playerId));
    }

    private function applyCardPlayed(GameEvent $event, GameState $gameState): GameState
    {
        $cardId = $event->data['cardId'] ?? null;
        $playerId = $event->data['playerId'] ?? null;

        if (!\is_string($cardId)) {
            throw new \LogicException('CARD_PLAYED requires a cardId');
        }

        if (!\is_string($playerId)) {
            throw new \LogicException('CARD_PLAYED requires a playerId');
        }

        $player = $gameState->getPlayer($playerId);

        if (!$player->hasCardInHand($cardId)) {
            throw new \LogicException(\sprintf('Player %s does not have card %s in hand', $playerId, $cardId));
        }

        $player = $player->removeCardFromHand($cardId);

        return $gameState->withUpdatedPlayer($player);
    }

    private function applyDamage(GameEvent $event, GameState $gameState): GameState
    {
        $target = $event->data['targetId'] ?? null;
        $damage = $event->data['damage'] ?? null;

        if (!\is_string($target)) {
            throw new \LogicException('DAMAGE requires a targetId');
        }

        if (!\is_int($damage)) {
            throw new \LogicException('DAMAGE requires a damage integer');
        }

        if ($damage < 0) {
            throw new \LogicException('DAMAGE requires a positive damage integer');
        }

        $state = match ($target) {
            $gameState->player1->player->id, $gameState->player1->characterCardId => $gameState->player1,
            $gameState->player2->player->id, $gameState->player2->characterCardId => $gameState->player2,
            default => $gameState->getCardState($target),
        };

        if ($state instanceof PlayerState) {
            $newState = $state->withUpdatedHealth($state->healthPoints - $damage);
            $newGameState = $gameState->withUpdatedPlayer($newState);
        } elseif ($state instanceof MonsterCardState) {
            $newState = $state->withCurrentHealthPoints($state->currentHealthPoints - $damage);
            $newGameState = $gameState->withUpdatedCardState($newState);
        } else {
            throw new \LogicException('DAMAGE target must be a player or a monster card');
        }

        $sourceId = $event->data['sourceId'] ?? null;
        if (\is_string($sourceId)) {
            $sourceState = $newGameState->getCardState($sourceId);

            if ($sourceState instanceof MonsterCardState) {
                $newSourceState = $sourceState->withCanAttack(false);
                $newGameState = $newGameState->withUpdatedCardState($newSourceState);
            }
        }

        return $newGameState;
    }

    public function applyHeal(GameEvent $event, GameState $gameState): GameState
    {
        $target = $event->data['targetId'] ?? null;
        $amount = $event->data['amount'] ?? null;

        if (!\is_string($target)) {
            throw new \LogicException('HEAL requires a targetId');
        }

        if (!\is_int($amount)) {
            throw new \LogicException('HEAL requires a amount integer');
        }

        $targetPlayerState = $gameState->getPlayer($target);
        $newHealth = $targetPlayerState->healthPoints + $amount;

        if ($newHealth > $targetPlayerState->maxHealthPoints) {
            $newHealth = $targetPlayerState->maxHealthPoints;
        }

        $newPlayerState = $targetPlayerState->withUpdatedHealth($newHealth);

        return $gameState->withUpdatedPlayer($newPlayerState);
    }

    private function applyTurnEnded(GameEvent $event, GameState $gameState): GameState
    {
        return $gameState->withCurrentPlayer($gameState->getNextPlayer()->id);
    }

    private function applyTurnStarted(GameEvent $event, GameState $gameState): GameState
    {
        if (!($playerId = $event->data['playerId'] ?? null) || !\is_string($playerId)) {
            throw new \LogicException('TURN_STARTED requires a playerId');
        }

        $monsterCards = $gameState->getPlayer($playerId)->playArea->monsterCards;

        foreach ($monsterCards as $cardId) {
            $cardState = $gameState->getCardState($cardId);

            if ($cardState instanceof MonsterCardState) {
                $newCardState = $cardState->withCanAttack(true);
                $gameState = $gameState->withUpdatedCardState($newCardState);
            }
        }

        return $gameState;
    }

    private function applyRoundStarted(GameEvent $event, GameState $gameState): GameState
    {
        // @todo appliquer les effets de début de round (buffs, dégâts sur la durée, etc.)

        return $gameState;
    }

    private function noOp(GameEvent $event, GameState $gameState): GameState
    {
        // no-op

        return $gameState;
    }

    private function applyEffectAdded(GameEvent $event, GameState $gameState): GameState
    {
        if (null === ($cardId = $event->data['cardId'] ?? null) || !\is_string($cardId)) {
            throw new \LogicException('EffectAdded requires a cardId');
        }

        if (null === ($effectId = $event->data['effect'] ?? null)) {
            throw new \LogicException('EffectAdded requires an effect');
        }

        if (!($cardState = $gameState->cards[$cardId] ?? null)) {
            throw new \LogicException('EffectAdded requires a valid cardId');
        }

        if (!($effect = CardEffectEnum::tryFrom((string) $effectId))) {
            throw new \LogicException('EffectAdded requires a valid effect');
        }

        $cardState = $cardState->addEffect(new EffectState($effect, $event->data['effectValues'] ?? []));

        return $gameState->withUpdatedCardState($cardState);
    }

    private function applyCardDiscarded(GameEvent $event, GameState $gameState): GameState
    {
        if (null === ($cardId = $event->data['cardId'] ?? null) || !\is_string($cardId)) {
            throw new \LogicException('DiscardCard requires a cardId');
        }

        $cardState = $gameState->getCardState($cardId);

        if (!$cardState) {
            throw new \LogicException('DiscardCard requires a valid cardId');
        }

        $playerId = $cardState->ownerId;

        $player = $gameState->getPlayer($playerId);
        $player = $player->withDiscardedCard($cardId);

        return $gameState->withUpdatedPlayer($player)->removeCard($cardId);
    }

    private function applyCardPlaceInPlayArea(GameEvent $event, GameState $gameState): GameState
    {
        if (null === ($cardId = $event->data['cardId'] ?? null) || !\is_string($cardId)) {
            throw new \LogicException('DiscardCard requires a cardId');
        }

        if (null === ($playerId = $event->data['playerId'] ?? null) || !\is_string($playerId)) {
            throw new \LogicException('DiscardCard requires a playerId');
        }

        $player = $gameState->getPlayer($playerId);
        $newArea = $player->playArea->addPassiveCard($cardId);

        return $gameState->withUpdatedPlayer($player->withPlayArea($newArea));
    }

    private function applyCardPlaceInMonsterArea(GameEvent $event, GameState $gameState): GameState
    {
        if (null === ($cardId = $event->data['cardId'] ?? null) || !\is_string($cardId)) {
            throw new \LogicException('DiscardCard requires a cardId');
        }

        if (null === ($playerId = $event->data['playerId'] ?? null) || !\is_string($playerId)) {
            throw new \LogicException('DiscardCard requires a playerId');
        }

        if (null === ($healthpoints = $event->data['cardHealthPoints'] ?? null) || !\is_int($healthpoints)) {
            throw new \LogicException('DiscardCard requires a cardHealthPoints');
        }

        if (!($cardState = $gameState->getCardState($cardId))) {
            throw new \LogicException('DiscardCard requires a valid cardId');
        }

        $player = $gameState->getPlayer($playerId);
        $newArea = $player->playArea->addMonsterCard($cardId);
        $newCardState = MonsterCardState::fromParent($cardState, $healthpoints);

        return $gameState->withUpdatedPlayer($player->withPlayArea($newArea))->withUpdatedCardState($newCardState);
    }

    private function applyCardStateUpdate(GameEvent $event, GameState $gameState): GameState
    {
        if (null === ($cardId = $event->data['cardId'] ?? null) || !\is_string($cardId)) {
            throw new \LogicException('Update card state requires a cardId');
        }

        if (null === ($playerId = $event->data['stateToUpdate'] ?? null) || !\is_array($playerId)) {
            throw new \LogicException('Update card state requires a playerId');
        }

        if (!($state = $gameState->getCardState($cardId))) {
            throw new \LogicException('Update card state requires a valid cardId');
        }

        $newState = $state->updateValues($event->data['stateToUpdate']);

        return $gameState->withUpdatedCardState($newState);
    }
}
