<?php

declare(strict_types=1);

namespace App\Service\Game\Pipeline\Middleware;

use App\Service\Game\Pipeline\GamePipelineContext;
use App\Service\Game\Pipeline\GamePipelineStackInterface;
use App\Service\Game\State\GameStateRepositoryInterface;

/**
 * Maybe this middleware should handle the up to date state logic
 */
final class ProvideGameStateMiddleware implements GameMiddlewareInterface
{
    public function __construct(
        private GameStateRepositoryInterface $gameStateRepository,
    ) {}

    public function handle(GamePipelineContext $gamePipelineContext, GamePipelineStackInterface $stack): GamePipelineContext
    {
        $action = $gamePipelineContext->getAction();
        $gameState = $this->gameStateRepository->get($action->gameId);

        $gamePipelineContext->setGameState($gameState);

        return $stack->next()->handle($gamePipelineContext, $stack);
    }
}
