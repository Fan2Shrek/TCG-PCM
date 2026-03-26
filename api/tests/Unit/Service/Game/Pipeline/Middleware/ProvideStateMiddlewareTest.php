<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Game\Pipeline\Middleware;

use App\Game\PlayerAction;
use App\Game\State\GameState;
use App\Game\State\PlayerState;
use App\Service\Game\Pipeline\GamePipelineContext;
use App\Service\Game\Pipeline\GamePipelineMiddlewareStack;
use App\Service\Game\Pipeline\Middleware\ProvideGameStateMiddleware;
use App\Service\Game\State\GameStateRepositoryInterface;
use PHPUnit\Framework\TestCase;

final class ProvideStateMiddlewareTest extends TestCase
{
    public function testProvideState()
    {
        $repo = $this->createMock(GameStateRepositoryInterface::class);
        $repo
            ->expects(self::once())
            ->method('get')
            ->with('gameId')
            ->willReturn(new GameState($this->createStub(PlayerState::class), $this->createStub(PlayerState::class), null, 0, ''));
        $sut = new ProvideGameStateMiddleware($repo);

        $gamePipelineContext = new GamePipelineContext(new PlayerAction('', '', 'gameId', []));

        $sut->handle($gamePipelineContext, new GamePipelineMiddlewareStack([]));
    }
}
