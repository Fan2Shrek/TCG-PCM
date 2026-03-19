<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Entity\UserBadge;
use App\Enum\BadgeEnum;
use App\Game\Badge\BadgeEventInterface;
use App\Game\Badge\Handler\BadgeHandlerInterface;
use App\Repository\UserBadgeRepository;
use App\Service\BadgeManager;
use App\Tests\Resources\DummyCurrentUserProvider;
use PHPUnit\Framework\TestCase;

final class BadgeManagerTest extends TestCase
{
    public function testHandle(): void
    {
        $handler = new SpyBadgeHandler();
        $badgeManager = $this->getBadgeManager([$handler]);

        $badgeManager->handleFromEvent(new DummyEvent());

        $this->assertTrue($handler->handleCalled);
    }

    public function testHandleWithWrongEvent(): void
    {
        $this->expectException(\RuntimeException::class);

        $handler = new SpyBadgeHandler();
        $badgeManager = $this->getBadgeManager([$handler]);

        $badgeManager->handleFromEvent(new class implements BadgeEventInterface {
            public static function geBadgeKey(): BadgeEnum
            {
                return BadgeEnum::GamePlayed;
            }
        });
    }

    private function getBadgeManager(array $handlers): BadgeManager
    {
        $repository = $this->createStub(UserBadgeRepository::class);

        return new BadgeManager($handlers, $repository, new DummyCurrentUserProvider());
    }
}

class SpyBadgeHandler implements BadgeHandlerInterface
{
    public bool $handleCalled = false;

    public static function getBadgeKey(): BadgeEnum
    {
        return BadgeEnum::OpenedBooster;
    }

    public function handle(BadgeEventInterface $event, UserBadge $userBadge): void
    {
        $this->handleCalled = true;
    }
}

class DummyEvent implements BadgeEventInterface
{
    public static function geBadgeKey(): BadgeEnum
    {
        return BadgeEnum::OpenedBooster;
    }
}
