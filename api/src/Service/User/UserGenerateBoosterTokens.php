<?php

namespace App\Service\User;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class UserGenerateBoosterTokens
{
    private const MAX_BOOSTER_TOKENS = 5;
    private const BOOSTER_TOKEN_INTERVAL_HOURS = 10;
    private const BOOSTER_TOKEN_INTERVAL_SECONDS = self::BOOSTER_TOKEN_INTERVAL_HOURS * 60 * 60;

    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {}

    public function generate(User $user): int
    {
        $now = new \DateTimeImmutable();
        $userWallet = $user->getUserWallet();
        $userInfo = $user->getUserInfo();

        $elapsedSeconds = max(0, $now->getTimestamp() - $userInfo->getLastBoosterTokensAt()->getTimestamp());
        $tokensEarned = intdiv($elapsedSeconds, self::BOOSTER_TOKEN_INTERVAL_SECONDS);
        $totalTokens = min($userWallet->getBoosterTokens() + $tokensEarned, self::MAX_BOOSTER_TOKENS);

        $userWallet->setBoosterTokens($totalTokens);

        if ($tokensEarned > 0 || $userWallet->getBoosterTokens() >= self::MAX_BOOSTER_TOKENS) {
            $leftoverSeconds = $elapsedSeconds % self::BOOSTER_TOKEN_INTERVAL_SECONDS;
            $userInfo->setLastBoosterTokensAt($now->sub(new \DateInterval('PT'.$leftoverSeconds.'S')));
        }

        $this->entityManager->flush();

        return $totalTokens;
    }
}
