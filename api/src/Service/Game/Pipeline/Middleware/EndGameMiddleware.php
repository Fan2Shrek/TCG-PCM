<?php

declare(strict_types=1);

namespace App\Service\Game\Pipeline\Middleware;

use App\Service\Game\EndGameHandlerInterface;
use App\Service\Game\Pipeline\GamePipelineContext;
use App\Service\Game\Pipeline\GamePipelineStackInterface;

final class EndGameMiddleware implements GameMiddlewareInterface
{
    public function __construct(
        private EndGameHandlerInterface $endGameHandler,
    ) {}

    public function handle(GamePipelineContext $gamePipelineContext, GamePipelineStackInterface $stack): GamePipelineContext
    {
        $player1 = $gamePipelineContext->getResolutionResult()->state->player1;
        $player2 = $gamePipelineContext->getResolutionResult()->state->player2;

        if ($player1->healthPoints <= 0 || $player2->healthPoints <= 0) {
            if (!($gameId = $gamePipelineContext->getAction()->gameId)) {
                throw new \LogicException('Game ID is required to end the game');
            }

            $this->endGameHandler->endGame(
                $gameId,
                $player1->healthPoints <= 0 ? $player2->player->id : $player1->player->id,
            );
        }

        return $stack->next()->handle($gamePipelineContext, $stack);
    }
}
