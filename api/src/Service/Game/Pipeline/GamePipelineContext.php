<?php

declare(strict_types=1);

namespace App\Service\Game\Pipeline;

use App\Game\PlayerAction;
use App\Game\State\GameEvent;
use App\Game\State\GameState;
use App\Service\Game\ResolutionResult;

final class GamePipelineContext
{
    private GameState $gameState;

    private ResolutionResult $resolutionResult;

    private GameEvent $mainEvent;

    public function __construct(
        private PlayerAction $action,
    ) {}

    public function getAction(): PlayerAction
    {
        return $this->action;
    }

    public function setGameState(mixed $gameState): void
    {
        $this->gameState = $gameState;
    }

    public function getGameState(): GameState
    {
        return $this->gameState;
    }

    public function setResolutionResult(ResolutionResult $resolutionResult): void
    {
        $this->resolutionResult = $resolutionResult;
    }

    public function getResolutionResult(): ResolutionResult
    {
        return $this->resolutionResult;
    }

    public function setMainEvent(GameEvent $mainEvent): void
    {
        $this->mainEvent = $mainEvent;
    }

    public function getMainEvent(): GameEvent
    {
        return $this->mainEvent;
    }
}
