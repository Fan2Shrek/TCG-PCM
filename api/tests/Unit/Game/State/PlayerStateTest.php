<?php

declare(strict_types=1);

namespace App\Tests\Unit\Game\State;

use App\Game\Player;
use App\Game\State\PlayArea;
use App\Game\State\PlayerState;
use PHPUnit\Framework\TestCase;

final class PlayerStateTest extends TestCase
{
    public function testWithUpdatedHealth(): void
    {
        $playerState = new PlayerState(
            player: new Player('1', 'Player 1'),
            healthPoints: 30,
            maxHealthPoints: 30,
            characterCardId: '',
            hand: [],
            drawPile: [],
            playArea: new PlayArea(),
        );

        $updatedState = $playerState->withUpdatedHealth(20);

        self::assertSame(30, $playerState->healthPoints);
        self::assertSame(20, $updatedState->healthPoints);
    }

    public function testWithNewHandAndDeck(): void
    {
        $playerState = new PlayerState(
            player: new Player('1', 'Player 1'),
            healthPoints: 30,
            maxHealthPoints: 30,
            characterCardId: '',
            hand: ['Card A'],
            drawPile: ['Card B'],
            playArea: new PlayArea(),
        );

        $updatedState = $playerState->withNewHandAndDeck(['Card C'], ['Card D']);

        self::assertSame(['Card A'], $playerState->hand);
        self::assertSame(['Card B'], $playerState->drawPile);
        self::assertSame(['Card C'], $updatedState->hand);
        self::assertSame(['Card D'], $updatedState->drawPile);
    }

    public function testIsAliveReturnTrue(): void
    {
        $aliveState = new PlayerState(
            player: new Player('1', 'Player 1'),
            healthPoints: 10,
            maxHealthPoints: 30,
            characterCardId: '',
            hand: [],
            drawPile: [],
            playArea: new PlayArea(),
        );

        self::assertTrue($aliveState->isAlive());
    }

    public function testIsAliveReturnFalse(): void
    {
        $deadState = new PlayerState(
            player: new Player('1', 'Player 1'),
            healthPoints: 0,
            maxHealthPoints: 30,
            characterCardId: '',
            hand: [],
            drawPile: [],
            playArea: new PlayArea(),
        );

        self::assertFalse($deadState->isAlive());
    }

    public function testHasCardInHand(): void
    {
        $playerState = new PlayerState(
            player: new Player('1', 'Player 1'),
            healthPoints: 30,
            maxHealthPoints: 30,
            characterCardId: '',
            hand: ['Card A', 'Card B'],
            drawPile: [],
            playArea: new PlayArea(),
        );

        self::assertTrue($playerState->hasCardInHand('Card A'));
        self::assertTrue($playerState->hasCardInHand('Card B'));
        self::assertFalse($playerState->hasCardInHand('Card C'));
    }

    public function testRemoveCardFromHand()
    {
        $playerState = new PlayerState(
            player: new Player('1', 'Player 1'),
            healthPoints: 30,
            maxHealthPoints: 30,
            characterCardId: '',
            hand: ['Card A', 'Card B'],
            drawPile: [],
            playArea: new PlayArea(),
        );

        $updatedState = $playerState->removeCardFromHand('Card A');

        self::assertSame(['Card A', 'Card B'], $playerState->hand);
        self::assertSame(['Card B'], $updatedState->hand);
    }

    public function testRemoveCardFromHandWithCardNotInHand(): void
    {
        self::expectException(\BadMethodCallException::class);
        self::expectExceptionMessage('Card Card C not found in hand');

        $playerState = new PlayerState(
            player: new Player('1', 'Player 1'),
            healthPoints: 30,
            maxHealthPoints: 30,
            characterCardId: '',
            hand: ['Card A', 'Card B'],
            drawPile: [],
            playArea: new PlayArea(),
        );

        $playerState->removeCardFromHand('Card C');
    }
}
