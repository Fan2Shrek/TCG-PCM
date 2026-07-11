<?php

declare(strict_types=1);

namespace App\Domain\Model;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use App\Api\Provider\BoosterObtainableCardsProvider;
use App\Api\DTO\CollectionCardDTO;
use App\Domain\Command\Booster\OpenBoosterCommand;
use App\Game\AbstractCard;

#[ApiResource(operations: [
    new Get(uriTemplate: '/boosters/cards', provider: BoosterObtainableCardsProvider::class, status: 200),
    new Post(uriTemplate: '/boosters/open', messenger: 'input', input: OpenBoosterCommand::class, status: 200),
])]
final class Booster
{
    /**
     * @param array<AbstractCard|CollectionCardDTO> $cards
     */
    public function __construct(
        private array $cards,
    ) {}

    /**
     * @return array<AbstractCard|CollectionCardDTO>
     */
    public function getCards(): array
    {
        return $this->cards;
    }
}
