<?php

declare(strict_types=1);

namespace App\Domain\Command\Room;

use App\Enum\RoomStatusEnum;
use App\Service\Auth\CurrentUserProviderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class RemoveOpponentHandler
{
    public function __construct(
        private CurrentUserProviderInterface $currentUserProvider,
        private EntityManagerInterface $entityManager,
        private HubInterface $hub,
    ) {}

    public function __invoke(RemoveOpponentCommand $command): void
    {
        $room = $command->getCurrentResource();
        $user = $this->currentUserProvider->getCurrentUser();

        if ($room->getOwner() !== $user) {
            throw HttpException::fromStatusCode(Response::HTTP_FORBIDDEN, 'Only the room owner can remove the opponent.');
        }

        if ($room->getOpponent() === null) {
            throw HttpException::fromStatusCode(Response::HTTP_BAD_REQUEST, 'No opponent in this room.');
        }

        if ($room->getStatus() !== RoomStatusEnum::WAITING) {
            throw HttpException::fromStatusCode(Response::HTTP_BAD_REQUEST, 'Cannot remove opponent from a room that has already started.');
        }

        $room->setOpponent(null);
        $this->entityManager->flush();

        $topic = "game/{$room->getId()}";
        $this->hub->publish(
            new Update($topic, json_encode(['type' => 'opponent_removed'], JSON_THROW_ON_ERROR), true)
        );
    }
}
