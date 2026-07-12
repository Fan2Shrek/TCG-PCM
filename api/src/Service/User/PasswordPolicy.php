<?php

declare(strict_types=1);

namespace App\Service\User;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class PasswordPolicy
{
    private const int MIN_LENGTH = 12;

    public function assertValid(#[\SensitiveParameter] string $password): void
    {
        if (mb_strlen($password) < self::MIN_LENGTH) {
            throw HttpException::fromStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY, 'Password must be at least 12 characters.');
        }

        if (!preg_match('/[a-zA-Z]/', $password)) {
            throw HttpException::fromStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY, 'Password must contain at least one letter.');
        }

        if (!preg_match('/[0-9]/', $password)) {
            throw HttpException::fromStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY, 'Password must contain at least one digit.');
        }

        if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
            throw HttpException::fromStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY, 'Password must contain at least one symbol.');
        }
    }
}
