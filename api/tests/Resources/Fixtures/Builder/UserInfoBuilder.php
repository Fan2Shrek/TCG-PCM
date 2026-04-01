<?php

declare(strict_types=1);

namespace App\Tests\Resources\Fixtures\Builder;

use App\Entity\User;
use App\Entity\UserInfo;
use App\Entity\UserWallet;
use App\Tests\Resources\Fixtures\ThereIs;

/**
 * @extends AbstractBuilder<UserInfo>
 */
final class UserInfoBuilder extends AbstractBuilder
{
    private User $user;
    private \DateTimeImmutable $lastBoosterTokensAt;

    protected function doBuild(): void
    {
        $this->entity = new UserInfo($this->user ??= ThereIs::anUser()->build());
        $this->entity->setLastBoosterTokensAt($this->lastBoosterTokensAt ?? new \DateTimeImmutable());
    }

    public function for(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function withLastBoosterTokensAt(\DateTimeImmutable $lastBoosterTokensAt): self
    {
        $this->lastBoosterTokensAt = $lastBoosterTokensAt;

        return $this;
    }
}
