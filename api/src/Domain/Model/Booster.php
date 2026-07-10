<?php

declare(strict_types=1);

namespace App\Domain\Model;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\Api\DTO\BoosterOpenedCardDTO;
use App\Domain\Command\Booster\OpenBoosterCommand;
use App\Game\AbstractCard;

#[ApiResource(operations: [
    new Post(uriTemplate: '/boosters/open', messenger: 'input', input: OpenBoosterCommand::class, status: 200),
])]
final class Booster
{
    /**
     * @param array<AbstractCard|BoosterOpenedCardDTO> $cards
     */
    public function __construct(
        private array $cards,
    ) {}

    /**
     * @return array<AbstractCard|BoosterOpenedCardDTO>
     */
    public function getCards(): array
    {
        return $this->cards;
    }
}
