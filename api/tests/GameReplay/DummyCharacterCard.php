<?php

declare(strict_types=1);

namespace App\Tests\GameReplay;

use App\Game\Card\Character\AbstractCharacterCard;

final class DummyCharacterCard extends AbstractCharacterCard
{
    public function getName(): string
    {
        return 'Dummy Character Card';
    }

    public function getId(): string
    {
        return 'dummy_character';
    }

    public function getHealthPoints(): int
    {
        return 1;
    }

    public function getDescription(): string
    {
        return '';
    }
}
