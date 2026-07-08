<?php

declare(strict_types=1);

namespace App\Domain\Command\Room;

use App\Service\Auth\CurrentUserProviderInterface;
use App\Enum\RoomStatusEnum;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

#[AsMessageHandler]
final class LeaveRoomHandler
{
    public function __construct(
        private CurrentUserProviderInterface $currentUserProvider,
        private EntityManagerInterface $entityManager,
        private HubInterface $hub,
    ) {}

    public function __invoke(LeaveRoomCommand $command): void
    {
        $room = $command->getCurrentResource();
        $user = $this->currentUserProvider->getCurrentUser();
        $roomId = (string) $room->getId();

        if ($room->getOwner() !== $user && $room->getOpponent() !== $user) {
            throw HttpException::fromStatusCode(Response::HTTP_FORBIDDEN, 'You are not in this room.');
        }

        if ($room->getStatus() === RoomStatusEnum::WAITING) {
            if ($room->getOwner() === $user) {
                $this->entityManager->remove($room);
                if ($room->getOpponent() !== null) {
                    $this->publishOwnerLeft($roomId);
                }
            } else {
                $room->setOpponent(null);
            }
        } else {
            $otherPlayer = $room->getOwner() === $user ? $room->getOpponent() : $room->getOwner();

            if ($otherPlayer === null) {
                throw HttpException::fromStatusCode(Response::HTTP_BAD_REQUEST, 'Cannot determine winner.');
            }

            $room->setWinnerId((string) $otherPlayer->getId());
            $room->setStatus(RoomStatusEnum::FINISHED);
        }

        $this->entityManager->flush();
    }

    private function publishOwnerLeft(string $roomId): void
    {
        $topic = "game/{$roomId}";
        $payload = json_encode(['type' => 'owner_left'], JSON_THROW_ON_ERROR);
        $update = new Update($topic, $payload, true);
        $this->hub->publish($update);
    }
}
