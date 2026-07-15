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
final class DeclineFriendRequestHandler
{
    public function __construct(
        private CurrentUserProviderInterface $currentUserProvider,
        private EntityManagerInterface $em,
        private HubInterface $hub,
    ) {}

    public function __invoke(DeclineFriendRequestCommand $command): void
    {
        $friendship = $command->getCurrentResource();
        $user = $this->currentUserProvider->getCurrentUser();

        if ($friendship->getAddressee() !== $user) {
            throw HttpException::fromStatusCode(Response::HTTP_FORBIDDEN, 'You cannot decline this friend request.');
        }

        if (FriendshipStatusEnum::PENDING !== $friendship->getStatus()) {
            throw HttpException::fromStatusCode(Response::HTTP_BAD_REQUEST, 'This friend request is no longer pending.');
        }

        $requesterUsername = $friendship->getRequester()->getUsername();

        $this->em->remove($friendship);
        $this->em->flush();

        $this->hub->publish(
            new Update(
                \sprintf('friendships/%s', $requesterUsername),
                json_encode(['type' => 'friend_request_declined', 'data' => ['addressee' => ['username' => $user->getUsername()]]], JSON_THROW_ON_ERROR),
                true,
            ),
        );
    }
}
