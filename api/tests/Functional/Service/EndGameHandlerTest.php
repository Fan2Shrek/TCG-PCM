<?php

declare(strict_types=1);

namespace App\Tests\Functional\Service;

use App\Enum\RoomStatusEnum;
use App\Service\EndGameHandler;
use App\Tests\Functional\FunctionalTestCase;
use App\Tests\Resources\Fixtures\ThereIs;

final class EndGameHandlerTest extends FunctionalTestCase
{
    protected static bool $requestsWithAuthentication = false;

    public function testEndGameAwardsBoosterTokenToWinner(): void
    {
        $owner = ThereIs::anUser()->withBoosterTokens(0)->build();
        $opponent = ThereIs::anUser()->withBoosterTokens(0)->build();
        $room = ThereIs::aRoom()->withOwner($owner)->withOpponent($opponent)->build();

        $sut = static::getContainer()->get(EndGameHandler::class);
        $sut->endGame((string) $room->getId(), (string) $owner->getId());

        self::assertSame(RoomStatusEnum::FINISHED, $room->getStatus());
        self::assertSame((string) $owner->getId(), $room->getWinnerId());
        self::assertSame(1, $owner->getUserWallet()->getBoosterTokens());
        self::assertSame(0, $opponent->getUserWallet()->getBoosterTokens());
    }

    public function testEndGameDoesNotExceedBoosterTokenCap(): void
    {
        $owner = ThereIs::anUser()->withBoosterTokens(5)->build();
        $opponent = ThereIs::anUser()->withBoosterTokens(0)->build();
        $room = ThereIs::aRoom()->withOwner($owner)->withOpponent($opponent)->build();

        $sut = static::getContainer()->get(EndGameHandler::class);
        $sut->endGame((string) $room->getId(), (string) $owner->getId());

        self::assertSame(5, $owner->getUserWallet()->getBoosterTokens());
    }
}
