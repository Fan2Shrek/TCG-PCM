<?php

declare(strict_types=1);

namespace App\Service\User;

use App\Entity\User;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordToken;

final class PasswordResetMailer
{
    public function __construct(
        private MailerInterface $mailer,
        private string $resetPasswordFrontUrl,
        private string $mailerFrom,
    ) {}

    public function send(User $user, #[\SensitiveParameter] ResetPasswordToken $token): void
    {
        $resetUrl = \sprintf('%s?token=%s', $this->resetPasswordFrontUrl, $token->getToken());
        $expiresInMinutes = (int) (($token->getExpiresAt()->getTimestamp() - time()) / 60);

        $email = new Email()
            ->from($this->mailerFrom)
            ->to($user->getEmail())
            ->subject('Réinitialisation de votre mot de passe')
            ->text(\sprintf(
                "Bonjour,\n\nUne demande de réinitialisation de mot de passe a été faite pour votre compte.\nCe lien est valable %d minutes : %s\n\nSi vous n'êtes pas à l'origine de cette demande, ignorez cet e-mail.",
                $expiresInMinutes,
                $resetUrl,
            ));

        $this->mailer->send($email);
    }
}
