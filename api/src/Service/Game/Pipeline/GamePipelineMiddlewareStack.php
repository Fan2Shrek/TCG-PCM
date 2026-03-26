<?php

declare(strict_types=1);

namespace App\Service\Game\Pipeline;

use App\Service\Game\Pipeline\GamePipelineStackInterface;
use App\Service\Game\Pipeline\Middleware\GameMiddlewareInterface;

class GamePipelineMiddlewareStack implements GameMiddlewareInterface, GamePipelineStackInterface
{
    private bool $started = false;
    /**
     * @var \Generator<GameMiddlewareInterface, void, mixed, mixed> $generator
     */
    private \Generator $generator;

    public function __construct(iterable $middlewares)
    {
        $this->generator = (static function () use ($middlewares) {
            yield from $middlewares;
        })();
    }

    public function next(): GameMiddlewareInterface
    {
        if (!$this->started) {
            $this->started = true;
        } else {
            $this->generator->next();
        }

        if (!$this->generator->valid()) {
            return $this;
        }

        return $this->generator->current();
    }

    public function handle(GamePipelineContext $gamePipelineContext, GamePipelineStackInterface $stack): GamePipelineContext
    {
        return $gamePipelineContext;
    }
}
