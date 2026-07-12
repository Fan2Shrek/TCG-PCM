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
    private const LIST_URI = '/api/boosters/cards';

    public function testGetObtainableBoosterCards(): void
    {
        $user = ThereIs::anUser()->build();
        $this->client->loginUser($user);

        $this->get(self::LIST_URI);

        $response = $this->client->getResponse();
        self::assertResponseIsSuccessful();

        $data = json_decode($response->getContent() ?? '', true);
        self::assertArrayHasKey('cards', $data);
        self::assertArrayHasKey('total', $data);
        self::assertArrayHasKey('offset', $data);
        self::assertArrayHasKey('step', $data);
        self::assertIsArray($data['cards']);
        self::assertIsInt($data['total']);
        self::assertSame(0, $data['offset']);
        self::assertNull($data['step']);

        if ([] !== $data['cards']) {
            $card = $data['cards'][0];
            self::assertArrayHasKey('name', $card);
            self::assertArrayHasKey('description', $card);
            self::assertArrayHasKey('rarity', $card);
            self::assertArrayHasKey('type', $card);
            self::assertTrue(
                array_key_exists('cost', $card) || array_key_exists('hp', $card) || array_key_exists('attack', $card),
                'Listed card should expose at least one visible stat field (cost, hp, or attack).',
            );
        }
    }

    public function testGetObtainableBoosterCardsWithPagination(): void
    {
        $user = ThereIs::anUser()->build();
        $this->client->loginUser($user);

        $this->get(self::LIST_URI.'?offset=1&step=2&type=default');

        $response = $this->client->getResponse();
        self::assertResponseIsSuccessful();

        $data = json_decode($response->getContent() ?? '', true);
        self::assertSame(1, $data['offset']);
        self::assertSame(2, $data['step']);
        self::assertArrayHasKey('total', $data);
        self::assertArrayHasKey('cards', $data);

        $expectedCount = max(0, min(2, $data['total'] - 1));
        self::assertCount($expectedCount, $data['cards']);
    }

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
                'Opened card should expose at least one visible stat field (cost, hp, or attack).',
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
