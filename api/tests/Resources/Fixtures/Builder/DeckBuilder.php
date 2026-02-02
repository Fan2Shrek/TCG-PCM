<?php

declare(strict_types=1);

namespace App\Tests\Resources\Fixtures\Builder;

use App\Entity\Deck;
use App\Entity\User;
use App\Game\Card\Character\PierrotCard;
use App\Tests\Resources\Fixtures\ThereIs;

/**
* @extends AbstractBuilder<Deck>
*/
final class DeckBuilder extends AbstractBuilder
{
    private User $owner;

    public function doBuild(): void
    {
        $owner = $this->owner ?? ThereIs::anUser()->build();
        $this->entity = new Deck($owner, 'Default Deck', PierrotCard::class);
    }

    public function ownedBy(User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }
}
