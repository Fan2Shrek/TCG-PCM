<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Deck;
use App\Entity\User;
use App\Game\Card\BenjaminCard;
use App\Game\Card\Character\PierrotCard;
use App\Game\Card\D6Card;
use App\Game\Card\GitmanCard;
use App\Game\Card\HackedZoneCard;
use App\Game\Card\Monster\RedBloonsMonsterCard;
use App\Game\Card\PlacentaCard;
use App\Game\Card\SpicyD6Card;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

final class DeckFixtures extends AbstractFixtures implements DependentFixtureInterface
{
    public function __construct()
    {
        parent::__construct(Deck::class);
    }

    public function getData(): iterable
    {
        $cards = [
            $id = new D6Card()->getId(),
            $id,
            $id = new SpicyD6Card()->getId(),
            $id,
            $id = new BenjaminCard()->getId(),
            $id,
            $id = new GitmanCard()->getId(),
            $id,
            $id = new PlacentaCard()->getId(),
            $id,
            $id = new RedBloonsMonsterCard()->getId(),
            $id,
            $id,
            new HackedZoneCard()->getId(),
        ];
        $pierrotCardId = new PierrotCard()->getId();

        yield [
            'user' => $this->getReference('User_1', User::class),
            'name' => 'deck_1',
            'cards' => $cards,
            'characterCard' => $pierrotCardId,
        ];

        yield [
            'user' => $this->getReference('User_1', User::class),
            'name' => 'deck_2',
            'cards' => $cards,
            'characterCard' => $pierrotCardId,
        ];
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}
