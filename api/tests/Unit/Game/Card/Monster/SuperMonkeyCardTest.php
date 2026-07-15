<?php

declare(strict_types=1);

namespace App\Tests\Unit\Game\Card\Monster;

use App\Game\Card\CardState;
use App\Game\Card\Monster\SuperMonkeyCard;
use App\Game\GameContext;
use App\Game\Player;
use App\Game\State\GameState;
use App\Game\State\PlayArea;
use App\Game\State\PlayerState;
use App\Tests\Unit\Game\Card\CardTestCase;

final class SuperMonkeyCardTest extends CardTestCase
{
    public function getCardFQCN(): string
    {
        return SuperMonkeyCard::class;
    }

    public function testOnMonsterPlayedGrantsBonusPerMonkeyOnBoard(): void
    {
        $card = $this->getCard();

        // Owner already has one other monkey (DartMonkey, counted) and one non-monkey (Redbloons, ignored).
        $player1State = new PlayerState(new Player('1', 'Player 1'), 30, 30, 'char1', [], [], 0, new PlayArea([], ['m1', 'm2']));
        $player2State = $this->createPlayerState('2');

        $state = new GameState($player1State, $player2State, 1, 0, null, [
            'test_card' => new CardState('test_card', SuperMonkeyCard::class, '1', []),
            'm1' => new CardState('m1', 'DartMonkey', '1', []),
            'm2' => new CardState('m2', 'Redbloons', '1', []),
        ]);

        $ctx = new GameContext($state, '1');

        $card->onMonsterPlayed($ctx);
        $events = $ctx->flushEvents();

        // 1 (self) + 1 (DartMonkey) = 2 monkeys on board => bonus = 2 * 5 = 10.
        self::assertCount(1, $events);
        self::assertSame('test_card', $events[0]->data['cardId']);
        self::assertSame(['bonusAttack' => 10], $events[0]->data['stateToUpdate']);

        self::assertSame(30, $card->getBaseAttack());
        self::assertSame(30, $card->getHealPoints());
    }

    public function testOnMonsterPlayedWithNoOtherMonkeysGrantsSingleBonus(): void
    {
        $card = $this->getCard();
        $ctx = $this->createGameContext();

        $card->onMonsterPlayed($ctx);
        $events = $ctx->flushEvents();

        // Only self on board => bonus = 1 * 5 = 5.
        self::assertCount(1, $events);
        self::assertSame(['bonusAttack' => 5], $events[0]->data['stateToUpdate']);
        self::assertSame(25, $card->getBaseAttack());
        self::assertSame(25, $card->getHealPoints());
    }
}
