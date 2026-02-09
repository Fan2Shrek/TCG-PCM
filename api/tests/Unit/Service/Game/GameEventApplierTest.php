<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Game;

use App\Game\State\GameEvent;
use App\Enum\GameEventTypeEnum;
use App\Game\Player;
use App\Game\State\GameState;
use App\Game\State\PlayerState;
use App\Service\Game\GameEventApplier;
use PHPUnit\Framework\TestCase;

final class GameEventApplierTest extends TestCase
{
    public function testApplyCardDrawn()
    {
        $eventApplier = new GameEventApplier();
        $state = $this->getInitialGameState();
        $event = new GameEvent(1, GameEventTypeEnum::CARD_DRAWN, GameEvent::PLAYER_EVENT, ['playerId' => 'player1']);

        $newState = $eventApplier->apply($event, $state);

        self::assertCount(1, $newState->player1->hand);
    }

    public function testApplyCardDrawnWithPlayer2()
    {
        $eventApplier = new GameEventApplier();
        $state = $this->getInitialGameState();
        $event = new GameEvent(1, GameEventTypeEnum::CARD_DRAWN, GameEvent::PLAYER_EVENT, ['playerId' => 'player2']);

        $newState = $eventApplier->apply($event, $state);

        self::assertCount(0, $newState->player1->hand);
        self::assertCount(1, $newState->player2->hand);
    }

    private function getInitialGameState(): GameState
    {
        return new GameState(
            new PlayerState(
                new Player(
                    'player1',
                    'Alice',
                    30,
                ),
                [],
                [
                    'D6',
                ],
            ),
            new PlayerState(
                new Player(
                    'player2',
                    'Bob',
                    40,
                ),
                [],
                [
                    'D6',
                ],
            ),
            null,
        );
    }
}
