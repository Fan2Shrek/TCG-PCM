<?php

declare(strict_types=1);

namespace App\Service\Game\Pipeline;

use App\Game\PlayerAction;
use App\Service\Game\Pipeline\Middleware\GameMiddlewareInterface;

final class GamePipeline
{
    /**
     * @param iterable<GameMiddlewareInterface> $middlewares
     */
    public function __construct(
        private iterable $middlewares,
    ) {}

    public function start(PlayerAction $action): void
    {
        $stack = new GamePipelineMiddlewareStack($this->middlewares);
        $ctx = new GamePipelineContext($action);

        $stack->next()->handle($ctx, $stack);
    }
}
