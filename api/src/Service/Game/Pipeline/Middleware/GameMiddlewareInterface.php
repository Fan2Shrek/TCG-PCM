<?php

declare(strict_types=1);

namespace App\Service\Game\Pipeline\Middleware;

use App\Service\Game\Pipeline\GamePipelineContext;
use App\Service\Game\Pipeline\GamePipelineStackInterface;

interface GameMiddlewareInterface
{
    public function handle(GamePipelineContext $gamePipelineContext, GamePipelineStackInterface $stack): GamePipelineContext;
}
