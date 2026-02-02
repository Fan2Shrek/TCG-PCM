<?php

declare(strict_types=1);

namespace App\Service\Game;

use App\Entity\Room;
use App\Game\GameContext;

interface GameContextRepositoryInterface
{
    public function save(GameContext $gameContext, Room $room): void;

    public function get(Room $room): GameContext;
}
