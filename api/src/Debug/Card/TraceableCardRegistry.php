<?php

declare(strict_types=1);

namespace App\Debug\Card;

use App\Enum\CardRarityEnum;
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

    public function getCardInstanceById(string $cardId): AbstractCard
    {
        $card = $this->decorated->getCardInstanceById($cardId);

        if ($card instanceof AbstractPlayableCard) {
            $card = TraceablePlayableCard::create($card, $this->stopwatch);
        }

        return $this->cards[] = $card;
    }

    public function getAllByRarity(CardRarityEnum $rarity): array
    {
        return $this->decorated->getAllByRarity($rarity);
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
