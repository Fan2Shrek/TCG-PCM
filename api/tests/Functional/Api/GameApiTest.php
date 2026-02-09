<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api;

use App\Tests\Functional\FunctionalTestCase;
use App\Tests\Resources\Fixtures\ThereIs;
use App\Tests\Unit\Fixtures\DummyCard;

final class GameApiTest extends FunctionalTestCase
{
    public const string PLAY_URL = '/api/game/{id}/play';

    public function testPlayGame()
    {
        $gameState = ThereIs::aGame()->withOwner($this->currentUser)->build();

        $this->post($this->getUri(self::PLAY_URL, ['id' => $gameState->getId()]), [
            'actionId' => 'play_card',
            'payload' => [
                'cardId' => DummyCard::class,
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
}
