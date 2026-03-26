<?php

declare(strict_types=1);

namespace App\Service;

use App\Service\Game\Pipeline\GamePipelineContext;
use App\Service\Game\Pipeline\GamePipelineStackInterface;
use App\Service\Game\Pipeline\Middleware\GameMiddlewareInterface;
use App\Service\GameEventPublisher;

final class PublishEventMiddleware implements GameMiddlewareInterface
{
    public function __construct(
        private GameEventPublisher $gameEventPublisher,
    ) {}

    public function handle(GamePipelineContext $gamePipelineContext, GamePipelineStackInterface $stack): GamePipelineContext
    {
        $events = $gamePipelineContext->getResolutionResult()->events;
        $state = $gamePipelineContext->getGameState();
        $gameId = $gamePipelineContext->getAction()->gameId;

        $this->gameEventPublisher->publish($events, $state, $gameId);

        return $stack->next()->handle($gamePipelineContext, $stack);
    }
}
