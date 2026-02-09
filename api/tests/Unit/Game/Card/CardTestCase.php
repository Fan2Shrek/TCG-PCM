<?php

declare(strict_types=1);

namespace App\Tests\Unit\Game\Card;

use App\Game\Card\AbstractPlayableCard;
use App\Game\Dice;
use App\Game\GameContext;
use App\Game\Player;
use App\Game\State\GameState;
use App\Game\State\PlayerState;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\TestCase;

abstract class CardTestCase extends TestCase
{
    abstract protected function getCardFQCN(): string;

    #[Before]
    public function beforeAll(): void
    {
        Dice::setGenerator(null);
    }

    public function getCard(): AbstractPlayableCard
    {
        return new ($this->getCardFQCN())();
    }

    protected function ensureNextDiceRolls(int $result): void
    {
        Dice::setGenerator(fn ($sides) => $result);
    }

    protected static function allRollFromGenerator(int $count): \Generator
    {
        for ($i = 1; $i <= $count; $i++) {
            yield 'Test roll: '.$i => [$i];
        }
    }

    protected function createGameContext(): GameContext
    {
        $player1State = new PlayerState(
            new Player('1', 'Player 1', 67),
            30,
            [],
            [],
        );
        $player2State = new PlayerState(
            new Player('2', 'Player 2', 69),
            30,
            [],
            [],
        );

        $state = new GameState(
            $player1State,
            $player2State,
            1,
        );

        return new GameContext($state, '1');
    }
}
