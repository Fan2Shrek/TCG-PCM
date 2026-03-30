<?php

declare(strict_types=1);

namespace App\Service\Game\Pipeline\Middleware;

use App\Service\Game\Pipeline\GamePipelineContext;
use App\Service\Game\Pipeline\GamePipelineStackInterface;
use App\Service\Game\State\GameStateProvider;

final class ProvideGameStateMiddleware implements GameMiddlewareInterface
{
    public function __construct(
        private GameStateProvider $GameStateProvider,
    ) {}

    public function handle(GamePipelineContext $gamePipelineContext, GamePipelineStackInterface $stack): GamePipelineContext
    {
        $action = $gamePipelineContext->getAction();
        $gameState = $this->GameStateProvider->get($action->gameId);

        if (!$gameState) {
            throw new \LogicException('Game state not found for game ID: '.$action->gameId);
        }

        $gamePipelineContext->setGameState($gameState);

        return $stack->next()->handle($gamePipelineContext, $stack);
    }
}
