<?php

declare(strict_types=1);

namespace App\Domain\Command\Friendship;

use App\Enum\FriendshipStatusEnum;
use App\Service\Auth\CurrentUserProviderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class RemoveFriendHandler
{
    public function __construct(
        private CurrentUserProviderInterface $currentUserProvider,
        private EntityManagerInterface $em,
        private HubInterface $hub,
    ) {}

    public function __invoke(RemoveFriendCommand $command): void
    {
        $friendship = $command->getCurrentResource();
        $user = $this->currentUserProvider->getCurrentUser();

        if (!$friendship->involves($user)) {
            throw HttpException::fromStatusCode(Response::HTTP_FORBIDDEN, 'You are not part of this friendship.');
        }

        if (FriendshipStatusEnum::ACCEPTED !== $friendship->getStatus()) {
            throw HttpException::fromStatusCode(Response::HTTP_BAD_REQUEST, 'This friendship is not active.');
        }

        $otherUsername = $friendship->getOtherUser($user)->getUsername();

        $this->em->remove($friendship);
        $this->em->flush();

        $this->hub->publish(
            new Update(
                \sprintf('friendships/%s', $otherUsername),
                json_encode(['type' => 'friend_removed', 'data' => ['username' => $user->getUsername()]], JSON_THROW_ON_ERROR),
                true,
            ),
        );
    }
}
