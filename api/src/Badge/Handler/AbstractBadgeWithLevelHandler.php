<?php

declare(strict_types=1);

namespace App\Badge\Handler;

use App\Badge\BadgeEventInterface;
use App\Entity\UserBadge;

abstract class AbstractBadgeWithLevelHandler implements BadgeHandlerInterface
{
    /**
     * @return array<int, int>
     */
    abstract protected function getLevels(): array;

    public function handle(BadgeEventInterface $event, UserBadge $userBadge): void
    {
        $currentScore = $userBadge->getScore();
        $newScore = $currentScore + 1;

        $userBadge->setLevel($this->getLevelForScore($newScore));
        $userBadge->setScore($newScore);
    }

    protected function getLevelForScore(int $score): int
    {
        $level = 0;
        foreach ($this->getLevels() as $lvl => $scoreThreshold) {
            if ($score < $scoreThreshold) {
                continue;
            }

            $level = $lvl;
        }

        return $level;
    }
}
