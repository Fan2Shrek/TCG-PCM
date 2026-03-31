<?php

declare(strict_types=1);

namespace App\Tests\Unit\Game\Badge;

use App\Badge\BadgeEventInterface;
use App\Badge\Handler\AbstractBadgeWithLevelHandler;
use App\Entity\User;
use App\Entity\UserBadge;
use App\Enum\BadgeEnum;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class BadgeHandlerWithLevelTest extends TestCase
{
    public function testHandle()
    {
        $handler = new DummyLevelHandler();
        $userBadge = new UserBadge(new User('', ''), BadgeEnum::OpenedBooster);
        $userBadge->setScore(5);
        $event = new class implements BadgeEventInterface {
            public static function getBadgeKey(): BadgeEnum
            {
                return BadgeEnum::OpenedBooster;
            }
        };

        $handler->handle($event, $userBadge);

        $this->assertSame(3, $userBadge->getLevel());
        $this->assertSame(6, $userBadge->getScore());
    }

    #[DataProvider('provideGetLevelForScore')]
    public function testGetLevelForScore(int $expectedLevel, int $score)
    {
        $handler = new DummyLevelHandler();

        $this->assertSame($expectedLevel, $handler->getLevelForScore($score));
    }

    public static function provideGetLevelForScore(): array
    {
        return [
            [0, 0],
            [0, 1],
            [1, 2],
            [1, 3],
            [2, 4],
            [2, 5],
            [3, 6],
            [3, 10],
        ];
    }
}

class DummyLevelHandler extends AbstractBadgeWithLevelHandler
{
    protected function getLevels(): array
    {
        return [
            0 => 0,
            1 => 2,
            2 => 4,
            3 => 6,
        ];
    }

    public static function getBadgeKey(): BadgeEnum
    {
        return BadgeEnum::OpenedBooster;
    }

    public function getLevelForScore(int $score): int
    {
        return parent::getLevelForScore($score);
    }
}
