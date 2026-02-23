<?php

declare(strict_types=1);

namespace App\Game\Card;

use App\Game\GameContext;

final class SpicyD6Card extends AbstractPlayableCard
{
    public function getId(): string
    {
        return 'Spicy-D6';
    }

    public function getImage(): string
    {
        return 'https://www.shutterstock.com/image-photo/red-die-on-white-six-260nw-27724336.jpg';
    }

    public function getName(): string
    {
        return 'Spicy D6';
    }

    public function getDescription(): string
    {
        return 'Roll a six-sided dice and does that many damage.';
    }

    public function play(GameContext $context): void
    {
        $context->attack($context->rollDice(6));
    }
}
