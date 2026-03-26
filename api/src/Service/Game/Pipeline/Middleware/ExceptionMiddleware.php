<?php

declare(strict_types=1);

namespace App\Service\Game\Pipeline\Middleware;

use App\Game\Exception\GameException;
use App\Service\Game\Pipeline\GamePipelineContext;
use App\Service\Game\Pipeline\GamePipelineStackInterface;
use Psr\Log\LoggerAwareTrait;

final class ExceptionMiddleware implements GameMiddlewareInterface
{
    use LoggerAwareTrait;

    public function handle(GamePipelineContext $gamePipelineContext, GamePipelineStackInterface $stack): GamePipelineContext
    {
        try {
            return $stack->next()->handle($gamePipelineContext, $stack);
        } catch (GameException $e) {
            $action = $gamePipelineContext->getAction();

            $this->logger?->error('Game action failed', [
                'userId' => $action->authorId,
                'actionId' => $action->actionId,
                'payload' => $action->payload,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        } catch (\Throwable $e) {
            // @todo retry without redis cache

            throw $e;
        }
    }
}
