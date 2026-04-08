<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Domain\Exception\InvalidDeckException;
use App\Entity\Deck;
use App\Entity\Inventory\CardInventory;
use App\Entity\Inventory\Inventory;
use App\Entity\User;
use App\Enum\CardRarityEnum;
use App\Service\DeckValidator;
use App\Service\Game\CardRegistryInterface;
use App\Tests\Unit\Fixtures\DummyCard;
use PHPUnit\Framework\TestCase;

final class DeckValidatorTest extends TestCase
{
    private CardRegistryInterface $cardRegistry;
    private DeckValidator $deckValidator;

    protected function setUp(): void
    {
        $this->cardRegistry = $this->createStub(CardRegistryInterface::class);
        $this->deckValidator = new DeckValidator($this->cardRegistry);
    }

    public function testValidateDeckWithValidDeck(): void
    {
        $cards = $this->createValidCardsList();

        $deck = $this->createDeckWithCards('character-card', $cards);

        $this->setupCardRegistry($cards, 'character-card');

        $this->setupUserInventoryWithCards($deck->getUser(), $cards);

        $result = $this->deckValidator->validateDeck($deck);

        self::assertTrue($result);
    }

    public function testValidateDeckWithInvalidSize(): void
    {
        $this->expectException(InvalidDeckException::class);
        $this->expectExceptionMessage('Deck should have 50 cards (currently 30)');

        $cards = array_fill(0, 30, 'card-1');
        $deck = $this->createDeckWithCards('character-card', $cards);

        $this->setupCardRegistry(['card-1', 'character-card'], 'character-card');

        $this->deckValidator->validateDeck($deck);
    }

    public function testValidateDeckWithMissingCharacterCard(): void
    {
        $this->expectException(InvalidDeckException::class);
        $this->expectExceptionMessage('Character card "nonexistent-character" does not exist');

        $cards = array_fill(0, 50, 'card-1');
        $deck = $this->createDeckWithCards('nonexistent-character', $cards);

        $this->cardRegistry->method('has')->willReturnCallback(static fn($card) => $card !== 'nonexistent-character');

        $this->deckValidator->validateDeck($deck);
    }

    public function testValidateDeckWithNonexistentCard(): void
    {
        $this->expectException(InvalidDeckException::class);
        $this->expectExceptionMessage('Card "nonexistent-card" does not exist');

        $cards = array_merge(array_fill(0, 49, 'card-1'), ['nonexistent-card']);
        $deck = $this->createDeckWithCards('character-card', $cards);

        $this->cardRegistry
            ->method('has')
            ->willReturnCallback(static fn($card) => $card !== 'nonexistent-card' && $card !== 'character-card' || $card === 'character-card');

        $this->deckValidator->validateDeck($deck);
    }

    public function testValidateDeckExceedsRarityLimit(): void
    {
        $this->expectException(InvalidDeckException::class);
        $this->expectExceptionMessage('Deck cannot have more than 3 legendary cards');

        // Create 50 cards with 4 legendary cards (exceeds limit of 3)
        $cards = array_merge(array_fill(0, 46, 'common-card'), ['legendary-card-1', 'legendary-card-2', 'legendary-card-3', 'legendary-card-4']);

        $deck = $this->createDeckWithCards('character-card', $cards);

        // Setup card registry with different rarities
        $this->setupCardRegistryWithRarities([
            'common-card' => CardRarityEnum::COMMON,
            'character-card' => CardRarityEnum::COMMON,
            'legendary-card-1' => CardRarityEnum::LEGENDARY,
            'legendary-card-2' => CardRarityEnum::LEGENDARY,
            'legendary-card-3' => CardRarityEnum::LEGENDARY,
            'legendary-card-4' => CardRarityEnum::LEGENDARY,
        ]);

        $this->setupUserInventoryWithCards($deck->getUser(), $cards);

        $this->deckValidator->validateDeck($deck);
    }

    public function testValidateDeckWithExactlyTwoCopiesIsValid(): void
    {
        $cards = array_merge(['card-1', 'card-1'], array_fill(0, 48, 'other-card'));

        $deck = $this->createDeckWithCards('character-card', $cards);

        $this->setupCardRegistry($cards, 'character-card');

        $this->setupUserInventoryWithCards($deck->getUser(), $cards);

        $result = $this->deckValidator->validateDeck($deck);

        self::assertTrue($result);
    }

