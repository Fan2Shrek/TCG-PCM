<?php

declare(strict_types=1);

namespace App\Game\Card\Character;

use App\Enum\CardEffectEnum;
use App\Game\GameUtils;

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
        return GameUtils::formatDescription('{{effect}} {{value1}} card every {{value2}} turns.', [
            'effect' => CardEffectEnum::TORNED,
            'value1' => 1,
            'value2' => 2,
        ]);
    }
}
