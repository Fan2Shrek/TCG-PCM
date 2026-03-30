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
        $this->methodCalled[] = __METHOD__;

        return $this->card->getAttack();
    }

    public function getHealPoints(): int
    {
        $this->methodCalled[] = __METHOD__;

        return $this->card->getHealPoints();
    }

    public function onMonsterPlayed(GameContext $context): void
    {
        $this->methodCalled[] = __METHOD__;
        $this->card->onMonsterPlayed($context);
    }

    public function onMonsterDeath(GameContext $context): void
    {
        $this->methodCalled[] = __METHOD__;
        $this->card->onMonsterPlayed($context);
    }

    public function canAttack(): bool
    {
        $this->methodCalled[] = __METHOD__;

        return $this->card->canAttack();
    }

    public function getCurrentHealthPoints(): int
    {
        return $this->card->getCurrentHealthPoints();
    }

    public static function create(parent $card, Stopwatch $stopwatch): self
    {
        $traceableCard = new static();
        $traceableCard->card = $card;
        $traceableCard->stopwatch = $stopwatch;

        return $traceableCard;
    }
}
