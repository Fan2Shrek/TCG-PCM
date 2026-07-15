<?php

declare(strict_types=1);

namespace App\Domain\Model;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\Api\Provider\CardEffectProvider;

#[ApiResource(operations: [
    new GetCollection(uriTemplate: '/card-effects', status: 200, provider: CardEffectProvider::class),
])]
final readonly class CardEffect
{
    public function __construct(
        public string $name,
        public string $description,
    ) {}
}
