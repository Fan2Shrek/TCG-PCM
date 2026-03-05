<?php

declare(strict_types=1);

namespace App\Tests\Resources;

use App\Entity\User;
use App\Service\Auth\CurrentUserProviderInterface;

final class DummyCurrentUserProvider implements CurrentUserProviderInterface
{
    public function getCurrentUser(): User
    {
        return new User('', '');
    }
}
