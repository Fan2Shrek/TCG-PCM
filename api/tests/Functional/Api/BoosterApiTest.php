<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api;

use App\Entity\Inventory\Inventory;
use App\Tests\Functional\FunctionalTestCase;
use App\Tests\Resources\Fixtures\ThereIs;

final class BoosterApiTest extends FunctionalTestCase
{
    private const URI = '/api/boosters/open';

    public function testSuccessfulBoosterOpen(): void
    {
        $user = ThereIs::anUser()->build();
        $this->client->loginUser($user);
        $this->post(self::URI);

        $response = $this->client->getResponse();
        self::assertResponseIsSuccessful();

        $data = json_decode($response->getContent() ?? '', true);
        self::assertArrayHasKey('cards', $data);
        self::assertCount(5, $data['cards']);
    }

    public function testOpenBoosterAddedToInventory(): void
    {
        $user = ThereIs::anUser()->build();
        $this->client->loginUser($user);
        $this->post(self::URI);

        $this->client->getResponse();

        $inventory = $this->getEM()->getRepository(Inventory::class)->find($user->getId());

        self::assertNotCount(0, $inventory->getCards());
    }
}
