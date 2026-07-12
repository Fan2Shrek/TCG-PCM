<?php

declare(strict_types=1);

namespace App\Service;

use App\Enum\RoomStatusEnum;
use App\Repository\RoomRepository;
use App\Service\Game\EndGameHandlerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

final class EndGameHandler implements EndGameHandlerInterface
{
    private const int WIN_BOOSTER_TOKEN_REWARD = 1;

    public function __construct(
        private RoomRepository $roomRepository,
        private EntityManagerInterface $em,
        private HubInterface $hub,
    ) {}

    public function endGame(string $gameId, string $winnerId): void
    {
        if (!($room = $this->roomRepository->find($gameId))) {
            throw new \LogicException('Room not found for game ID: '.$gameId);
        }

        $room->setStatus(RoomStatusEnum::FINISHED);
        $room->setWinnerId($winnerId);

        $winner = match ($winnerId) {
            (string) $room->getOwner()->getId() => $room->getOwner(),
            (string) $room->getOpponent()?->getId() => $room->getOpponent(),
            default => null,
        };
        $winner?->getUserWallet()->addBoosterToken(self::WIN_BOOSTER_TOKEN_REWARD);

        $this->em->flush();

        $topic = \sprintf('game/%s', $gameId);
        $payload = json_encode([
            'type' => 'game_finished',
            'data' => [
                'roomId' => $gameId,
                'winnerId' => $winnerId,
            ],
        ], JSON_THROW_ON_ERROR);

        $this->hub->publish(new Update($topic, $payload, true));
    }
}
