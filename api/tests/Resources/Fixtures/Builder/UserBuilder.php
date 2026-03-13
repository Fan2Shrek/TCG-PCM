<?php

declare(strict_types=1);

namespace App\Tests\Resources\Fixtures\Builder;

use App\Entity\User;

/**
 * @extends AbstractBuilder<User>
 */
final class UserBuilder extends AbstractBuilder
{
    protected static array $usedIds = [];

    protected function doBuild(): void
    {
        $id = 'user_'.spl_object_id($this);

        if (\in_array($id, self::$usedIds)) {
            $id = 'user_'.spl_object_id($this).'-'.count(self::$usedIds);
        }
        self::$usedIds[] = $id;

        $this->entity = new User($id);
        $this->entity->setPassword('password');
    }
}
