<?php

declare(strict_types=1);

namespace App\Tests\Resources\Fixtures\Builder;

use App\Entity\User;

/**
 * @extends AbstractBuilder<User>
 */
final class UserBuilder extends AbstractBuilder
{
    protected function doBuild(): void
    {
        $this->entity = new User('user_'.spl_object_id($this));
        $this->entity->setPassword('password');
    }
}
