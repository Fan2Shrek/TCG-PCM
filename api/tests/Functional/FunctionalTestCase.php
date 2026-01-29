<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Entity\User;
use App\Tests\Resources\Fixtures\ThereIs;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

abstract class FunctionalTestCase extends ApiTestCase
{
    use JsonAssertionTrait;

    protected static ?bool $alwaysBootKernel = false;
    protected static bool $requestsWithAuthentication = true;
    protected Client $client;
    protected ?User $currentUser = null;

    public function setup(): void
    {
        $this->client = self::createClient([], [
            'headers' => [
                'Accept-Language' => 'fr',
            ],
        ]);

        ThereIs::setContainer(static::getContainer());

        static::getContainer()->get(Connection::class)->beginTransaction();

        if (static::$requestsWithAuthentication) {
            $this->currentUser = $this->createUser();
            $this->client->loginUser($this->currentUser, 'api');
        }

        parent::setUp();
    }

    public function tearDown(): void
    {
        static::getContainer()->get(Connection::class)->rollBack();

        parent::tearDown();
    }

    protected static function getEM(): EntityManagerInterface
    {
        return static::getContainer()->get('doctrine.orm.default_entity_manager');
    }

    protected function createUser(?string $username = null, ?string $password = null): User
    {
        $user = new User($username ?? 'test');
        $user->setPassword(self::getContainer()->get('security.password_hasher')
            ->hashPassword($user, $password ?? 'password'))
        ;
        $this->getEm()->persist($user);
        $this->getEm()->flush();

        return $user;
    }

    protected function post(string $uri, array $json = []): ResponseInterface
    {
        return $this->client->request('POST', $uri, [
            'headers' => [
                'Content-Type' => 'application/json'
            ],
            'json' => $json,
        ]);
    }

    protected function getUri(string $uriTemplate, array $variables = []): string
    {
        $uri = $uriTemplate;
        foreach ($variables as $key => $value) {
            $uri = str_replace('{' . $key . '}', (string) $value, $uri);
        }

        return $uri;
    }
}
