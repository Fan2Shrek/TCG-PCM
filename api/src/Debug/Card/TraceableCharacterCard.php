<?php

declare(strict_types=1);

namespace App\Debug\Card;

use App\Game\Card\Character\AbstractCharacterCard;
use App\Game\Card\Interface\CardAwareInterface;
use App\Game\Card\Interface\TurnAwareInterface;
use Symfony\Component\Stopwatch\Stopwatch;

final class TraceableCharacterCard extends AbstractCharacterCard implements TurnAwareInterface, CardAwareInterface
{
    /** @use TraceableCardTrait<AbstractCharacterCard> */
    use TraceableCardTrait;

    public static function create(AbstractCharacterCard $card, Stopwatch $stopwatch): static
    {
        $traceableCard = new static();
        $traceableCard->card = $card;
        $traceableCard->stopwatch = $stopwatch;

        return $traceableCard;
    }

    public function getHealthPoints(): int
    {
        return $this->card->getHealthPoints();
    }
}
