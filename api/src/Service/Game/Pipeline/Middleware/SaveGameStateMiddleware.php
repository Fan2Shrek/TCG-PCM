<?php

declare(strict_types=1);

namespace App\Service\Game\Pipeline\Middleware;

use App\Service\Game\Pipeline\GamePipelineContext;
use App\Service\Game\Pipeline\GamePipelineStackInterface;
use App\Service\Game\State\GameStateRepositoryInterface;

final class SaveGameStateMiddleware implements GameMiddlewareInterface
{
    public function __construct(
        private GameStateRepositoryInterface $gameStateRepository,
    ) {}

    public function handle(GamePipelineContext $gamePipelineContext, GamePipelineStackInterface $stack): GamePipelineContext
    {
        $ctx = $gamePipelineContext->getResolutionResult()->state;
        $gameId = $gamePipelineContext->getAction()->gameId;

        $this->gameStateRepository->save($ctx, $gameId);

        return $stack->next()->handle($gamePipelineContext, $stack);
    }
}
