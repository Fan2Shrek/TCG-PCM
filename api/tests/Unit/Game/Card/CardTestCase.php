<?php

declare(strict_types=1);

namespace App\Tests\Unit\Game\Card;

use App\Game\AbstractCard;
use App\Game\Card\CardState;
use App\Game\Dice;
use App\Game\GameContext;
use App\Game\Player;
use App\Game\State\GameState;
use App\Game\State\PlayerState;
use App\Tests\Unit\Fixtures\DummyCard;
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

    public function getCard(): AbstractCard
    {
        $card = new ($this->getCardFQCN())();

        $card->setState(new CardState(
            'test_card',
            $card->getId(),
            '1',
            [],
        ));

        return $card;
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
            30,
            [],
            [],
        );
        $player2State = new PlayerState(
            new Player('2', 'Player 2', 69),
            30,
            30,
            [],
            [],
        );

        $state = new GameState(
            $player1State,
            $player2State,
            1,
            null,
            [
                'test_card' => new CardState(
                    'test_card',
                    DummyCard::class,
                    '1',
                    [],
                ),
            ]
        );

        return new GameContext($state, '1');
    }
}
