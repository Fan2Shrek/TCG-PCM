<?php

declare(strict_types=1);

namespace App\Domain\Command\Friendship;

use App\Entity\Friendship;
use App\Repository\FriendshipRepository;
use App\Repository\UserRepository;
use App\Service\Auth\CurrentUserProviderInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class SendFriendRequestHandler
{
    public function __construct(
        private CurrentUserProviderInterface $currentUserProvider,
        private UserRepository $userRepository,
        private FriendshipRepository $friendshipRepository,
        private HubInterface $hub,
    ) {}

    public function __invoke(SendFriendRequestCommand $command): Friendship
    {
        $user = $this->currentUserProvider->getCurrentUser();
        $addressee = $this->userRepository->findOneBy(['username' => $command->username]);

        if (null === $addressee) {
            throw HttpException::fromStatusCode(Response::HTTP_NOT_FOUND, 'User not found.');
        }

        if ($addressee === $user) {
            throw HttpException::fromStatusCode(Response::HTTP_BAD_REQUEST, 'You cannot send a friend request to yourself.');
        }

        if (null !== $this->friendshipRepository->findBetween($user, $addressee)) {
            throw HttpException::fromStatusCode(Response::HTTP_BAD_REQUEST, 'A friendship or pending request already exists with this user.');
        }

        $friendship = new Friendship($user, $addressee);
        $this->friendshipRepository->save($friendship);

        $this->hub->publish(
            new Update(
                \sprintf('friendships/%s', $addressee->getUsername()),
                json_encode([
                    'type' => 'friend_request_received',
                    'data' => [
                        'id' => (string) $friendship->getId(),
                        'requester' => ['username' => $user->getUsername()],
                    ],
                ], JSON_THROW_ON_ERROR),
                true,
            ),
        );

        return $friendship;
    }
}
