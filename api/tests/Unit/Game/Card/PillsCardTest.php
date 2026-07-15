<?php

declare(strict_types=1);

namespace App\Tests\Unit\Game\Card;

use App\Enum\GameEventTypeEnum;
use App\Game\Card\CardState;
use App\Game\Card\PillsCard;
use App\Game\State\GameState;
use App\Game\State\PlayArea;

/**
 * PillsCard picks one of several effects via array_rand(), which is not
 * routed through the (mockable) GameContext randomizer. These tests assert
 * the structural invariants that must hold regardless of which branch is
 * picked, exercised over many runs so every branch gets a chance to fire.
 */
final class PillsCardTest extends CardTestCase
{
    protected function getCardFQCN(): string
    {
        return PillsCard::class;
    }

    public function testPlayWithoutMonstersOnlyAffectsOwner(): void
    {
        $card = $this->getCard();
        $ctx = $this->createGameContext();

        for ($i = 0; $i < 20; $i++) {
            $card->play($ctx);
            $events = $ctx->flushEvents();

            self::assertCount(1, $events);
            self::assertContains($events[0]->type, [GameEventTypeEnum::DAMAGE, GameEventTypeEnum::HEAL]);
            self::assertSame('1', $events[0]->data['targetId']);
        }
    }

    public function testPlayWithMonsterProducesAValidOutcomeForEveryPossibleEffect(): void
    {
        $card = $this->getCard();

        $player1 = $this->createPlayerState('1')->withPlayArea(new PlayArea([], ['monster1']));
        $player2 = $this->createPlayerState('2');

        $state = new GameState($player1, $player2, 1, 0, '1', [
            'test_card' => new CardState('test_card', 'Pills', '1', []),
            'monster1' => new CardState('monster1', 'SomeMonster', '1', []),
        ]);

        for ($i = 0; $i < 60; $i++) {
            $ctx = $this->createGameContext($state);
            $card->play($ctx);
            $events = array_values($ctx->flushEvents());

            self::assertNotSame([], $events, 'Pills must always push at least one event');

            switch (\count($events)) {
                case 1:
                    // Only self_damage / self_heal skip selectRandomCardIn().
                    self::assertContains($events[0]->type, [GameEventTypeEnum::DAMAGE, GameEventTypeEnum::HEAL]);
                    self::assertSame('1', $events[0]->data['targetId']);
                    if (GameEventTypeEnum::DAMAGE === $events[0]->type) {
                        self::assertSame(5, $events[0]->data['damage']);
                    } else {
                        self::assertSame(10, $events[0]->data['amount']);
                    }
                    break;

                case 2:
                    // All monster-targeting effects go through selectRandomCardIn() first.
                    self::assertSame(GameEventTypeEnum::CARD_RUNTIME_VALUE, $events[0]->type);
                    $second = $events[1];
                    self::assertContains(
                        $second->type,
                        [
                            GameEventTypeEnum::HEAL,
                            GameEventTypeEnum::DAMAGE,
                            GameEventTypeEnum::UPDATE_CARD_STATE,
                        ],
                    );

                    switch ($second->type) {
                        case GameEventTypeEnum::HEAL:
                            self::assertSame(['targetId' => 'monster1', 'amount' => 5], $second->data);
                            break;
                        case GameEventTypeEnum::DAMAGE:
                            self::assertSame(['targetId' => 'monster1', 'damage' => 3], $second->data);
                            break;
                        case GameEventTypeEnum::UPDATE_CARD_STATE:
                            self::assertSame('monster1', $second->data['cardId']);
                            self::assertContains($second->data['stateToUpdate']['bonusAttack'], [5, -3]);
                            break;
                        default:
                            self::fail('Unexpected event type');
                    }
                    break;

                default:
                    self::fail(\sprintf('Unexpected event sequence: %s', implode(', ', array_map(static fn($e) => $e->type->value, $events))));
            }
        }
    }
}
