<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api;

use App\Tests\Functional\FunctionalTestCase;
use App\Tests\Resources\Fixtures\ThereIs;

final class GameApiTest extends FunctionalTestCase
{
    protected const string PLAY_URL = '/api/game/{id}/play';
    protected const GET_GAME_URI = '/api/game/{id}';

    public function testPlayGame()
    {
        $gameState = ThereIs::aGame()->withOwner($this->currentUser)->build();

        $this->post($this->getUri(self::PLAY_URL, ['id' => $gameState->getId()]), [
            'actionId' => 'play_card',
            'payload' => [
                'cardId' => '1',
            ],
        ]);

        self::assertResponseIsSuccessful();
    }

    public function testPlayInvalidAction(): void
    {
        $gameState = ThereIs::aGame()->withOwner($this->currentUser)->build();

        $this->post($this->getUri(self::PLAY_URL, ['id' => $gameState->getId()]), [
            'actionId' => 'invalid_action',
            'payload' => [],
        ]);

        self::assertResponseStatusCodeSame(400);
    }

    public function testPlayGameCardNotInHand(): void
    {
        $gameState = ThereIs::aGame()->withOwner($this->currentUser)->build();

        $this->post($this->getUri(self::PLAY_URL, ['id' => $gameState->getId()]), [
            'actionId' => 'play_card',
            'payload' => [
                'cardId' => 'no_card',
            ],
        ]);

        self::assertResponseStatusCodeSame(400);
    }

    public function testUnknownAction()
    {
        $gameState = ThereIs::aGame()->withOwner($this->currentUser)->build();

        $this->post($this->getUri(self::PLAY_URL, ['id' => $gameState->getId()]), [
            'actionId' => 'play_card_bla_bla_bla',
            'payload' => [
                'cardId' => 'no_card',
            ],
        ]);

        self::assertResponseStatusCodeSame(400);
    }

    public function testGetCurrentState()
    {
        $room = ThereIs::aGame()->build();

        $this->get($this->getUri(self::GET_GAME_URI, ['id' => (string) $room->getId()]));

        self::assertResponseStatusCodeSame(200);
    }

    public function testGetCurrentState404()
    {
        $this->get($this->getUri(self::GET_GAME_URI, ['id' => 'blablabla']));

        self::assertResponseStatusCodeSame(404);
    }

    public function testHiddenCard()
    {
        $room = ThereIs::aGame()->withOwner($this->currentUser)->build();

        $response = $this->get($this->getUri(self::GET_GAME_URI, ['id' => (string) $room->getId()]));

        $data = $response->toArray();

        self::assertArrayNotHasKey('cardtest', $data['cards']);
    }
}
