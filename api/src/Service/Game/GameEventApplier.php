<?php

declare(strict_types=1);

namespace App\Service\Game;

use App\Enum\GameEventTypeEnum;
use App\Game\Card\AbstractPlayableCard;
use App\Game\State\GameEvent;
use App\Game\State\GameState;
use App\Service\Game\Factory\GameContextFactoryInterface;

class GameEventApplier
{
    public function __construct(
        private CardRegistry $cardRegistry,
        private GameContextFactoryInterface $gameContextFactory,
    ) {}

    public function apply(GameEvent $event, GameState $gameState): GameState
    {
        return match ($event->type) {
            GameEventTypeEnum::CARD_DRAWN => $this->applyCardDrawn($event, $gameState),
            GameEventTypeEnum::CARD_PLAYED => $this->applyCardPlayed($event, $gameState),
            GameEventTypeEnum::DAMAGE => $this->applyDamage($event, $gameState),
            GameEventTypeEnum::TURN_ENDED => $this->applyTurnEnded($event, $gameState),
            GameEventTypeEnum::TURN_STARTED => $this->applyTurnStarted($event, $gameState),
            GameEventTypeEnum::ROUND_STARTED => $this->applyRoundStarted($event, $gameState),
            GameEventTypeEnum::DICE_ROLLED => $this->applyDiceRolled($event, $gameState),
        };
    }

    /**
     * @param GameEvent[] $events
     */
    public function applyMultiple(array $events, GameState $gameState): GameState
    {
        foreach ($events as $event) {
            $gameState = $this->apply($event, $gameState);
        }

        return $gameState;
    }

    // @ŧodo voir comportement quand plus de cartes
    private function applyCardDrawn(GameEvent $event, GameState $state): GameState
    {
        $playerId = $event->data['playerId'] ?? null;

        if (!\is_string($playerId)) {
            throw new \LogicException('CARD_DRAWN requires a playerId');
        }

        $player = $state->getPlayer($playerId);

        $deck = $player->drawPile;
        $drawn = array_shift($deck);

        $newPlayer = $player->withNewHandAndDeck([...$player->hand, $drawn], $deck);

        return $state->withUpdatedPlayer($newPlayer);
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
        $gameState = $gameState->withUpdatedPlayer($player);

        $card = $this->cardRegistry->getCardInstanceById($cardId);

        if (!$card instanceof AbstractPlayableCard) {
            throw new \LogicException(\sprintf('Card with id %s is not playable', $cardId));
        }

        $ctx = $this->gameContextFactory->createGameContext($gameState, $playerId);
        $card->play($ctx);

        foreach ($ctx->flushEvents() as $cardEvent) {
            $gameState = $this->apply($cardEvent, $gameState);
        }

        return $gameState;
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

        $targetPlayerState = $gameState->getPlayer($target);
        $newPlayerState = $targetPlayerState->withUpdatedHealth($targetPlayerState->healthPoints - $damage);

        return $gameState->withUpdatedPlayer($newPlayerState);
    }

    private function applyTurnEnded(GameEvent $event, GameState $gameState): GameState
    {
        return $gameState->withCurrentPlayer($gameState->getNextPlayer()->id);
    }

    private function applyTurnStarted(GameEvent $event, GameState $gameState): GameState
    {
        // @todo appliquer les effets de début de tour (buffs, dégâts sur la durée, etc.)

        return $gameState;
    }

    private function applyRoundStarted(GameEvent $event, GameState $gameState): GameState
    {
        // @todo appliquer les effets de début de round (buffs, dégâts sur la durée, etc.)

        return $gameState;
    }

    private function applyDiceRolled(GameEvent $event, GameState $gameState): GameState
    {
        // no-op

        return $gameState;
    }
}
