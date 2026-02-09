<?php

declare(strict_types=1);

namespace App\Tests\Resources\Fixtures\Builder;

use App\Entity\Game\InitialGameState;
use App\Game\Player;
use App\Game\State\PlayerState;
use App\Tests\Unit\Fixtures\DummyCard;

final class GameBuilder extends RoomBuilder
{
    public function build(): object
    {
        $this->withOpponent();
        parent::doBuild();

        $this->getEm()->persist($this->entity);
        $this->getEm()->flush();

        $defaultHand = [
            DummyCard::class,
        ];

        $gameState = new InitialGameState(
            $this->entity->getId()->toString(),
            new PlayerState(Player::fromUser($this->entity->getOwner()), 100, $defaultHand, []),
            new PlayerState(Player::fromUser($this->entity->getOpponent()), 100, $defaultHand, []),
        );

        $this->getEm()->persist($gameState);
        $this->getEm()->flush();

        return $gameState;
    }
}
