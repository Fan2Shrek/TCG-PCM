<?php

declare(strict_types=1);

namespace App\Tests\Unit\Game\Card\Effect;

use App\Enum\CardEffectEnum;
use App\Game\Card\Effect\AbstractCardEffect;
use App\Game\Card\Effect\PowerBoostEffect;
use PHPUnit\Framework\Attributes\DataProvider;

final class PowerBoostEffectTest extends CardEffectTestCase
{
    public function testGetName()
    {
        self::assertSame(CardEffectEnum::POWER_BOOST, PowerBoostEffect::getName());
    }

    #[DataProvider('provideValues')]
    public function testGetValue(float $expected, int|float $rawValue)
    {
        $effect = new PowerBoostEffect(['value' => $rawValue]);

        self::assertSame($expected, $effect->getValue());
    }

    public static function provideValues(): \Generator
    {
        yield 'int value' => [200.0, 200];
        yield 'float value' => [1.5, 1.5];
        yield 'negative value' => [-10.0, -10];
    }

    public function testMissingValueThrows()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing value key');

        new PowerBoostEffect([]);
    }

    public function testZeroValueThrows()
    {
        // 0 is falsy, so the constructor's null-coalescing check rejects it too.
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing value key');

        new PowerBoostEffect(['value' => 0]);
    }

    public function testNonNumericValueThrows()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Value must be int or float');

        new PowerBoostEffect(['value' => 'not-a-number']);
    }

    public function testDefaultModifyValueIsUnchanged()
    {
        // PowerBoostEffect does not override modifyValue(); getValue() must be
        // read explicitly by callers (e.g. AbstractMonsterCard::getAttack()).
        $card = $this->getCardWithEffect();

        self::assertSame(10.0, $card->getValue(10));
    }

    protected function getEffect(): AbstractCardEffect
    {
        return new PowerBoostEffect(['value' => 200]);
    }
}
