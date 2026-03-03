<?php

declare(strict_types=1);

namespace App\Debug\Card;

use App\Game\AbstractCard;
use App\Game\Card\AbstractPlayableCard;
use App\Game\Card\CardState;
use App\Game\Card\ComputedCardInterface;
use App\Game\Card\EffectCollection;
use App\Game\GameContext;
use Symfony\Component\Stopwatch\Stopwatch;

final class TraceablePlayableCard extends AbstractPlayableCard implements ComputedCardInterface
{
    public const STOPWATCH_CATEGORY = 'app.card';

    private AbstractPlayableCard $card;
    private Stopwatch $stopwatch;

    public function getId(): string
    {
        return $this->card->getId();
    }

    public function getInstanceId(): ?string
    {
        return $this->card->getInstanceId() ?? null;
    }

    public function getName(): string
    {
        return $this->card->getName();
    }

    public function getDescription(): string
    {
        return $this->card->getDescription();
    }

    public function getImage(): string
    {
        return $this->card->getImage();
    }

    public function onCardPlayed(AbstractCard $card, GameContext $context): void
    {
        $this->card->onCardPlayed($card, $context);
    }

    public function onCardDrawn(AbstractCard $card, GameContext $context): void
    {
        $this->card->onCardDrawn($card, $context);
    }

    public function play(GameContext $context, array $data = []): void
    {
        $id = $this->getId().'.play';
        $this->stopwatch->start($id, self::STOPWATCH_CATEGORY);

        $this->card->play($context);

        $this->stopwatch->stop($id);
    }

    public function setState(CardState $state): void
    {
        $this->card->setState($state);
    }

    public static function create(AbstractPlayableCard $card, Stopwatch $stopwatch): static
    {
        $traceableCard = new static();
        $traceableCard->card = $card;
        $traceableCard->stopwatch = $stopwatch;

        return $traceableCard;
    }

    public function getEffects(): EffectCollection
    {
        return $this->card->effects;
    }

    public function computeValue(): mixed
    {
        if ($this->card instanceof ComputedCardInterface) {
            return $this->card->computeValue();
        }

        return null;
    }

    public function setComputedValue(mixed $value): void
    {
        if ($this->card instanceof ComputedCardInterface) {
            $this->card->setComputedValue($value);
        }
    }
}
