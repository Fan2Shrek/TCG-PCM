<?php

declare(strict_types=1);

namespace App\Tests\Unit\Game\Card\Effect;

use App\Enum\CardEffectEnum;
use App\Enum\GameEventTypeEnum;
use App\Game\Card\CardState;
use App\Game\Card\Effect\AbstractCardEffect;
use App\Game\Card\Effect\TornedCardEffect;
use App\Game\GameContext;
use App\Game\Player;
use App\Game\State\GameState;
use App\Game\State\PlayArea;
use App\Game\State\PlayerState;

final class TornedCardEffectTest extends CardEffectTestCase
{
    public function testGetName()
    {
        self::assertSame(CardEffectEnum::TORNED, TornedCardEffect::getName());
    }

    public function testBeforeActionPreventsCardWhenRollIsAtOrBelowFailChance()
    {
        $card = $this->getCardWithEffect();
        $card->setState(new CardState('effect_test_card', $card->getId(), '1', []));
        $ctx = $this->buildContext(TornedCardEffect::FAIL_CHANCE);

        $card->beforeAction($ctx);
        $events = $ctx->flushEvents();

        // First event is the DICE_ROLLED event from rollDice(), second is the prevention.
        self::assertCount(2, $events);
        self::assertSame(GameEventTypeEnum::DICE_ROLLED, $events[0]->type);
        self::assertSame(GameEventTypeEnum::CARD_ACTION_PREVENTED, $events[1]->type);
        self::assertSame('effect_test_card', $events[1]->data['cardId']);
        self::assertSame(CardEffectEnum::TORNED->value, $events[1]->data['reason']);
    }

    public function testBeforeActionDoesNothingWhenRollIsAboveFailChance()
    {
        $card = $this->getCardWithEffect();
        $card->setState(new CardState('effect_test_card', $card->getId(), '1', []));
        $ctx = $this->buildContext(TornedCardEffect::FAIL_CHANCE + 1);

        $card->beforeAction($ctx);
        $events = $ctx->flushEvents();

        self::assertCount(1, $events);
        self::assertSame(GameEventTypeEnum::DICE_ROLLED, $events[0]->type);
    }

    protected function getEffect(): AbstractCardEffect
    {
        return TornedCardEffect::fromEffectState(new \App\Game\Card\Effect\EffectState(CardEffectEnum::TORNED));
    }

    private function buildContext(int $roll): GameContext
    {
        $player1State = new PlayerState(new Player('1', 'Player 1', 67), 30, 30, '', [], [], 0, new PlayArea());
        $player2State = new PlayerState(new Player('2', 'Player 2', 67), 30, 30, '', [], [], 0, new PlayArea());
        $state = new GameState($player1State, $player2State, 1, 0, '1');

        return new class($state, '1', $roll) extends GameContext {
            public function __construct(
                GameState $state,
                string $playerId,
                private readonly int $fixedRoll,
            ) {
                parent::__construct($state, $playerId);
            }

            public function rollDice(int $faces): int
            {
                $this->pushGameEvent(\App\Enum\GameEventTypeEnum::DICE_ROLLED, [
                    'faces' => $faces,
                    'result' => $this->fixedRoll,
                ]);

                return $this->fixedRoll;
            }
        };
    }
}
