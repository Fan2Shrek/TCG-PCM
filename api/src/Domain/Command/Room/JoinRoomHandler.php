<?php

declare(strict_types=1);

namespace App\Domain\Command\Room;

use App\Service\Auth\CurrentUserProviderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class JoinRoomHandler
{
    public function __construct(
        private CurrentUserProviderInterface $currentUserProvider,
        private EntityManagerInterface $em,
        private HubInterface $hub,
    ) {}

    public function __invoke(JoinRoomCommand $command): array
    {
        $user = $this->currentUserProvider->getCurrentUser();

        if (!($deck = $user->getDecks()->first())) {
            throw HttpException::fromStatusCode(Response::HTTP_BAD_REQUEST, 'User has no deck to join a room.');
        }

        $room = $command->getCurrentResource();

        if ($user === $room->getOwner()) {
            throw HttpException::fromStatusCode(Response::HTTP_BAD_REQUEST, 'User cannot join their own room.');
        }

        $room->setOpponent($user);
        $room->setOpponentDeck($deck);

        $this->em->flush();

        $topic = \sprintf('game/%s', $room->getId());
        $token = $this->hub->getFactory()?->create([$topic], []);
        $url = \sprintf('%s?topic=%s', $this->hub->getPublicUrl(), $topic);

        $this->hub->publish(
            new Update($topic, json_encode([
                'type' => 'opponent_joined',
                'data' => [
                    'opponent' => $user->getUsername(),
                ],
            ])),
        );

        return [
            'id' => $room->getId(),
            'mercure_url' => $url,
            'mercure_token' => $token,
        ];
    }
}
