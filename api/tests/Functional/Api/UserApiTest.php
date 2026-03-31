<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api;

use App\Tests\Functional\FunctionalTestCase;
use App\Tests\Resources\Fixtures\ThereIs;

final class UserApiTest extends FunctionalTestCase
{
    private const LOGIN_URI = '/api/login_check';
    private const INVENTORY_URI = '/api/inventory';

    public function testLogin()
    {
        $this->createUser('onMangeDesPatesCeSoir', 'jyPeutRienJsuisEtudiant');

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
        $this->createUser('jaiFaimSVP', 'genreJeVeuxManger');

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
        $inv = ThereIs::anInventory()
            ->withCard('D6', 2)
            ->withCard('Pierrot')
            ->build();
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
}
