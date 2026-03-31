<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\UserInfo;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class UserInfoFixtures extends AbstractFixtures implements DependentFixtureInterface
{
    public function __construct()
    {
        parent::__construct(UserInfo::class);
    }

    protected function getData(): iterable
    {
        yield [
            'user' => $this->getReference('User_1', User::class),
            'lastBoosterAt' => new \DateTimeImmutable('-1 day'),
        ];

        yield [
            'user' => $this->getReference('User_2', User::class),
            'lastBoosterAt' => new \DateTimeImmutable(),
        ];
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}
