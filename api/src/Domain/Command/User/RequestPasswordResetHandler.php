<?php

declare(strict_types=1);

namespace App\Domain\Command\User;

use App\Repository\UserRepository;
use App\Service\User\PasswordResetMailer;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

#[AsMessageHandler]
final class RequestPasswordResetHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private ResetPasswordHelperInterface $resetPasswordHelper,
        private PasswordResetMailer $passwordResetMailer,
    ) {}

    public function __invoke(RequestPasswordResetCommand $command): void
    {
        $user = $this->userRepository->findOneBy(['email' => $command->email]);

        if (null === $user) {
            return;
        }

        try {
            $token = $this->resetPasswordHelper->generateResetToken($user);
        } catch (ResetPasswordExceptionInterface) {
            return;
        }

        $this->passwordResetMailer->send($user, $token);
    }
}
