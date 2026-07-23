<?php

namespace App\Tests\Unit\Game\Card\Monster;

use App\Tests\Unit\Game\Card\CardTestCase;

final class DeadGameXDCardTest extends CardTestCase
{
    public function getCardFQCN(): string
    {
        return DeadGameXD::class;
    }

    public function testCard()
    {
        self::markTestIncomplete(\sprintf('TODO: Implement %s method.', __METHOD__));
    }
}
