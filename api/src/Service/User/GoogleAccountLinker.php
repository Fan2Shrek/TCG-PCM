<?php

declare(strict_types=1);

namespace App\Service\User;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class GoogleAccountLinker
{
    public function __construct(
        private UserRepository $userRepository,
        private UserRegistrar $userRegistrar,
        private EntityManagerInterface $em,
    ) {}

    public function findOrCreate(string $googleId, string $email, bool $emailVerified, string $name): User
    {
        $existingByGoogleId = $this->userRepository->findOneBy(['googleId' => $googleId]);
        if (null !== $existingByGoogleId) {
            return $existingByGoogleId;
        }

        $existingByEmail = $this->userRepository->findOneBy(['email' => $email]);
        if (null !== $existingByEmail) {
            if (!$emailVerified) {
                throw HttpException::fromStatusCode(Response::HTTP_CONFLICT, 'An account with this email already exists.');
            }

            $existingByEmail->setGoogleId($googleId);
            $this->em->flush();

            return $existingByEmail;
        }

        return $this->userRegistrar->registerViaGoogle($googleId, $email, $name);
    }
}
