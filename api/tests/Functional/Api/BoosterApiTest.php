<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api;

use App\Entity\Inventory\Inventory;
use App\Entity\UserWallet;
use App\Tests\Functional\FunctionalTestCase;
use App\Tests\Resources\Fixtures\ThereIs;

final class BoosterApiTest extends FunctionalTestCase
{
    private const URI = '/api/boosters/open';

    public function testSuccessfulBoosterOpen(): void
    {
        $user = ThereIs::anUser()->build();
        $this->client->loginUser($user);
        $this->post(self::URI, [
            'type' => 'default',
        ]);

        $response = $this->client->getResponse();
        self::assertResponseIsSuccessful();

        $data = json_decode($response->getContent() ?? '', true);
        self::assertArrayHasKey('cards', $data);
        self::assertCount(5, $data['cards']);

        foreach ($data['cards'] as $card) {
            self::assertArrayHasKey('name', $card);
            self::assertArrayHasKey('description', $card);
            self::assertArrayHasKey('rarity', $card);

            self::assertTrue(
                array_key_exists('cost', $card) || array_key_exists('hp', $card) || array_key_exists('attack', $card),
                'Opened card should expose at least one visible stat field (cost, hp, or attack).'
            );
        }
    }

    public function testOpenBoosterAddedToInventory(): void
    {
        $user = ThereIs::anUser()->build();
        $this->client->loginUser($user);
        $this->post(self::URI, [
            'type' => 'default',
        ]);

        $this->client->getResponse();

        $inventory = $this->getEM()->getRepository(Inventory::class)->find($user->getId());

        self::assertNotCount(0, $inventory->getCards());
    }

    public function testOpenBoosterWithoutToken(): void
    {
        $user = ThereIs::anUser()->withBoosterTokens(0)->build();
        $this->client->loginUser($user);
        $this->post(self::URI, [
            'type' => 'default',
        ]);

        $this->client->getResponse();

        self::assertResponseStatusCodeSame(400);
    }

    public function testOpenBoosterRemoveToken(): void
    {
        $user = ThereIs::anUser()->withBoosterTokens(1)->build();
        $this->client->loginUser($user);
        $this->post(self::URI, [
            'type' => 'default',
        ]);

        $this->client->getResponse();

        $wallet = $this->getEM()->getRepository(UserWallet::class)->find($user->getId());

        self::assertSame(0, $wallet->getBoosterTokens());
    }
}
