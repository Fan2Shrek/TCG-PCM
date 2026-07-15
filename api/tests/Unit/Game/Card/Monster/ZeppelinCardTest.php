<?php

declare(strict_types=1);

namespace App\Tests\Unit\Game\Card\Monster;

use App\Game\Card\CardState;
use App\Game\Card\Monster\ZeppelinCard;
use App\Game\GameContext;
use App\Game\Player;
use App\Game\State\GameState;
use App\Game\State\PlayArea;
use App\Game\State\PlayerState;
use App\Tests\Unit\Game\Card\CardTestCase;

final class ZeppelinCardTest extends CardTestCase
{
    public function getCardFQCN(): string
    {
        return ZeppelinCard::class;
    }

    public function testOnMonsterPlayedBuffsBloonsOnBothSidesAndIgnoresOthers(): void
    {
        $card = $this->getCard();

        // player1 (owner) has a bloon (Redbloons) and a non-bloon (DartMonkey).
        // player2 (opponent) has a bloon too (MOAB), which should also be buffed.
        $player1State = new PlayerState(new Player('1', 'Player 1'), 30, 30, 'char1', [], [], 0, new PlayArea([], ['bloon1', 'notBloon']));
        $player2State = new PlayerState(new Player('2', 'Player 2'), 30, 30, 'char2', [], [], 0, new PlayArea([], ['bloon2']));

        $state = new GameState($player1State, $player2State, 1, 0, null, [
            'test_card' => new CardState('test_card', ZeppelinCard::class, '1', []),
            'bloon1' => new CardState('bloon1', 'Redbloons', '1', [], ['bonusAttack' => 5]),
            'notBloon' => new CardState('notBloon', 'DartMonkey', '1', []),
            'bloon2' => new CardState('bloon2', 'MOAB', '2', []),
        ]);

        $ctx = new GameContext($state, '1');

        $card->onMonsterPlayed($ctx);
        $events = $ctx->flushEvents();

        // Two bloons buffed (bloon1, bloon2), each producing an UPDATE_CARD_STATE + a HEAL event.
        self::assertCount(4, $events);

        self::assertSame('bloon1', $events[0]->data['cardId']);
        self::assertSame(['bonusAttack' => 15], $events[0]->data['stateToUpdate']);
        self::assertSame('bloon1', $events[1]->data['targetId']);
        self::assertSame(10, $events[1]->data['amount']);

        self::assertSame('bloon2', $events[2]->data['cardId']);
        self::assertSame(['bonusAttack' => 10], $events[2]->data['stateToUpdate']);
        self::assertSame('bloon2', $events[3]->data['targetId']);
        self::assertSame(10, $events[3]->data['amount']);
    }
}
