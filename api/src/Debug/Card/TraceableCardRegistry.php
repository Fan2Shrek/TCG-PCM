<?php

declare(strict_types=1);

namespace App\Debug\Card;

use App\Game\AbstractCard;
use App\Game\Card\AbstractPlayableCard;
use App\Service\Game\CardRegistryInterface;
use Symfony\Component\Stopwatch\Stopwatch;

final class TraceableCardRegistry implements CardRegistryInterface
{
    /**
     * @var AbstractCard[]
     */
    private array $cards = [];

    public function __construct(
        private CardRegistryInterface $decorated,
        private Stopwatch $stopwatch,
    ) {}

    public function getCardTemplateById(string $cardId): AbstractCard
    {
        $card = $this->decorated->getCardTemplateById($cardId);

        if ($card instanceof AbstractPlayableCard) {
            $card = TraceablePlayableCard::create($card, $this->stopwatch);
        }

        return $this->cards[] = $card;
    }

    public function getAllBy(array $criteria): array
    {
        return $this->decorated->getAllBy($criteria);
    }

    public function hasCards(): bool
    {
        return [] !== $this->cards;
    }

    /**
     * @return AbstractCard[]
     */
    public function getCards(): array
    {
        return $this->cards;
    }
}
