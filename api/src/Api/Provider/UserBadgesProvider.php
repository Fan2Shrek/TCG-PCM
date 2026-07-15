<?php

declare(strict_types=1);

namespace App\Api\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Api\DTO\UserBadgeDTO;
use App\Api\DTO\UserBadgesDTO;
use App\Badge\Handler\BadgeHandlerInterface;
use App\Entity\UserBadge;
use App\Enum\BadgeEnum;
use App\Repository\UserBadgeRepository;
use App\Service\Auth\CurrentUserProviderInterface;

/**
 * @implements ProviderInterface<UserBadgesDTO>
 */
final class UserBadgesProvider implements ProviderInterface
{
    /**
     * @param iterable<BadgeHandlerInterface> $badgeHandlers
     */
    public function __construct(
        private iterable $badgeHandlers,
        private UserBadgeRepository $userBadgeRepository,
        private CurrentUserProviderInterface $currentUserProvider,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): UserBadgesDTO
    {
        $user = $this->currentUserProvider->getCurrentUser();

        $badges = array_map(
            fn(BadgeEnum $badgeKey): UserBadgeDTO => $this->buildDTO(
                $badgeKey,
                $this->userBadgeRepository->findByUserAndBadge($user, $badgeKey),
            ),
            BadgeEnum::cases(),
        );

        return new UserBadgesDTO($badges);
    }

    private function buildDTO(BadgeEnum $badgeKey, ?UserBadge $userBadge): UserBadgeDTO
    {
        $thresholds = $this->getHandlerForKey($badgeKey)->getThresholds();
        $level = $userBadge?->getLevel() ?? 0;

        return new UserBadgeDTO(
            badgeName: $badgeKey->value,
            level: $level,
            score: $userBadge?->getScore() ?? 0,
            currentThreshold: $thresholds[$level] ?? 0,
            nextThreshold: $thresholds[$level + 1] ?? null,
        );
    }

    private function getHandlerForKey(BadgeEnum $badgeKey): BadgeHandlerInterface
    {
        foreach ($this->badgeHandlers as $handler) {
            if ($handler::getBadgeKey() !== $badgeKey) {
                continue;
            }

            return $handler;
        }

        throw new \RuntimeException(\sprintf('No handler found for badge key %s', $badgeKey->value));
    }
}
