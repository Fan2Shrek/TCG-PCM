<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api;

use App\Service\User\UserGenerateBoosterTokens;
use App\Tests\Functional\FunctionalTestCase;
use App\Tests\Resources\Fixtures\ThereIs;

final class UserApiTest extends FunctionalTestCase
{
    private const LOGIN_URI = '/api/login_check';
    private const INVENTORY_URI = '/api/inventory';
    private const CURRENT_USER_URI = '/api/user';

    private UserGenerateBoosterTokens $userGenerateBoosterTokens;

    public function setUp(): void
    {
        parent::setUp();

        $this->userGenerateBoosterTokens = static::getContainer()->get(UserGenerateBoosterTokens::class);
    }

    public function testLogin()
    {
        $this->createUser('onMangeDesPatesCeSoir', 'jyPeutRienJsuisEtudiant', true);

        $response = $this->client->request('POST', static::LOGIN_URI, [
            'json' => [
                'username' => 'onMangeDesPatesCeSoir',
                'password' => 'jyPeutRienJsuisEtudiant',
            ],
        ]);

        self::assertResponseIsSuccessful();
        self::assertJsonHasKey('token', $response);
    }

    public function testLoginWithWrongCredentials()
    {
        $this->createUser('jaiFaimSVP', 'genreJeVeuxManger', true);

        $this->client->request('POST', static::LOGIN_URI, [
            'json' => [
                'username' => 'jaiFaimSVP',
                'password' => 'pasLeDroitDeManger',
            ],
        ]);

        self::assertResponseStatusCodeSame(401);
        self::assertJsonContains([
            'message' => 'Invalid credentials.',
        ]);
    }

    public function testGetInventory()
    {
        $user = ThereIs::anUser()->build();
        $this->client->loginUser($user);

        $this->get(static::INVENTORY_URI);

        self::assertResponseIsSuccessful();
    }

    public function testGetInventoryReturnsCards()
    {
        $inv = ThereIs::anInventory()->withCard('D6', 2)->withCard('Pierrot')->build();
        $user = ThereIs::anUser()->withInventory($inv)->build();
        $this->client->loginUser($user);

        $response = $this->get(static::INVENTORY_URI);
        $content = $response->toArray();

        self::assertCount(2, $content['cards']);
        self::assertSame('D6', $content['cards'][0]['card']['name']);
        self::assertSame(2, $content['cards'][0]['quantity']);
        self::assertSame('Pierrot', $content['cards'][1]['card']['name']);
        self::assertSame(1, $content['cards'][1]['quantity']);
    }

    public function testGetCurrentUser()
    {
        $user = ThereIs::anUser()->build();
        $this->client->loginUser($user);

        $response = $this->get(static::CURRENT_USER_URI);
        $content = $response->toArray();

        self::assertSame($user->getUsername(), $content['username']);
    }

    public function testGetCurrentUserTokens()
    {
        $user = ThereIs::anUser()->withBoosterTokens(100)->build();
        $this->client->loginUser($user);

        $response = $this->get(static::CURRENT_USER_URI);
        $content = $response->toArray();

        self::assertSame(100, $content['userWallet']['boosterTokens']);
    }

    public function testGenerateBoosterTokens()
    {
        $date = new \DateTimeImmutable();
        $twoDaysAgo = $date->modify('-2 days');
        $user = ThereIs::anUser()->withBoosterTokens(0)->withLastBoosterTokensAt($twoDaysAgo)->build();

        $this->userGenerateBoosterTokens->generate($user);

        self::assertSame(4, $user->getUserWallet()->getBoosterTokens());
    }

    public function testGenerateBoosterTokensSpareTimeKept()
    {
        $date = new \DateTimeImmutable();
        $daysAgo = $date->modify('-2 days -5 hours');
        $hoursAgo = $date->modify('-5 hours');

        $user = ThereIs::anUser()->withBoosterTokens(0)->withLastBoosterTokensAt($daysAgo)->build();

        $this->userGenerateBoosterTokens->generate($user);

        $delta = 1;
        self::assertEqualsWithDelta($hoursAgo->getTimestamp(), $user->getUserInfo()->getLastBoosterTokensAt()->getTimestamp(), $delta);
    }

    public function testGenerateBoosterTokensNotAboveCap()
    {
        $user = ThereIs::anUser()
            ->withBoosterTokens(3)
            ->withLastBoosterTokensAt(new \DateTimeImmutable('2023-01-01'))
            ->build();

        $this->userGenerateBoosterTokens->generate($user);

        self::assertSame(5, $user->getUserWallet()->getBoosterTokens());
    }
}
