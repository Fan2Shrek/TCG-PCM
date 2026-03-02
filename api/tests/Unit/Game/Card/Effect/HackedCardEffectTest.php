<?php

declare(strict_types=1);

namespace App\Tests\Unit\Game\Card\Effect;

use App\Game\Card\Effect\HackedCardEffect;

final class HackedCardEffectTest extends CardEffectTestCase
{
    public function testHackEffect()
    {
        $card = $this->getCardWithEffect();

        self::assertEquals(2.0, $card->getValue(1));
    }

    protected function getEffect(): HackedCardEffect
    {
        return new HackedCardEffect([
            'value' => 2.0,
        ]);
    }
}
