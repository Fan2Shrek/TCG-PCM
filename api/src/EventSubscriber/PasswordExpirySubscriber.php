<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class PasswordExpirySubscriber implements EventSubscriberInterface
{
    private const int PASSWORD_MAX_AGE_DAYS = 60;

    public static function getSubscribedEvents(): array
    {
        return [
            Events::AUTHENTICATION_SUCCESS => 'onAuthenticationSuccess',
        ];
    }

    public function onAuthenticationSuccess(AuthenticationSuccessEvent $event): void
    {
        $user = $event->getUser();

        if (!$user instanceof User) {
            return;
        }

        $days = $user->getPasswordChangedAt()->diff(new \DateTimeImmutable())->days;
        $ageInDays = false === $days ? 0 : $days;

        $event->setData([
            ...$event->getData(),
            'password_expired' => $ageInDays >= self::PASSWORD_MAX_AGE_DAYS,
        ]);
    }
}
