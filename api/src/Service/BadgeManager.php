<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\UserBadge;
use App\Enum\BadgeEnum;
use App\Game\Badge\BadgeEventInterface;
use App\Game\Badge\Handler\BadgeHandlerInterface;
use App\Repository\UserBadgeRepository;
use App\Service\Auth\CurrentUserProviderInterface;

final class BadgeManager
{
    /**
     * @param BadgeHandlerInterface[] $badgeHandlers
     */
    public function __construct(
        private iterable $badgeHandlers,
        private UserBadgeRepository $userBadgeRepository,
        private CurrentUserProviderInterface $currentUserProvider,
    ) {}

    public function handleFromEvent(BadgeEventInterface $event): void
    {
        $user = $this->currentUserProvider->getCurrentUser();

        $badgeKey = $event::geBadgeKey();
        $handler = $this->getHandlerForKey($badgeKey);

        if (!($userBadge = $this->userBadgeRepository->findByUserAndBadge($user, $badgeKey))) {
            $userBadge = new UserBadge($user, $badgeKey);
        }

        $handler->handle($event, $userBadge);

        $this->userBadgeRepository->save($userBadge);
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
