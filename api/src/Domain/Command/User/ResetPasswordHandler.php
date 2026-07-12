<?php

declare(strict_types=1);

namespace App\Domain\Command\User;

use App\Entity\User;
use App\Service\User\PasswordPolicy;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

#[AsMessageHandler]
final class ResetPasswordHandler
{
    public function __construct(
        private ResetPasswordHelperInterface $resetPasswordHelper,
        private PasswordPolicy $passwordPolicy,
        private UserPasswordHasherInterface $passwordHasher,
        private EntityManagerInterface $em,
    ) {}

    public function __invoke(ResetPasswordCommand $command): void
    {
        try {
            $user = $this->resetPasswordHelper->validateTokenAndFetchUser($command->token);
        } catch (ResetPasswordExceptionInterface) {
            throw HttpException::fromStatusCode(Response::HTTP_BAD_REQUEST, 'This reset password link is invalid or has expired.');
        }

        \assert($user instanceof User, 'ResetPasswordHelper only supports App\Entity\User.');

        $this->passwordPolicy->assertValid($command->newPassword);

        $user->setPassword($this->passwordHasher->hashPassword($user, $command->newPassword));
        $this->em->flush();

        $this->resetPasswordHelper->removeResetRequest($command->token);
    }
}
