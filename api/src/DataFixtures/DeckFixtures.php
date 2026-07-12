<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\DataFixtures\Inventory\CardInventoryFixtures;
use App\Entity\Deck;
use App\Entity\Inventory\CardInventory;
use App\Entity\Inventory\Inventory;
use App\Entity\User;
use App\Enum\CardTypeEnum;
use App\Game\Card\Character\PierrotCard;
use App\Game\Card\Character\StonksCard;
use App\Service\DeckValidator;
use App\Service\Game\CardRegistryInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

final class DeckFixtures extends AbstractFixtures implements DependentFixtureInterface
{
    public function __construct(
        private CardRegistryInterface $cardRegistry,
    ) {
        parent::__construct(Deck::class);
    }

    public function getData(): iterable
    {
        $allCards = array_values(array_filter(
            $this->cardRegistry->getAllBy([]),
            fn(string $cardId): bool => CardTypeEnum::CHARACTER !== $this->cardRegistry->getCardTemplateById($cardId)->getType(),
        ));
        $cards = [];

        $count = count($allCards);

        $i = 0;
        while (count($cards) < DeckValidator::DECK_SIZE) {
            $cards[] = $allCards[$i % $count];
            $i++;
        }

        $pierrotCardId = new PierrotCard()->getId();
        $stonksCardId = new StonksCard()->getId();

        $user1 = $this->getReference('User_1', User::class);
        $user2 = $this->getReference('User_2', User::class);

        yield [
            'user' => $user1,
            'name' => 'deck_1',
            'cards' => $cards,
            'characterCard' => $pierrotCardId,
            'isFavorite' => true,
        ];

        yield [
            'user' => $user2,
            'name' => 'deck_2',
            'cards' => $cards,
            'characterCard' => $stonksCardId,
            'isFavorite' => false,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        foreach ($this->getData() as $key => $data) {
            /** @var User $user */
            $user = $data['user'];
            $cards = $data['cards'];
            $characterCard = $data['characterCard'];

            $this->synchronizeInventoryForDeck(manager: $manager, inventory: $user->getInventory(), cards: [...$cards, $characterCard]);

            $deck = new Deck($user, $data['name'], $characterCard, $cards);
            $deck->setIsFavorite($data['isFavorite']);

            $manager->persist($deck);
            ++$key;
            $this->addReference(Deck::class.'_'.$key, $deck);
        }

        $manager->flush();
    }

    /**
     * @param string[] $cards
     */
    private function synchronizeInventoryForDeck(ObjectManager $manager, Inventory $inventory, array $cards): void
    {
        $requiredCards = array_count_values($cards);

        foreach ($requiredCards as $cardId => $requiredQuantity) {
            $existingCardInventory = $inventory->findCardByCardId($cardId);

            if (null === $existingCardInventory) {
                $newCardInventory = new CardInventory($inventory, $cardId);
                $newCardInventory->setQuantity($requiredQuantity);
                $inventory->addCard($newCardInventory);
                $manager->persist($newCardInventory);

                continue;
            }

            if ($existingCardInventory->getQuantity() < $requiredQuantity) {
                $existingCardInventory->setQuantity($requiredQuantity);
            }
        }
    }

    public function getDependencies(): array
    {
        return [
            CardInventoryFixtures::class,
        ];
    }
}
