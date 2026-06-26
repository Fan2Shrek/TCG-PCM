<?php

namespace App\Game\Card\Monster;

final class MimeticPrismSaphirCard extends MimeticPrismRubyCard
{
    protected const HEALTH_POINTS_MULTIPLIER = 2;
    protected const ATTACK_MULTIPLIER = 0.5;

    public function getId(): string
    {
        return 'mimestic_prism_saphir';
    }
}
