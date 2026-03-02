<?php

declare(strict_types=1);

namespace App\Game\Card;

use App\Enum\CardSerieEnum;
use App\Game\GameContext;

final class D6Card extends AbstractPlayableCard
{
    public static CardSerieEnum $serie = CardSerieEnum::TBOI;

    public function getId(): string
    {
        return 'D6';
    }

    public function getImage(): string
    {
        return 'https://www.shutterstock.com/image-photo/red-die-on-white-six-260nw-27724336.jpg';
    }

    public function getName(): string
    {
        return 'D6';
    }

    public function getDescription(): string
    {
        return 'Roll a six-sided dice and draw that many cards.';
    }

    public function play(GameContext $context, array $data = []): void
    {
        $context->drawCards($context->rollDice(6));
    }
}
