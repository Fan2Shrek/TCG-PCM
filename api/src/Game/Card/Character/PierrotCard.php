<?php

declare(strict_types=1);

namespace App\Game\Card\Character;

final class PierrotCard extends AbstractCharacterCard
{
    public function getId(): string
    {
        return 'Pierrot';
    }

    public function getHealthPoints(): int
    {
        return 3300;
    }

    public function getName(): string
    {
        return 'Pierrot';
    }

    public function getDescription(): string
    {
        return 'Tord une carte tous les 2 tours';
    }
}
