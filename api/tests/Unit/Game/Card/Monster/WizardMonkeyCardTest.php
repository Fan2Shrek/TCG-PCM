<?php

declare(strict_types=1);

namespace App\Tests\Unit\Game\Card\Monster;

use App\Game\Card\CardState;
use App\Game\Card\Monster\WizardMonkeyCard;
use App\Game\GameContext;
use App\Game\Player;
use App\Game\State\GameState;
use App\Game\State\PlayArea;
use App\Game\State\PlayerState;
use App\Tests\Unit\Game\Card\CardTestCase;

final class WizardMonkeyCardTest extends CardTestCase
{
    public function getCardFQCN(): string
    {
        return WizardMonkeyCard::class;
    }

    public function testOnMonsterPlayedDamagesAllOpponentTargets(): void
    {
        $card = $this->getCard();

        $player1State = $this->createPlayerState('1');
        $player2State = new PlayerState(new Player('2', 'Player 2'), 30, 30, 'char2', [], [], 0, new PlayArea([], ['m1', 'm2']));

        $state = new GameState($player1State, $player2State, 1, 0, null, [
            'test_card' => new CardState('test_card', WizardMonkeyCard::class, '1', []),
        ]);

        $ctx = new GameContext($state, '1');

        $card->onMonsterPlayed($ctx);
        $events = $ctx->flushEvents();

        self::assertCount(3, $events);

        $targetIds = array_map(static fn($event) => $event->data['targetId'], $events);
        self::assertSame(['m1', 'm2', 'char2'], $targetIds);

        foreach ($events as $event) {
            self::assertSame(8, $event->data['damage']);
        }
    }
}
