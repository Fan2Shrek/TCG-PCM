<?php

declare(strict_types=1);

namespace App\Service;

use App\Badge\BadgeEventInterface;
use App\Badge\Handler\BadgeHandlerInterface;
use App\Entity\UserBadge;
use App\Enum\BadgeEnum;
use App\Repository\UserBadgeRepository;

final class BadgeManager
{
    /**
     * @param BadgeHandlerInterface[] $badgeHandlers
     */
    public function __construct(
        private iterable $badgeHandlers,
        private UserBadgeRepository $userBadgeRepository,
    ) {}

    public function handleFromEvent(BadgeEventInterface $event): void
    {
        $user = $event->getUser();

        $badgeKey = $event::getBadgeKey();
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
