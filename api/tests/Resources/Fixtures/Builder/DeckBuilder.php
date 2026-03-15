<?php

declare(strict_types=1);

namespace App\Tests\Resources\Fixtures\Builder;

use App\Entity\Deck;
use App\Entity\User;
use App\Game\Card\Character\PierrotCard;
use App\Tests\Resources\Fixtures\ThereIs;
use App\Tests\Unit\Fixtures\DummyCard;

/**
* @extends AbstractBuilder<Deck>
*/
final class DeckBuilder extends AbstractBuilder
{
    private User $owner;

    public function doBuild(): void
    {
        $owner = $this->owner ?? ThereIs::anUser()->build();
        $this->entity = new Deck($owner, 'Default Deck', new PierrotCard()->getId(), array_fill(0, 6, DummyCard::class));
    }

    public function ownedBy(User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }
}
