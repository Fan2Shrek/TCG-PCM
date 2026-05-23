<?php

namespace App\Game\Card;

use App\Game\GameContext;
use Override;

final class BombCard extends AbstractPlayableCard
{
    private const DAMAGE = 1;

    public function getId(): string
    {
        return 'Bomb';
    }

    #[Override]
    public function getImage(): string
    {
        return 'bomb.png';
    }

    public function play(GameContext $context, array $data = []): void
    {
        foreach (CardHelper::getAllMonster($context) as $monster) {
            $context->damageCard($monster, $this->getValue(self::DAMAGE, true));
        }
    }
}
