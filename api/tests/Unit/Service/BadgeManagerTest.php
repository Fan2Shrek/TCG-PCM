<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Badge\BadgeEventInterface;
use App\Badge\Handler\BadgeHandlerInterface;
use App\Entity\User;
use App\Entity\UserBadge;
use App\Enum\BadgeEnum;
use App\Repository\UserBadgeRepository;
use App\Service\BadgeManager;
use PHPUnit\Framework\TestCase;

final class BadgeManagerTest extends TestCase
{
    public function testHandle(): void
    {
        $handler = new SpyBadgeHandler();
        $badgeManager = $this->getBadgeManager([$handler]);

        $badgeManager->handleFromEvent(new DummyEvent(new User('', '')));

        $this->assertTrue($handler->handleCalled);
    }

    public function testHandleWithWrongEvent(): void
    {
        $this->expectException(\RuntimeException::class);

        $handler = new SpyBadgeHandler();
        $badgeManager = $this->getBadgeManager([$handler]);

        $badgeManager->handleFromEvent(new class(new User('', '')) implements BadgeEventInterface {
            public function __construct(private readonly User $user) {}

            public static function getBadgeKey(): BadgeEnum
            {
                return BadgeEnum::GamePlayed;
            }

            public function getUser(): User
            {
                return $this->user;
            }
        });
    }

    private function getBadgeManager(array $handlers): BadgeManager
    {
        $repository = $this->createStub(UserBadgeRepository::class);

        return new BadgeManager($handlers, $repository);
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

    public function getThresholds(): array
    {
        return [];
    }
}

class DummyEvent implements BadgeEventInterface
{
    public function __construct(private readonly User $user) {}

    public static function getBadgeKey(): BadgeEnum
    {
        return BadgeEnum::OpenedBooster;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
