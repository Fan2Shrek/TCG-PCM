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
        private \Doctrine\ORM\EntityManagerInterface $entityManager,
    ) {}

    public function __invoke(ChangeDeckCommand $command): void
    {
        $room = $command->getCurrentResource();
        $user = $this->currentUserProvider->getCurrentUser();
        $deck = $command->deck;

        if ($deck->getUser() !== $user) {
            throw HttpException::fromStatusCode(Response::HTTP_FORBIDDEN, 'You can only select your own deck.');
        }

        if ($deck->isDeleted()) {
            throw HttpException::fromStatusCode(Response::HTTP_BAD_REQUEST, 'Deleted decks cannot be selected.');
        }

        match (true) {
            $room->getOwner() === $user => $room->setOwnerDeck($deck),
            $room->getOpponent() === $user => $room->setOpponentDeck($deck),
            default => throw HttpException::fromStatusCode(Response::HTTP_FORBIDDEN, 'You are not a player in this room.'),
        };

        $this->entityManager->flush();
    }
}
