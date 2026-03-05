<?php

declare(strict_types=1);

namespace App\Service\Game;

use App\Game\AbstractCard;

interface CardRegistryInterface
{
    public function getCardTemplateById(string $cardId): AbstractCard;

    /**
     * @param array<string, mixed> $criteria
     *
     * @return string[]
     */
    public function getAllBy(array $criteria): array;
}
