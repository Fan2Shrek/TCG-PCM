<?php

declare(strict_types=1);

namespace App\Tests\Unit\Game\Card;

use App\Enum\CardEffectEnum;
use App\Enum\GameEventTypeEnum;
use App\Game\Card\HackedZoneCard;
use App\Game\GameContext;
use App\Game\Player;
use App\Game\State\GameEvent;
use App\Game\State\GameState;
use App\Game\State\PlayArea;
use App\Game\State\PlayerState;

final class HackedZoneCardTest extends CardTestCase
{
    public function getCardFQCN(): string
    {
        return HackedZoneCard::class;
    }

    public function testOnCardPlace(): void
    {
        $gameState = new GameState(
            new PlayerState(
                new Player('player1', 'Player 1'),
                0,
                0,
                'characterCardId1',
                [
                    'card1',
                    'card2',
                ],
                [],
                new PlayArea(['card3']),
            ),
            new PlayerState(
                new Player('player2', 'Player 2'),
                0,
                0,
                'characterCardId2',
                [
                    'card4',
                    'card5',
                ],
                [],
                new PlayArea(['card6']),
            ),
            null,
            null,
            [],
        );
        $gameContext = new GameContext($gameState, 'player1');
        $card = $this->getCard();

        $card->onCardPlace($gameContext);

        $events = $gameContext->flushEvents();

        self::assertCount(16, $events);
        $cardIds = array_map(fn ($event) => $event->data['cardId'], array_filter($events, fn ($a) => GameEventTypeEnum::EFFECT_ADDED === $a->type));
        self::assertCount(8, array_unique($cardIds));
        self::assertEqualsCanonicalizing([
            'card1',
            'card2',
            'card3',
            'card4',
            'card5',
            'card6',
            'characterCardId1',
            'characterCardId2',
        ], $cardIds);
    }

    public function testOnCardDrawn()
    {
        $card = $this->getCard();
        $gameContext = $this->createGameContext();
        $this->ensureNextDiceRolls(100);

        $card->onCardDrawn('player1', $gameContext);

        $events = $gameContext->flushEvents();

        self::assertCount(2, $events);
        self::assertSame(GameEventTypeEnum::DICE_ROLLED, $events[0]->type);
        self::assertEquals(new GameEvent(
            0,
            GameEventTypeEnum::EFFECT_ADDED,
            GameEvent::GAME_EVENT,
            [
                'effect' => CardEffectEnum::HACKED->value,
                'cardId' => 'player1',
                'effectValues' => [
                    'value' => 1,
                ],
            ],
        ), $events[1]);
    }
}
