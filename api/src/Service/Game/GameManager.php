<?php

declare(strict_types=1);

namespace App\Service\Game;

use App\Entity\Room;
use App\Entity\User;
use App\Enum\RoomStatusEnum;
use App\Game\AbstractCard;
use App\Game\GameContext;
use App\Game\Player;

final class GameManager
{
    public function __construct(
        private GameContextRepositoryInterface $gameContextRepository,
    ) {}

    public function startGame(Room $room): void
    {
        $room->setStatus(RoomStatusEnum::PLAYING);

        if (!($opponent = $room->getOpponent())) {
            throw new \RuntimeException('Room has no opponent');
        }

        $gameContext = new GameContext(
            $this->createPlayerFromUser($room->getOwner()),
            $this->createPlayerFromUser($opponent),
        );

        $this->gameContextRepository->save($gameContext, $room);
    }

    public function play(AbstractCard $card, Room $room): void
    {
        $gameContext = $this->gameContextRepository->get($room);
    }

    private function createPlayerFromUser(User $user): Player
    {
        // @todo deck
        // @todo draw cards
        // @todo get health point from character card
        return new Player($user->getUsername(), 30);
    }
}
