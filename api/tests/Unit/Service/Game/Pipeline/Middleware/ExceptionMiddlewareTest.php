<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Game\Pipeline\Middleware;

use App\Game\Exception\GameException;
use App\Game\PlayerAction;
use App\Service\Game\Pipeline\GamePipelineContext;
use App\Service\Game\Pipeline\GamePipelineMiddlewareStack;
use App\Service\Game\Pipeline\GamePipelineStackInterface;
use App\Service\Game\Pipeline\Middleware\ExceptionMiddleware;
use App\Service\Game\Pipeline\Middleware\GameMiddlewareInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class ExceptionMiddlewareTest extends TestCase
{
    public function testLogOnException()
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('error');
        $sut = new ExceptionMiddleware();
        $sut->setLogger($logger);
        $gamePipelineContext = new GamePipelineContext(new PlayerAction('', '', '', []));

        try {
            $sut->handle($gamePipelineContext, new GamePipelineMiddlewareStack([new ThrowErrorMiddleware()]));
        } catch (\Exception) {
        }
    }

    public function testLog()
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('error');
        $sut = new ExceptionMiddleware();
        $sut->setLogger($logger);
        $gamePipelineContext = new GamePipelineContext(new PlayerAction('', '', '', []));

        $sut->handle($gamePipelineContext, new GamePipelineMiddlewareStack([]));
    }
}

class ThrowErrorMiddleware implements GameMiddlewareInterface
{
    public function handle(GamePipelineContext $gamePipelineContext, GamePipelineStackInterface $stack): GamePipelineContext
    {
        throw new GameException();
    }
}
