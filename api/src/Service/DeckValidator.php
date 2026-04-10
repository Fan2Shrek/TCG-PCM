<?php

declare(strict_types=1);

namespace App\Service;

use App\Domain\Exception\InvalidDeckException;
use App\Entity\Deck;
use App\Enum\CardRarityEnum;
use App\Service\Game\CardRegistryInterface;

final class DeckValidator
{
    public const DECK_SIZE = 50;

    private const array RARITY_LIMITS = [
        CardRarityEnum::UNCOMMON->value => 7,
        CardRarityEnum::RARE->value => 6,
        CardRarityEnum::EPIC->value => 5,
        CardRarityEnum::LEGENDARY->value => 3,
    ];

    public function __construct(
        private CardRegistryInterface $cardRegistry,
    ) {}

    public function validateDeck(Deck $deck): bool
    {
        $inventory = $deck->getUser()->getInventory();
        $allCards = $deck->getCards();
        $cardsMap = [];
        $rarityCount = [
            CardRarityEnum::COMMON->value => 0,
            CardRarityEnum::UNCOMMON->value => 0,
            CardRarityEnum::RARE->value => 0,
            CardRarityEnum::EPIC->value => 0,
            CardRarityEnum::LEGENDARY->value => 0,
        ];

        if (50 !== count($allCards)) {
            throw new InvalidDeckException(\sprintf('Deck should have %d cards (currently %d)', self::DECK_SIZE, count($allCards)));
        }

        if (!$this->cardRegistry->has($deck->getCharacterCard())) {
            throw new InvalidDeckException(\sprintf('Character card "%s" does not exist', $deck->getCharacterCard()));
        }

        foreach ($allCards as $card) {
            if (!$this->cardRegistry->has($card)) {
                throw new InvalidDeckException(\sprintf('Card "%s" does not exist', $card));
            }
            $template = $this->cardRegistry->getCardTemplateById($card);

            if (!($cardsMap[$card] ?? null)) {
                $cardsMap[$card] = 0;
            }

            ++$cardsMap[$card];

            $rarity = $template::$rarity->value;
            $rarityCount[$rarity]++;
        }

        foreach (self::RARITY_LIMITS as $rarity => $limit) {
            if ($rarityCount[$rarity] > $limit) {
                throw new InvalidDeckException(\sprintf('Deck cannot have more than %d %s cards (currently %d)', $limit, $rarity, $rarityCount[$rarity]));
            }
        }

        foreach ($cardsMap as $cardId => $count) {
            if (!($cardInventory = $inventory->findCardByCardId($cardId))) {
                throw new InvalidDeckException(\sprintf('User does not have card "%s"', $cardId));
            }

            if ($cardInventory->getQuantity() < $count) {
                throw new InvalidDeckException(\sprintf(
                    'User does not have enough copies of card "%s" (has %d, needs %d)',
                    $cardId,
                    $cardInventory->getQuantity(),
                    $count,
                ));
            }
        }

        return true;
    }
}
