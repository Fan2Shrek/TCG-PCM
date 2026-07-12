<?php

declare(strict_types=1);

namespace App\Api\DTO;

final class CreateDeckInput
{
    /**
     * @var string[]
     */
    public array $cards = [];

    public string $name = '';

    public string $characterCard = '';

    public ?bool $isFavorite = null;
}
