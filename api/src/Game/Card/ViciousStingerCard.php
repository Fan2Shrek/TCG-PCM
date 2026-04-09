<?php

namespace App\Game\Card;

use App\Game\GameContext;

final class ViciousStingerCard extends AbstractPlayableCard
{
    public function getId(): string
    {
        return 'ViciousStinger';
    }

    public function play(GameContext $context, array $data = []): void
    {
        // @todo
        // Appliquer DMG buff ou tsais pas quoi
    }
}
