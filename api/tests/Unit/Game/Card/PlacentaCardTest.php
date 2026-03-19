<?php

declare(strict_types=1);

namespace App\Tests\Unit\Game\Card;

use App\Enum\GameEventTypeEnum;
use App\Game\Card\PlacentaCard;
use App\Game\GameContext;

final class PlacentaCardTest extends CardTestCase
{
    public function getCardFQCN(): string
    {
        return PlacentaCard::class;
    }

    public function testTurnStart(): void
    {
        $card = $this->getCard();
        $gameContext = $this->createGameContext();
        $player = $gameContext->getCurrentPlayerState();
        $player = $player->withUpdatedHealth(10);
        $gameContext = new GameContext($gameContext->state->withUpdatedPlayer($player), $gameContext->playerId);

        $card->onTurnStart($gameContext);
        $events = $gameContext->flushEvents();

        self::assertCount(1, $events);
        self::assertSame(GameEventTypeEnum::HEAL, $events[0]->type);
    }
}
