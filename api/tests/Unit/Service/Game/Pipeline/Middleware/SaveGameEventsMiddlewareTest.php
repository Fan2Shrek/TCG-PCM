<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Game\Pipeline\Middleware;

use App\Game\PlayerAction;
use App\Repository\Game\GameEventRepository;
use App\Service\Game\Pipeline\GamePipelineContext;
use App\Service\Game\Pipeline\GamePipelineMiddlewareStack;
use App\Service\Game\Pipeline\Middleware\SaveGameEventsMiddleware;
use PHPUnit\Framework\TestCase;

final class SaveGameEventsMiddlewareTest extends TestCase
{
    public function testHandle()
    {
        $repo = $this->createMock(GameEventRepository::class);
        $repo->expects(self::exactly(2))->method('save');
        $middleware = new SaveGameEventsMiddleware($repo);
        $gpc = new GamePipelineContext(new PlayerAction('', '', 'gameId', []));

        $middleware->handle($gpc, new GamePipelineMiddlewareStack([]));
    }
}
