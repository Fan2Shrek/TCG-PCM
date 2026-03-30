<?php

declare(strict_types=1);

namespace App\Tests\Resources\Fixtures\Builder;

use App\Entity\Inventory\Inventory;
use App\Entity\User;
use App\Tests\Resources\Fixtures\ThereIs;

/**
 * @extends AbstractBuilder<User>
 */
final class UserBuilder extends AbstractBuilder
{
    protected static array $usedIds = [];

    private Inventory $inventory;

    public function build(): object
    {
        $user = parent::build();

        $this->inventory ??= ThereIs::anInventory()->for($user)->build();
        $user->setInventory($this->inventory);

        return $user;
    }

    public function withInventory(Inventory $inventory): self
    {
        $this->inventory = $inventory;

        return $this;
    }

    protected function doBuild(): void
    {
        $id = 'user_'.spl_object_id($this);

        if (\in_array($id, self::$usedIds, true)) {
            $id = 'user_'.spl_object_id($this).'-'.count(self::$usedIds);
        }
        self::$usedIds[] = $id;

        $this->entity = new User($id);
        $this->entity->setPassword('password');
    }
}
