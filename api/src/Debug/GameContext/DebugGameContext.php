<?php

namespace App\Debug\GameContext;

use App\Game\GameContext;

class DebugGameContext extends GameContext
{
    public array $flushedEvents = [];

    public function flushEvents(): array
    {
        $events = parent::flushEvents();

        $this->flushedEvents = array_merge($this->flushedEvents, $events);

        return $events;
    }
}
