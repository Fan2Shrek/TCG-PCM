<?php

declare(strict_types=1);

namespace App\Domain\Command\User;

use App\Service\Auth\CurrentUserProviderInterface;
use App\Service\User\PasswordPolicy;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsMessageHandler]
final class ChangePasswordHandler
{
    public function __construct(
        private CurrentUserProviderInterface $currentUserProvider,
        private PasswordPolicy $passwordPolicy,
        private UserPasswordHasherInterface $passwordHasher,
        private EntityManagerInterface $em,
    ) {}

    public function __invoke(ChangePasswordCommand $command): void
    {
        $user = $this->currentUserProvider->getCurrentUser();

        if (!$this->passwordHasher->isPasswordValid($user, $command->currentPassword)) {
            throw HttpException::fromStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY, 'Current password is incorrect.');
        }

        $this->passwordPolicy->assertValid($command->newPassword);

        $user->setPassword($this->passwordHasher->hashPassword($user, $command->newPassword));
        $this->em->flush();
    }
}
