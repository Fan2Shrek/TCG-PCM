<?php

namespace App\Game\Card;

use App\Game\GameContext;

final class GlitchCard extends AbstractPlayableCard
{
    public function getId(): string
    {
        return 'Glitch';
    }

    public function play(GameContext $context, array $data = []): void
    {
        $context->pushGameEvent();
    }
}
