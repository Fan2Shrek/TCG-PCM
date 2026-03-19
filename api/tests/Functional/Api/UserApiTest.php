<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api;

use App\Tests\Functional\FunctionalTestCase;

final class UserApiTest extends FunctionalTestCase
{
    private const LOGIN_URI = '/api/login_check';

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
}
