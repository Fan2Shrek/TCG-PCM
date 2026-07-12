<?php

declare(strict_types=1);

namespace App\Api\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Api\DTO\CardCollectionDTO;
use App\Api\DTO\CollectionCardDTO;
use App\Game\AbstractCard;
use App\Game\Card\Character\AbstractCharacterCard;
use App\Game\Card\Monster\AbstractMonsterCard;
use App\Service\Auth\CurrentUserProviderInterface;
use App\Service\Game\CardRegistryInterface;

/**
 * @implements ProviderInterface<CardCollectionDTO>
 */
final class CardCollectionProvider implements ProviderInterface
{
    public function __construct(
        private CurrentUserProviderInterface $currentUserProvider,
        private CardRegistryInterface $cardRegistry,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): CardCollectionDTO
    {
        $inventory = $this->currentUserProvider->getCurrentUser()->getInventory();

        $quantityByCardId = [];
        foreach ($inventory->getCards() as $inventoryCard) {
            $quantityByCardId[$inventoryCard->getCard()] = $inventoryCard->getQuantity();
        }

        $cardIds = $this->cardRegistry->getAllBy([]);
        sort($cardIds);

        // Some registry entries alias the same underlying card class (same AbstractCard::getId()),
        // so entries are keyed and merged by the card's own id to avoid listing it twice.
        $quantityByResolvedId = [];
        $cardByResolvedId = [];
        foreach ($cardIds as $cardId) {
            $card = $this->cardRegistry->getCardTemplateById($cardId);
            $resolvedId = $card->getId();

            $cardByResolvedId[$resolvedId] ??= $card;
            $quantityByResolvedId[$resolvedId] = ($quantityByResolvedId[$resolvedId] ?? 0) + ($quantityByCardId[$cardId] ?? 0);
        }

        $entries = array_map(fn(string $resolvedId): array => [
            'card' => $this->buildCollectionCardDTO($cardByResolvedId[$resolvedId]),
            'quantity' => $quantityByResolvedId[$resolvedId],
        ], array_keys($cardByResolvedId));

        return new CardCollectionDTO($entries);
    }

    private function buildCollectionCardDTO(AbstractCard $card): CollectionCardDTO
    {
        $cost = null;
        $hp = null;
        $attack = null;

        if (!$card instanceof AbstractCharacterCard) {
            $cost = $card->getCost();
        }

        if ($card instanceof AbstractMonsterCard) {
            $hp = $card->getHealPoints();
            $attack = $card->getAttack();
        }

        return new CollectionCardDTO(
            name: $card->getName(),
            description: $card->getDescription(),
            image: $card->getImage(),
            rarity: $card::$rarity,
            set: $card::$serie,
            instanceId: $card->getId(),
            type: $card->getType(),
            cost: $cost,
            hp: $hp,
            attack: $attack,
        );
    }
}
