<?php

declare(strict_types=1);

namespace App\Debug;

use App\Service\Game\Pipeline\GamePipelineContext;
use App\Service\Game\Pipeline\GamePipelineStackInterface;
use App\Service\Game\Pipeline\Middleware\GameMiddlewareInterface;
use Symfony\Component\Stopwatch\Stopwatch;

final class TraceableGameMiddleware implements GameMiddlewareInterface
{
    private const EVENT_CATEGORY = 'game.middleware';

    public function __construct(
        private Stopwatch $stopwatch,
    ) {}

    public function handle(GamePipelineContext $gamePipelineContext, GamePipelineStackInterface $stack): GamePipelineContext
    {
        $debugStack = new TraceableGamePipelineStack($stack, $this->stopwatch, self::EVENT_CATEGORY);

        try {
            return $debugStack->next()->handle($gamePipelineContext, $debugStack);
        } finally {
            $debugStack->stop();
        }
    }
}

class TraceableGamePipelineStack implements GamePipelineStackInterface
{
    private ?string $currentEvent = null;

    public function __construct(
        private GamePipelineStackInterface $decorated,
        private Stopwatch $stopwatch,
        private string $category,
    ) {}

    public function next(): GameMiddlewareInterface
    {
        if ($this->currentEvent) {
            $this->stop();
        }

        $next = $this->decorated->next();

        $this->currentEvent = $next::class;

        $this->stopwatch->start($this->currentEvent, $this->category);

        return $next;
    }

    public function stop(): void
    {
        if ($this->currentEvent) {
            $this->stopwatch->stop($this->currentEvent);
            $this->currentEvent = null;
        }
    }
}
