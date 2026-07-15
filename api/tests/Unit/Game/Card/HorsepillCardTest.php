<?php

declare(strict_types=1);

namespace App\Tests\Unit\Game\Card;

use App\Enum\GameEventTypeEnum;
use App\Game\Card\CardState;
use App\Game\Card\HorsepillCard;
use App\Game\State\GameState;
use App\Game\State\PlayArea;

/**
 * HorsepillCard picks one of several effects via array_rand(), which is not
 * routed through the (mockable) GameContext randomizer. These tests therefore
 * assert the structural invariants that must hold regardless of which branch
 * is picked, exercised over many runs so every branch gets a chance to fire.
 */
final class HorsepillCardTest extends CardTestCase
{
    protected function getCardFQCN(): string
    {
        return HorsepillCard::class;
    }

    private function buildState(): GameState
    {
        $player1 = $this
            ->createPlayerState('1')
            ->withPlayArea(new PlayArea(['passive1'], ['monster1']))
            ->withNewHandAndDeck(['test_card', 'ownerHandCard'], []);
        $player2 = $this->createPlayerState('2')->withNewHandAndDeck(['oppHandCard'], []);

        return new GameState($player1, $player2, 1, 0, '1', [
            'test_card' => new CardState('test_card', 'Horsepill', '1', []),
            'ownerHandCard' => new CardState('ownerHandCard', 'SomeCard', '1', []),
            'oppHandCard' => new CardState('oppHandCard', 'SomeCard', '2', []),
        ]);
    }

    public function testPlayProducesAValidOutcomeForEveryPossibleEffect(): void
    {
        $card = $this->getCard();
        $state = $this->buildState();

        for ($i = 0; $i < 60; $i++) {
            $ctx = $this->createGameContext($state);
            $card->play($ctx);
            $events = array_values($ctx->flushEvents());

            self::assertNotSame([], $events, 'Horsepill must always push at least one event');

            $last = $events[\count($events) - 1];

            switch (true) {
                case 1 === \count($events) && GameEventTypeEnum::DAMAGE === $last->type:
                    self::assertSame('1', $last->data['targetId']);
                    self::assertSame(50, $last->data['damage']);
                    break;

                case 1 === \count($events) && GameEventTypeEnum::HEAL === $last->type:
                    self::assertSame('1', $last->data['targetId']);
                    self::assertSame(50, $last->data['amount']);
                    break;

                case 1 === \count($events) && GameEventTypeEnum::CARD_DISCARDED === $last->type:
                    self::assertContains($last->data['cardId'], ['ownerHandCard', 'oppHandCard']);
                    break;

                case 2 === \count($events) && GameEventTypeEnum::CARD_DISCARDED === $last->type:
                    self::assertSame(GameEventTypeEnum::CARD_RUNTIME_VALUE, $events[0]->type);
                    self::assertContains($last->data['cardId'], ['passive1', 'monster1']);
                    break;

                case 2 === \count($events) && GameEventTypeEnum::UPDATE_CARD_STATE === $last->type:
                    self::assertSame(GameEventTypeEnum::CARD_RUNTIME_VALUE, $events[0]->type);
                    self::assertSame('monster1', $last->data['cardId']);
                    self::assertContains($last->data['stateToUpdate']['forcedAttack'], [0, 99]);
                    break;

                default:
                    self::fail(\sprintf('Unexpected event sequence: %s', implode(', ', array_map(static fn($e) => $e->type->value, $events))));
            }
        }
    }
}
