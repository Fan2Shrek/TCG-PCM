<?php

declare(strict_types=1);

namespace App\Service;

use App\Service\Game\Pipeline\GamePipelineContext;
use App\Service\Game\Pipeline\GamePipelineStackInterface;
use App\Service\Game\Pipeline\Middleware\GameMiddlewareInterface;

final class PublishEventMiddleware implements GameMiddlewareInterface
{
    public function __construct(
        private GameEventPublisher $gameEventPublisher,
    ) {}

    public function handle(GamePipelineContext $gamePipelineContext, GamePipelineStackInterface $stack): GamePipelineContext
    {
        $resolution = $gamePipelineContext->getResolutionResult();
        $gameId = $gamePipelineContext->getAction()->gameId;

        $this->gameEventPublisher->publish($resolution->events, $resolution->state, $gameId);

        return $stack->next()->handle($gamePipelineContext, $stack);
    }
}
