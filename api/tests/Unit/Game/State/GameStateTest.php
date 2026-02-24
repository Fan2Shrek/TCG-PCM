<?php

declare(strict_types=1);

namespace App\Tests\Unit\Game\State;

use App\Game\Player;
use App\Game\State\GameState;
use App\Game\State\PlayerState;
use PHPUnit\Framework\TestCase;

final class GameStateTest extends TestCase
{
    public function testGetPlayer(): void
    {
        $player1 = new PlayerState(
            new Player('1', 'Player 1'),
            0,
            [],
            [],
        );
        $player2 = new PlayerState(
            new Player('2', 'Player 1'),
            0,
            [],
            [],
        );
        $gameState = new GameState(
            $player1,
            $player2,
            0,
        );

        self::assertSame($player1, $gameState->getPlayer('1'));
        self::assertSame($player2, $gameState->getPlayer('2'));
    }

    public function testGetPlayers(): void
    {
        $player1 = new PlayerState(
            new Player('1', 'Player 1'),
            0,
            [],
            [],
        );
        $player2 = new PlayerState(
            new Player('2', 'Player 1'),
            0,
            [],
            [],
        );
        $gameState = new GameState(
            $player1,
            $player2,
            0,
        );

        self::assertSame([$player1->player, $player2->player], $gameState->getPlayers());
    }

    public function testGetCurrentPlayerState()
    {
        $player1 = new PlayerState(
            new Player('1', 'Player 1'),
            0,
            [],
            [],
        );
        $player2 = new PlayerState(
            new Player('2', 'Player 1'),
            0,
            [],
            [],
        );
        $gameState = new GameState(
            $player1,
            $player2,
            0,
        );

        self::assertSame($player1, $gameState->getCurrentPlayerState());
    }

    public function testGetCurrentPlayerStateWithOtherPlayerTurn()
    {
        $player1 = new PlayerState(
            new Player('1', 'Player 1'),
            0,
            [],
            [],
        );
        $player2 = new PlayerState(
            new Player('2', 'Player 1'),
            0,
            [],
            [],
        );
        $gameState = new GameState(
            $player1,
            $player2,
            0,
            '2',
        );

        self::assertSame($player2, $gameState->getCurrentPlayerState());
    }

    public function testGetCurrentPlayer()
    {
        $player1 = new PlayerState(
            new Player('1', 'Player 1'),
            0,
            [],
            [],
        );
        $player2 = new PlayerState(
            new Player('2', 'Player 1'),
            0,
            [],
            [],
        );
        $gameState = new GameState(
            $player1,
            $player2,
            0,
        );

        self::assertSame($player1->player, $gameState->getCurrentPlayer());
    }

    public function testGetCurrentPlayerWithOtherPlayerTurn()
    {
        $player1 = new PlayerState(
            new Player('1', 'Player 1'),
            0,
            [],
            [],
        );
        $player2 = new PlayerState(
            new Player('2', 'Player 1'),
            0,
            [],
            [],
        );
        $gameState = new GameState(
            $player1,
            $player2,
            0,
            '2',
        );

        self::assertSame($player2->player, $gameState->getCurrentPlayer());
    }

    public function testGetNextPlayer()
    {
        $player1 = new PlayerState(
            new Player('1', 'Player 1'),
            0,
            [],
            [],
        );
        $player2 = new PlayerState(
            new Player('2', 'Player 1'),
            0,
            [],
            [],
        );
        $gameState = new GameState(
            $player1,
            $player2,
            0,
        );

        self::assertSame($player2->player, $gameState->getNextPlayer());
    }

    public function testGetNextPlayerWithOtherPlayerTurn()
    {
        $player1 = new PlayerState(
            new Player('1', 'Player 1'),
            0,
            [],
            [],
        );
        $player2 = new PlayerState(
            new Player('2', 'Player 1'),
            0,
            [],
            [],
        );
        $gameState = new GameState(
            $player1,
            $player2,
            0,
            '2',
        );

        self::assertSame($player1->player, $gameState->getNextPlayer());
    }

    public function testIsCurrentPlayer()
    {
        $player1 = new PlayerState(
            new Player('1', 'Player 1'),
            0,
            [],
            [],
        );
        $player2 = new PlayerState(
            new Player('2', 'Player 1'),
            0,
            [],
            [],
        );
        $gameState = new GameState(
            $player1,
            $player2,
            0,
        );

        self::assertTrue($gameState->isCurrentPlayer($player1->player));
        self::assertFalse($gameState->isCurrentPlayer($player2->player));
    }

