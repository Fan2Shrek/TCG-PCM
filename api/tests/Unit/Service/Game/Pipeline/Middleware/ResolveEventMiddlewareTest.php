<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Game\Pipeline\Middleware;

use App\Enum\GameEventTypeEnum;
use App\Game\PlayerAction;
use App\Game\State\GameEvent;
use App\Game\State\GameState;
use App\Game\State\PlayerState;
use App\Service\Game\GameEventResolver;
use App\Service\Game\Pipeline\GamePipelineContext;
use App\Service\Game\Pipeline\GamePipelineMiddlewareStack;
use App\Service\Game\Pipeline\Middleware\ResolveEventMiddleware;
use App\Service\Game\ResolutionResult;
use PHPUnit\Framework\TestCase;

final class ResolveEventMiddlewareTest extends TestCase
{
    public function testHandle()
    {
        $gameState = new GameState($this->createStub(PlayerState::class), $this->createStub(PlayerState::class), null, 0, '');
        $ger = $this->createMock(GameEventResolver::class);
        $ger->expects(self::once())->method('resolve')->willReturn(new ResolutionResult([], $gameState));
        $middleware = new ResolveEventMiddleware($ger);
        $gpc = new GamePipelineContext(new PlayerAction('', '', '', []));
        $gpc->setGameState($gameState);
        $gpc->setMainEvent(GameEvent::player(GameEventTypeEnum::CARD_PLAYED, []));

        $middleware->handle($gpc, new GamePipelineMiddlewareStack([]));
    }
}
