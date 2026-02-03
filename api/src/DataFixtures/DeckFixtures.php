<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Deck;
use App\Entity\User;
use App\Game\Card\Character\PierrotCard;
use App\Game\Card\D6Card;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

final class DeckFixtures extends AbstractFixtures implements DependentFixtureInterface
{
    public function __construct()
    {
        parent::__construct(Deck::class);
    }

    public function getData(): iterable
    {
        yield [
            'user' => $this->getReference('User_1', User::class),
            'name' => 'deck_1',
            'cards' => [
                D6Card::class,
            ],
            'characterCard' => PierrotCard::class,
        ];

        yield [
            'user' => $this->getReference('User_1', User::class),
            'name' => 'deck_2',
            'cards' => [
                D6Card::class,
            ],
            'characterCard' => PierrotCard::class,
        ];
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}
