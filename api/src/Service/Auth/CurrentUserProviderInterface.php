<?php

declare(strict_types=1);

namespace App\Service\Auth;

use App\Entity\User;

interface CurrentUserProviderInterface
{
    public function getCurrentUser(): User;
}
