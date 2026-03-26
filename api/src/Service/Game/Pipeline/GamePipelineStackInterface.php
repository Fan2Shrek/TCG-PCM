<?php

declare(strict_types=1);

namespace App\Service\Game\Pipeline;

use App\Service\Game\Pipeline\Middleware\GameMiddlewareInterface;

interface GamePipelineStackInterface
{
    public function next(): GameMiddlewareInterface;
}