    public function testValidateDeckWithInsufficientInventory(): void
    {
        $this->expectException(InvalidDeckException::class);

        $cards = array_merge(['card-1', 'card-1'], array_fill(0, 48, 'other-card'));

        $deck = $this->createDeckWithCards('character-card', $cards);

        $this->setupCardRegistry($cards, 'character-card');

        $user = $deck->getUser();
        $inventory = $user->getInventory();

        foreach ($inventory->getCards() as $c) {
            $inventory->removeCard($c);
        }

        $cardInventory = new CardInventory($inventory, 'card-1');
        $cardInventory->setQuantity(1);
        $inventory->addCard($cardInventory);

        foreach (array_unique(['other-card']) as $cardId) {
            $cardInv = new CardInventory($inventory, $cardId);
            $cardInv->setQuantity(10);
            $inventory->addCard($cardInv);
        }

        $this->deckValidator->validateDeck($deck);
    }

    public function testValidateDeckWithComplexRarityDistribution(): void
    {
        // Test witha valid complex distribution:
        // - 35 common cards
        // - 7 uncommon cards (at max limit)
        // - 4 rare cards (below max limit of 6)
        // - 3 epic cards (below max limit of 5)
        // - 1 legendary card (below max limit of 3)

        $cards = array_merge(
            array_fill(0, 35, 'common'),
            array_fill(0, 7, 'uncommon-1'),
            array_fill(0, 4, 'rare-1'),
            array_fill(0, 3, 'epic-1'),
            ['legendary-1'],
        );

        $deck = $this->createDeckWithCards('character-card', $cards);

        $this->setupCardRegistryWithRarities([
            'common' => CardRarityEnum::COMMON,
            'uncommon-1' => CardRarityEnum::UNCOMMON,
            'rare-1' => CardRarityEnum::RARE,
            'epic-1' => CardRarityEnum::EPIC,
            'legendary-1' => CardRarityEnum::LEGENDARY,
            'character-card' => CardRarityEnum::COMMON,
        ]);

        $this->setupUserInventoryWithCards($deck->getUser(), $cards);

        $result = $this->deckValidator->validateDeck($deck);

        self::assertTrue($result);
    }

    private function createValidCardsList(): array
    {
        return array_merge(array_fill(0, 46, 'common-card'), ['rare-card-1', 'rare-card-2', 'epic-card-1', 'legendary-card-1']);
    }

    private function createDeckWithCards(string $characterCard, array $cards): Deck
    {
        $user = $this->createStub(User::class);
        $user->method('getId')->willReturn(spl_object_id($user));
        $this->setupUserInventoryWithCards($user, $cards);

        return new Deck($user, 'Test Deck', $characterCard, $cards);
    }

    private function setupCardRegistry(array $cards, string $characterCard): void
    {
        $allCards = array_unique(array_merge($cards, [$characterCard]));

        $this->cardRegistry->method('has')->willReturnCallback(static fn($card) => \in_array($card, $allCards, true));

        $this->cardRegistry->method('getCardTemplateById')->willReturn(new DummyCard());
    }

    private function setupCardRegistryWithRarities(array $cardRarityMap): void
    {
        $this->cardRegistry->method('has')->willReturnCallback(static fn($card) => null !== $cardRarityMap[$card] ?? null);

        $this->cardRegistry
            ->method('getCardTemplateById')
            ->willReturnCallback(static function ($cardId) use ($cardRarityMap) {
                $rarity = $cardRarityMap[$cardId] ?? CardRarityEnum::COMMON;

                return CardRarityEnum::LEGENDARY === $rarity ? new LegendaryCard() : new DummyCard();
            });
    }

    private function setupUserInventoryWithCards(User $user, array $cards): void
    {
        $inventory = new Inventory($user);
        $cardCounts = array_count_values($cards);

        foreach ($cardCounts as $cardId => $count) {
            $cardInventory = new CardInventory($inventory, $cardId);
            $cardInventory->setQuantity($count + 1);
            $inventory->addCard($cardInventory);
        }

        $user->method('getInventory')->willReturn($inventory);
    }
}

class LegendaryCard extends DummyCard
{
    public static CardRarityEnum $rarity = CardRarityEnum::LEGENDARY;
}
