<?php

declare(strict_types=1);

namespace App\Service;

use App\Enum\RoomStatusEnum;
use App\Game\State\GameState;
use App\Repository\RoomRepository;
use App\Service\Game\EndGameHandlerInterface;
use Doctrine\ORM\EntityManagerInterface;

final class EndGameHandler implements EndGameHandlerInterface
{
    public function __construct(
        private RoomRepository $roomRepository,
        private EntityManagerInterface $em,
    ) {}

    public function endGame(string $gameId, GameState $gameState, string $winnerId): void
    {
        if (!($room = $this->roomRepository->find($gameId))) {
            throw new \LogicException('Room not found for game ID: '.$gameId);
        }

        $room->setStatus(RoomStatusEnum::FINISHED);
        $room->setWinnerId($winnerId);

        $this->em->flush();
    }
}
