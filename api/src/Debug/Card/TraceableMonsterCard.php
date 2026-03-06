<?php

declare(strict_types=1);

namespace App\Debug\Card;

use App\Game\Card\Monster\AbstractMonsterCard;
use App\Game\GameContext;
use Symfony\Component\Stopwatch\Stopwatch;

final class TraceableMonsterCard extends AbstractMonsterCard
{
    /** @use TraceableCardTrait<AbstractMonsterCard> */
    use TraceableCardTrait;

    public function getAttack(): int
    {
        return $this->card->getAttack();
    }

    public function getHealPoints(): int
    {
        return $this->card->getHealPoints();
    }

    public function onMonsterPlayed(GameContext $context): void
    {
        $this->card->onMonsterPlayed($context);
    }

    public static function create(AbstractMonsterCard $card, Stopwatch $stopwatch): self
    {
        $traceableCard = new static();
        $traceableCard->card = $card;
        $traceableCard->stopwatch = $stopwatch;

        return $traceableCard;
    }
}
