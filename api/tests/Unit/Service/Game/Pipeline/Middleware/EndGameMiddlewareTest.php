<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Game\Pipeline\Middleware;

use App\Game\Player;
use App\Game\PlayerAction;
use App\Game\State\GameState;
use App\Game\State\PlayArea;
use App\Game\State\PlayerState;
use App\Service\Game\EndGameHandlerInterface;
use App\Service\Game\Pipeline\GamePipelineContext;
use App\Service\Game\Pipeline\GamePipelineMiddlewareStack;
use App\Service\Game\Pipeline\Middleware\EndGameMiddleware;
use App\Service\Game\ResolutionResult;
use PHPUnit\Framework\TestCase;

final class EndGameMiddlewareTest extends TestCase
{
    public function testHandleDoesNotEndGameWhenNoPlayerIsDead(): void
    {
        $endGameHandler = $this->createMock(EndGameHandlerInterface::class);
        $endGameHandler->expects(self::never())->method('endGame');

        $sut = new EndGameMiddleware($endGameHandler);
        $context = $this->createContext(10, 5);

        $result = $sut->handle($context, new GamePipelineMiddlewareStack([]));

        self::assertSame($context, $result);
    }

    public function testHandleEndsGameWhenPlayer1IsDead(): void
    {
        $context = $this->createContext(0, 5);
        $gameState = $context->getGameState();

        $endGameHandler = $this->createMock(EndGameHandlerInterface::class);
        $endGameHandler->expects(self::once())->method('endGame')->with('game-id', $gameState, '2');

        $sut = new EndGameMiddleware($endGameHandler);

        $result = $sut->handle($context, new GamePipelineMiddlewareStack([]));

        self::assertSame($context, $result);
    }

    public function testHandleEndsGameWhenPlayer2IsDead(): void
    {
        $context = $this->createContext(3, 0);
        $gameState = $context->getGameState();

        $endGameHandler = $this->createMock(EndGameHandlerInterface::class);
        $endGameHandler->expects(self::once())->method('endGame')->with('game-id', $gameState, '1');

        $sut = new EndGameMiddleware($endGameHandler);

        $result = $sut->handle($context, new GamePipelineMiddlewareStack([]));

        self::assertSame($context, $result);
    }

    public function testHandleThrowsWhenGameIdIsMissingForFinishedGame(): void
    {
        self::expectException(\LogicException::class);
        self::expectExceptionMessage('Game ID is required to end the game');

        $endGameHandler = $this->createMock(EndGameHandlerInterface::class);
        $endGameHandler->expects(self::never())->method('endGame');

        $sut = new EndGameMiddleware($endGameHandler);
        $context = $this->createContext(0, 5, '');

        $sut->handle($context, new GamePipelineMiddlewareStack([]));
    }

    private function createContext(int $player1HealthPoints, int $player2HealthPoints, string $gameId = 'game-id'): GamePipelineContext
    {
        $player1 = new PlayerState(new Player('1', 'player-1'), $player1HealthPoints, 10, '', [], [], 0, new PlayArea());
        $player2 = new PlayerState(new Player('2', 'player-2'), $player2HealthPoints, 10, '', [], [], 0, new PlayArea());
        $gameState = new GameState($player1, $player2, null, 0, '1');

        $context = new GamePipelineContext(new PlayerAction('1', PlayerAction::END_TURN, $gameId, []));
        $context->setGameState($gameState);
        $context->setResolutionResult(new ResolutionResult([], $gameState));

        return $context;
    }
}