    public function testIsFinishedReturnFalse()
    {
        $player1 = new PlayerState(
            new Player('1', 'Player 1'),
            1,
            [],
            [],
        );
        $player2 = new PlayerState(
            new Player('2', 'Player 1'),
            1,
            [],
            [],
        );
        $gameState = new GameState(
            $player1,
            $player2,
            0,
        );

        self::assertFalse($gameState->isFinished());
    }

    public function testIsFinishedReturnTrueWithPlayerOneDead()
    {
        $player1 = new PlayerState(
            new Player('1', 'Player 1'),
            0,
            [],
            [],
        );
        $player2 = new PlayerState(
            new Player('2', 'Player 1'),
            1,
            [],
            [],
        );
        $gameState = new GameState(
            $player1,
            $player2,
            0,
        );

        self::assertTrue($gameState->isFinished());
    }

    public function testIsFinishedReturnTrueWithPlayerTwoDead()
    {
        $player1 = new PlayerState(
            new Player('1', 'Player 1'),
            1,
            [],
            [],
        );
        $player2 = new PlayerState(
            new Player('2', 'Player 1'),
            0,
            [],
            [],
        );
        $gameState = new GameState(
            $player1,
            $player2,
            0,
        );

        self::assertTrue($gameState->isFinished());
    }

    public function testWithUpdatedPlayerUpdatePlayer1()
    {
        $player1 = new PlayerState(
            new Player('1', 'Player 1'),
            1,
            [],
            [],
        );
        $player2 = new PlayerState(
            new Player('2', 'Player 1'),
            1,
            [],
            [],
        );
        $gameState = new GameState(
            $player1,
            $player2,
            0,
        );

        $newGameState = $gameState->withUpdatedPlayer(
            new PlayerState(
                new Player('1', 'Player 1'),
                0,
                [],
                [],
            ),
        );

        self::assertSame(0, $newGameState->player1->healthPoints);
        self::assertSame(1, $gameState->player1->healthPoints);
    }

    public function testWithUpdatedPlayerUpdatePlayer2()
    {
        $player1 = new PlayerState(
            new Player('1', 'Player 1'),
            1,
            [],
            [],
        );
        $player2 = new PlayerState(
            new Player('2', 'Player 1'),
            1,
            [],
            [],
        );
        $gameState = new GameState(
            $player1,
            $player2,
            0,
        );

        $newGameState = $gameState->withUpdatedPlayer(
            new PlayerState(
                new Player('2', 'Player 2'),
                0,
                [],
                [],
            ),
        );

        self::assertSame(0, $newGameState->player2->healthPoints);
        self::assertSame(1, $gameState->player2->healthPoints);
    }

    public function testWithUpdatedPlayerUnknowPlayer()
    {
        self::expectException(\InvalidArgumentException::class);
        self::expectExceptionMessage('Player 3 not found in GameState');

        $player1 = new PlayerState(
            new Player('1', 'Player 1'),
            1,
            [],
            [],
        );
        $player2 = new PlayerState(
            new Player('2', 'Player 1'),
            1,
            [],
            [],
        );
        $gameState = new GameState(
            $player1,
            $player2,
            0,
        );

        $gameState->withUpdatedPlayer(
            new PlayerState(
                new Player('3', 'Player 3'),
                0,
                [],
                [],
            ),
        );
    }

    public function testWithCurrentPlayer()
    {
        $player1 = new PlayerState(
            new Player('1', 'Player 1'),
            1,
            [],
            [],
        );
        $player2 = new PlayerState(
            new Player('2', 'Player 1'),
            1,
            [],
            [],
        );
        $gameState = new GameState(
            $player1,
            $player2,
            0,
        );

        $newGameState = $gameState->withCurrentPlayer('2');

        self::assertSame($player2, $newGameState->getCurrentPlayerState());
        self::assertSame($player1, $gameState->getCurrentPlayerState());
    }

    public function testWithCurrentPlayerWithUnknowPlayer()
    {
        self::expectException(\InvalidArgumentException::class);
        self::expectExceptionMessage('Player 3 not found in GameState');

        $player1 = new PlayerState(
            new Player('1', 'Player 1'),
            1,
            [],
            [],
        );
        $player2 = new PlayerState(
            new Player('2', 'Player 1'),
            1,
            [],
            [],
        );
        $gameState = new GameState(
            $player1,
            $player2,
            0,
        );

        $gameState->withCurrentPlayer('3');
    }
}
