<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Game\Pipeline\Middleware;

use App\Game\Player;
use App\Game\PlayerAction;
use App\Game\State\GameState;
use App\Game\State\PlayArea;
use App\Game\State\PlayerState;
use App\Service\Game\Pipeline\GamePipelineContext;
use App\Service\Game\Pipeline\GamePipelineMiddlewareStack;
use App\Service\Game\Pipeline\Middleware\SaveGameStateMiddleware;
use App\Service\Game\ResolutionResult;
use App\Service\Game\State\GameStateRepositoryInterface;
use PHPUnit\Framework\TestCase;

final class SaveGameStateMiddlewareTest extends TestCase
{
    public function testHandle()
    {
        $repo = $this->createMock(GameStateRepositoryInterface::class);
        $repo->expects($this->once())->method('save');
        $sut = new SaveGameStateMiddleware($repo);
        $gamePipelineContext = new GamePipelineContext(new PlayerAction('game-id', 'player-id', 'action-type', []));
        $state = new GameState(
            new PlayerState(new Player('1', 'otherUsername'), 1, 1, '', ['cardId'], [], 0, new PlayArea()),
            new PlayerState(new Player('2', 'otherUsername'), 1, 1, '', [], [], 0, new PlayArea()),
            null,
            0,
            '1',
            [],
        );
        $gamePipelineContext->setResolutionResult(new ResolutionResult([], $state));

        $sut->handle($gamePipelineContext, new GamePipelineMiddlewareStack([]));
    }
}
