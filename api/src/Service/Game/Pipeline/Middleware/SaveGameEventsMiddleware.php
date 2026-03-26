<?php

declare(strict_types=1);

namespace App\Service\Game\Pipeline\Middleware;

use App\Service\Game\Pipeline\GamePipelineContext;
use App\Service\Game\Pipeline\GamePipelineStackInterface;
use App\Service\Game\ResolutionResult;
use App\Service\Game\State\GameEventRepositoryInterface;

final class SaveGameEventsMiddleware implements GameMiddlewareInterface
{
    public function __construct(
        private GameEventRepositoryInterface $gameEventRepository,
    ) {}

    public function handle(GamePipelineContext $gamePipelineContext, GamePipelineStackInterface $stack): GamePipelineContext
    {
        $resolution = $gamePipelineContext->getResolutionResult();
        $gameId = $gamePipelineContext->getAction()->gameId;

        $lastId = null;
        foreach ($resolution->events as $event) {
            if (!$event->shouldBePersisted()) {
                continue;
            }

            $event = $this->gameEventRepository->save($event, $gameId);

            $lastId = $event->id ? $event->id : null;
        }

        $state = $resolution->state;

        if ($lastId) {
            $state = $state->withLastEventId($lastId);
        }

        $gamePipelineContext->setResolutionResult(new ResolutionResult($resolution->events, $state));

        return $stack->next()->handle($gamePipelineContext, $stack);
    }
}
