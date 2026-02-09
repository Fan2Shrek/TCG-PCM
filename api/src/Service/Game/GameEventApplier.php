<?php

declare(strict_types=1);

namespace App\Service\Game;

use App\Enum\GameEventTypeEnum;
use App\Game\State\GameEvent;
use App\Game\State\GameState;
use App\Game\State\PlayerState;

final class GameEventApplier
{
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
        // cars MUST dispatch new events
        // @todo remove card from player hand and apply card effect

        return $gameState;
    }

    private function applyAttack(GameEvent $event, GameState $gameState): GameState
    {
        // @todo apply attack damage to target player or card
        // dispatch DamageEvent here

        return $gameState;
    }
}
