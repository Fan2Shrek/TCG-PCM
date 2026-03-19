<?php

declare(strict_types=1);

namespace App\Tests\Unit\Game\Card;

use App\Game\AbstractCard;
use App\Game\Card\CardState;
use App\Game\GameContext;
use App\Game\Player;
use App\Game\State\GameState;
use App\Game\State\PlayArea;
use App\Game\State\PlayerState;
use App\Tests\Unit\Fixtures\DummyCard;
use PHPUnit\Framework\TestCase;

abstract class CardTestCase extends TestCase
{
    private float $nextRoll;

    abstract protected function getCardFQCN(): string;

    public function getCard(): AbstractCard
    {
        $card = new ($this->getCardFQCN())();

        $card->setState(new CardState('test_card', $card->getId(), '1', []));

        return $card;
    }

    protected function ensureNextDiceRolls(int $result): void
    {
        $this->nextRoll = $result;
    }

    protected static function allRollFromGenerator(int $count): \Generator
    {
        for ($i = 1; $i <= $count; $i++) {
            yield 'Test roll: '.$i => [$i];
        }
    }

    protected function createGameContext(): GameContext
    {
        $player1State = $this->createPlayerState('1');
        $player2State = $this->createPlayerState('2');

        $state = new GameState($player1State, $player2State, 1, 0, null, [
            'test_card' => new CardState('test_card', DummyCard::class, '1', []),
        ]);

        return new TestableGameContext($state, '1', $this->nextRoll ?? 0);
    }

    protected function createPlayerState(string $id): PlayerState
    {
        return new PlayerState(new Player($id, 'Player 1', 67), 30, 30, '', [], [], 0, new PlayArea());
    }
}

class TestableGameContext extends GameContext
{
    public function __construct(
        GameState $state,
        string $playerId,
        public float $nextRoll,
    ) {
        parent::__construct($state, $playerId);
    }

    public function rollDice(int $sides): int
    {
        return (int) $this->nextRoll;
    }

    public function randomBetween(float $min, float $max): float
    {
        return $this->nextRoll;
    }
}
