<?php

declare(strict_types=1);

namespace App\Service\Game;

use App\Enum\GameEventTypeEnum;
use App\Game\Card\AbstractPlayableCard;
use App\Game\GameContext;
use App\Game\State\GameEvent;
use App\Game\State\GameState;
use App\Game\State\PlayerState;

final class GameEventApplier
{
    public function __construct(
        private CardRegistry $cardRegistry,
    ) {}

    public function apply(GameEvent $event, GameState $gameState): GameState
    {
        return match ($event->type) {
            GameEventTypeEnum::CARD_DRAWN => $this->applyCardDrawn($event, $gameState),
            GameEventTypeEnum::CARD_PLAYED => $this->applyCardPlayed($event, $gameState),
            GameEventTypeEnum::ATTACK => $this->applyAttack($event, $gameState),
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

        if ($playerId === null || !\is_string($playerId)) {
            throw new \LogicException('CARD_DRAWN requires a playerId');
        }

        $player = $state->getPlayer($playerId);

        $deck = $player->drawPile;
        $drawn = array_shift($deck);

        $newPlayer = new PlayerState($player->player, [...$player->hand, $drawn], $deck);

        return $state->withUpdatedPlayer($newPlayer);
    }

    private function applyCardPlayed(GameEvent $event, GameState $gameState): GameState
    {
        $cardId = $event->data['cardId'] ?? null;
        $playerId = $event->data['playerId'] ?? null;

        if ($cardId === null || !\is_string($cardId)) {
            throw new \LogicException('CARD_PLAYED requires a cardId');
        }

        if ($playerId === null || !\is_string($playerId)) {
            throw new \LogicException('CARD_PLAYED requires a playerId');
        }

        $card = $this->cardRegistry->getCardInstanceById($cardId);

        if (!$card instanceof AbstractPlayableCard) {
            throw new \LogicException(\sprintf('Card with id %s is not playable', $cardId));
        }

        $ctx = new GameContext($gameState, $playerId);
        $card->play($ctx);

        foreach ($ctx->flushEvents() as $cardEvent) {
            $gameState = $this->apply($cardEvent, $gameState);
        }

        return $gameState;
    }

    private function applyAttack(GameEvent $event, GameState $gameState): GameState
    {
        // @todo apply attack damage to target player or card
        // dispatch DamageEvent here

        return $gameState;
    }
}
