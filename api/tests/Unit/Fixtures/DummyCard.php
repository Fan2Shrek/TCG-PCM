<?php

declare(strict_types=1);

namespace App\Tests\Unit\Fixtures;

use App\Game\Card\AbstractPlayableCard;
use App\Game\GameContext;

final class DummyCard extends AbstractPlayableCard
{
    public function getName(): string
    {
        return 'DummyCard';
    }

    public function getId(): string
    {
        return 'DummyCard';
    }

    public function getDescription(): string
    {
        return 'This is a dummy card for testing purposes.';
    }

    public function play(GameContext $context): void
    {
        // No operation for dummy card
    }
}
