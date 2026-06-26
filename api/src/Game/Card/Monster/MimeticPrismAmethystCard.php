<?php

namespace App\Game\Card\Monster;

final class MimeticPrismAmethystCard extends MimeticPrismRubyCard
{
    protected const ATTACK_MULTIPLIER = 4;

    public function getId(): string
    {
        return 'mimestic_prism_amethyst.webp';
    }

    public function getHealPoints(): int
    {
        return 1;
    }
}
