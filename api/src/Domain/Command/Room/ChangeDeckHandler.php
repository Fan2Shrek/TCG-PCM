<?php

declare(strict_types=1);

namespace App\Domain\Command\Room;

use App\Service\Auth\CurrentUserProviderInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class ChangeDeckHandler
{
    public function __construct(
        private CurrentUserProviderInterface $currentUserProvider,
    ) {}

    public function __invoke(ChangeDeckCommand $command): void
    {
        $room = $command->getCurrentResource();
        $user = $this->currentUserProvider->getCurrentUser();

        match (true) {
            $room->getOwner() === $user => $room->setOwnerDeck($command->deck),
            $room->getOpponent() === $user => $room->setOpponentDeck($command->deck),
            default => throw HttpException::fromStatusCode(
                Response::HTTP_FORBIDDEN,
                'You are not a player in this room.',
            ),
        };
    }
}
