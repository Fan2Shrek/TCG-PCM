<?php

declare(strict_types=1);

namespace App\Domain\Model;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\Domain\Command\Booster\OpenBoosterCommand;

#[ApiResource(operations: [
    new Post(uriTemplate: '/boosters/open', messenger: 'input', input: OpenBoosterCommand::class, status: 200),
])]
final class Booster
{
    public function __construct(
        private array $cards,
    ) {}

    public function getCards(): array
    {
        return $this->cards;
    }
}
