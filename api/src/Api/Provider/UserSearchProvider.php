<?php

declare(strict_types=1);

namespace App\Api\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\User;
use App\Repository\FriendshipRepository;
use App\Repository\UserRepository;
use App\Service\Auth\CurrentUserProviderInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @implements ProviderInterface<User>
 */
final class UserSearchProvider implements ProviderInterface
{
    private const int MAX_RESULTS = 10;

    public function __construct(
        private UserRepository $userRepository,
        private FriendshipRepository $friendshipRepository,
        private CurrentUserProviderInterface $currentUserProvider,
        private RequestStack $requestStack,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $user = $this->currentUserProvider->getCurrentUser();
        $query = $this->requestStack->getCurrentRequest()?->query->get('q', '');

        if ('' === trim($query)) {
            return [];
        }

        $result = $this->userRepository
            ->createQueryBuilder('u')
            ->where('u.username LIKE :query')
            ->andWhere('u != :user')
            ->setParameter('query', '%'.$query.'%')
            ->setParameter('user', $user)
            ->setMaxResults(self::MAX_RESULTS)
            ->getQuery()
            ->getResult();

        if (!\is_array($result)) {
            throw new \LogicException('Expected user query result to be an array.');
        }

        /** @var User[] $result */
        return array_values(array_filter($result, fn(User $candidate): bool => null === $this->friendshipRepository->findBetween($user, $candidate)));
    }
}
