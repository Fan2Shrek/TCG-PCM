<?php

declare(strict_types=1);

namespace App\Service\Auth;

use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;

final class SecurityCurrentUserProvider implements CurrentUserProviderInterface
{
    public function __construct(
        private Security $security
    ) {}

    public function getCurrentUser(): User
    {
        $user = $this->security->getUser();

        if (!$user instanceof User) {
            throw new \RuntimeException('No authenticated user found.');
        }

        return $user;
    }
}
