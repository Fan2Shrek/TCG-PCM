<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\UserWallet;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class UserWalletFixtures extends AbstractFixtures implements DependentFixtureInterface
{
    public function __construct(
    ) {
        parent::__construct(UserWallet::class);
    }

    protected function getData(): iterable
    {
        yield [
            'user' => $this->getReference('User_1', User::class),
            'boosterTokens' => 2,
        ];

        yield [
            'user' => $this->getReference('User_2', User::class),
            'boosterTokens' => 0,
        ];
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}
