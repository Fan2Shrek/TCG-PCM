<?php

declare(strict_types=1);

namespace App\Debug\Card;

use App\Game\Card\AbstractPlayableCard;
use App\Game\Card\Interface\ComputedCardInterface;
use App\Game\GameContext;
use Symfony\Component\Stopwatch\Stopwatch;

final class TraceablePlayableCard extends AbstractPlayableCard implements ComputedCardInterface
{
    /** @use TraceableCardTrait<AbstractPlayableCard> */
    use TraceableCardTrait;

    public static function create(AbstractPlayableCard $card, Stopwatch $stopwatch): static
    {
        $traceableCard = new static();
        $traceableCard->card = $card;
        $traceableCard->stopwatch = $stopwatch;

        return $traceableCard;
    }

    public function play(GameContext $context, array $data = []): void
    {
        $this->stopwatch->start($this->getEventName('play'), self::STOPWATCH_CATEGORY);

        $this->methodCalled[] = __METHOD__;
        $this->card->play($context, $data);

        $this->stopwatch->stop($this->getEventName('play'));
    }
}
