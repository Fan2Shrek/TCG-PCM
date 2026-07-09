<?php

namespace App\Service\User;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class UserGenerateBoosterTokens
{
    private const MAX_BOOSTER_TOKENS = 5;
    private const BOOSTER_TOKEN_INTERVAL_HOURS = 12;

    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {}

    public function generate(User $user): int
    {
        $now = new \DateTimeImmutable();
        $userWallet = $user->getUserWallet();
        $userInfo = $user->getUserInfo();

        $interval = $userInfo->getLastBoosterTokensAt()->diff($now);
        $hours = (($interval->days ? $interval->days : 0) * 24) + $interval->h;
        $totalTokens = min(floor($hours / self::BOOSTER_TOKEN_INTERVAL_HOURS) + $userWallet->getBoosterTokens(), self::MAX_BOOSTER_TOKENS);
        $totalTokens = (int) round($totalTokens, 0);
        /** @var int $leftoverTime */
        $leftoverTime = $hours % self::BOOSTER_TOKEN_INTERVAL_HOURS;

        $userWallet->setBoosterTokens($totalTokens);

        $userInfo->setLastBoosterTokensAt($now->sub(new \DateInterval('PT'.$leftoverTime.'H')));

        $this->entityManager->flush();

        return $totalTokens;
    }
}
