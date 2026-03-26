<?php

declare(strict_types=1);

namespace App\Service\Game\Pipeline\Middleware;

use App\Service\Game\GameEventResolver;
use App\Service\Game\Pipeline\GamePipelineContext;
use App\Service\Game\Pipeline\GamePipelineStackInterface;

final class ResolveEventMiddleware implements GameMiddlewareInterface
{
    public function __construct(
        private GameEventResolver $gameEventResolver,
    ) {}

    public function handle(GamePipelineContext $gamePipelineContext, GamePipelineStackInterface $stack): GamePipelineContext
    {
        $state = $gamePipelineContext->getGameState();
        $mainEvent = $gamePipelineContext->getMainEvent();

        $resolution = $this->gameEventResolver->resolve($mainEvent, $state);

        $gamePipelineContext->setResolutionResult($resolution);

        return $stack->next()->handle($gamePipelineContext, $stack);
    }
}
