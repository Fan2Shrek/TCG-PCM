<?php

declare(strict_types=1);

namespace App\Domain\Command\Room;

use App\Entity\Room;
use App\Service\Auth\CurrentUserProviderInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class JoinRoomHandler
{
    public function __construct(
        private CurrentUserProviderInterface $currentUserProvider,
    ) {}

    public function __invoke(JoinRoomCommand $command): Room
    {
        $user = $this->currentUserProvider->getCurrentUser();
        $room = $command->getCurrentResource();

        if ($user === $room->getOwner()) {
            throw new BadRequestException('Owner cannot join their own room.');
        }

        $room->setOpponent($user);

        return $room;
    }
}
