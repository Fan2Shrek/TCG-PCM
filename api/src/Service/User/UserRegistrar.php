<?php

declare(strict_types=1);

namespace App\Service\User;

use App\Entity\Inventory\Inventory;
use App\Entity\User;
use App\Entity\UserInfo;
use App\Entity\UserWallet;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class UserRegistrar
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $passwordHasher,
        private UserRepository $userRepository,
    ) {}

    public function register(string $username, #[\SensitiveParameter] string $password): User
    {
        if (mb_strlen($username) < 3) {
            throw HttpException::fromStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY, 'Username must be at least 3 characters.');
        }

        if (mb_strlen($password) < 6) {
            throw HttpException::fromStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY, 'Password must be at least 6 characters.');
        }

        if (null !== $this->userRepository->findOneBy(['username' => $username])) {
            throw HttpException::fromStatusCode(Response::HTTP_CONFLICT, 'Username already taken.');
        }

        $user = new User($username);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));

        $this->em->persist($user);
        $this->em->flush();

        $this->em->persist(new UserWallet($user));
        $this->em->persist(new UserInfo($user));
        $this->em->persist(new Inventory($user));
        $this->em->flush();

        return $user;
    }
}
