<?php

declare(strict_types=1);

namespace App\Service\Game\Pipeline\Middleware;

use App\Game\Exception\CardNotInHandException;
use App\Game\Exception\GameAlreadyFinishedException;
use App\Game\Exception\NotYourTurnException;
use App\Game\Exception\UnknowActionException;
use App\Game\PlayerAction;
use App\Service\Game\Pipeline\GamePipelineContext;
use App\Service\Game\Pipeline\GamePipelineStackInterface;

final class ValidateActionMiddleware implements GameMiddlewareInterface
{
    public function handle(GamePipelineContext $gamePipelineContext, GamePipelineStackInterface $stack): GamePipelineContext
    {
        $action = $gamePipelineContext->getAction();

        if (!\in_array($action->actionId, PlayerAction::ACTIONS, true)) {
            throw new UnknowActionException();
        }

        $state = $gamePipelineContext->getGameState();

        if ($state->getCurrentPlayer()->id !== $action->authorId) {
            throw new NotYourTurnException();
        }

        if ($state->isFinished()) {
            throw new GameAlreadyFinishedException();
        }

        if (\in_array($action->actionId, [PlayerAction::PLAY_CARD, PlayerAction::ATTACK], true)) {
            $cardId = $action->payload['cardId'] ?? null;

            if (!$state->getCurrentPlayerState()->hasCardInHand((string) $cardId)) {
                throw new CardNotInHandException($state->getCurrentPlayerState()->player, (string) $cardId);
            }

            if (!$cardId || !\is_string($cardId) || !$state->getCardState($cardId)) {
                throw new \LogicException('Invalid card ID');
            }
        }

        return $stack->next()->handle($gamePipelineContext, $stack);
    }
}
