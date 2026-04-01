<?php

declare(strict_types=1);

namespace App\Tests\Resources\Fixtures\Builder;

use App\Entity\User;
use App\Entity\UserWallet;
use App\Tests\Resources\Fixtures\ThereIs;

/**
 * @extends AbstractBuilder<UserWallet>
 */
final class UserWalletBuilder extends AbstractBuilder
{
    private User $user;
    private int $boosterTokens = 0;

    protected function doBuild(): void
    {
        $this->entity = new UserWallet($this->user ??= ThereIs::anUser()->build());
        $this->entity->setBoosterTokens($this->boosterTokens);
    }

    public function for(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function withBoosterTokens(int $boosterTokens): self
    {
        $this->boosterTokens = $boosterTokens;

        return $this;
    }
}
