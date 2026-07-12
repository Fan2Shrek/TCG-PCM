<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Deck;
use App\Entity\User;
use App\Enum\CardTypeEnum;
use App\Game\Card\Character\PierrotCard;
use App\Game\Card\Character\StonksCard;
use App\Service\DeckValidator;
use App\Service\Game\CardRegistryInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

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

        yield [
            'user' => $this->getReference('User_1', User::class),
            'name' => 'deck_1',
            'cards' => $cards,
            'characterCard' => $pierrotCardId,
            'isFavorite' => true,
        ];

        yield [
            'user' => $this->getReference('User_2', User::class),
            'name' => 'deck_2',
            'cards' => $cards,
            'characterCard' => $stonksCardId,
            'isFavorite' => false,
        ];
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}
