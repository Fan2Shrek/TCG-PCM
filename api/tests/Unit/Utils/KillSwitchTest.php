<?php

declare(strict_types=1);

namespace App\Tests\Unit\Utils;

use App\Utils\KillSwitch;
use PHPUnit\Framework\TestCase;

final class KillSwitchTest extends TestCase
{
    private const FEATURES_FILE = __DIR__.'/resources/features.php';

    public function testKillSwitchTrue(): void
    {
        $killSwitch = new KillSwitch(self::FEATURES_FILE);

        self::assertTrue($killSwitch->isEnable('enable'));
    }

    public function testKillSwitchFalse(): void
    {
        $killSwitch = new KillSwitch(self::FEATURES_FILE);

        self::assertFalse($killSwitch->isEnable('disable'));
    }

    public function testKillSwitchNotExisting(): void
    {
        self::expectException(\InvalidArgumentException::class);
        self::expectExceptionMessage('Feature "not_existing" does not exist.');

        $killSwitch = new KillSwitch(self::FEATURES_FILE);

        self::assertFalse($killSwitch->isEnable('not_existing'));
    }
}
