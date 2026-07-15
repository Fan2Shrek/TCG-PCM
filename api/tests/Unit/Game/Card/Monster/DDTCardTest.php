<?php

declare(strict_types=1);

namespace App\Tests\Unit\Game\Card\Monster;

use App\Game\Card\CardState;
use App\Game\Card\Monster\DDTCard;
use App\Game\GameContext;
use App\Game\State\GameState;
use App\Tests\Unit\Game\Card\CardTestCase;

final class DDTCardTest extends CardTestCase
{
    public function getCardFQCN(): string
    {
        return DDTCard::class;
    }

    public function testStats()
    {
        $card = $this->getCard();

        self::assertSame(20, $card->getHealPoints());
        self::assertSame(15, $card->getBaseAttack());
        self::assertSame(15, $card->getAttack());
    }

    public function testReduceDamageDodgesAndTakesFullDamage()
    {
        // seed 0 makes randomIntBetween(0, 1) return 0, the dodge branch.
        $card = $this->getCard();
        $ctx = $this->createContextWithSeed(0);

        self::assertSame(10, $card->reduceDamage($ctx, 10));
    }

    public function testReduceDamageAbsorbsWhenNotDodging()
    {
        // seed 1 makes randomIntBetween(0, 1) return 1, the reduction branch.
        $card = $this->getCard();
        $ctx = $this->createContextWithSeed(1);

        self::assertSame(5, $card->reduceDamage($ctx, 10));
    }

    private function createContextWithSeed(int $seed): GameContext
    {
        $player1State = $this->createPlayerState('1');
        $player2State = $this->createPlayerState('2');

        $state = new GameState($player1State, $player2State, 1, $seed, null, [
            'test_card' => new CardState('test_card', $this->getCardFQCN(), '1', []),
        ]);

        return new GameContext($state, '1');
    }
}
