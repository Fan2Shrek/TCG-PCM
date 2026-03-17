<?php

declare(strict_types=1);

namespace App\Tests\Unit\Doctrine\Type;

use App\Doctrine\Type\PlayerStateType;
use App\Game\Player;
use App\Game\State\PlayArea;
use App\Game\State\PlayerState;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PlayerStateTypeTest extends TestCase
{
    #[DataProvider('provideConvertion')]
    public function testConvertToPHP(PlayerState $expected, string $value): void
    {
        $type = new PlayerStateType();
        $platform = $this->createStub(AbstractPlatform::class);

        $value = $type->convertToPHPValue($value, $platform);

        self::assertEquals($expected, $value);
    }

    #[DataProvider('provideConvertion')]
    public function testConvertToDatabase(PlayerState $value, string $expected): void
    {
        $type = new PlayerStateType();
        $platform = $this->createStub(AbstractPlatform::class);

        $value = $type->convertToDatabaseValue($value, $platform);

        self::assertEquals($expected, $value);
    }

    public static function provideConvertion(): \Generator
    {
        yield 'Basic' => [
            new PlayerState(
                new Player('player1', 'Player One'),
                10,
                10,
                'characterCardId',
                ['card1', 'card2'],
                ['card3' => 'template3', 'card4' => 'template4'],
                10,
                new PlayArea(),
            ),
            '{"player":{"id":"player1","name":"Player One"},"healthPoints":10,"maxHealthPoints":10,"characterCardId":"characterCardId","hand":["card1","card2"],"drawPile":{"card3":"template3","card4":"template4"},"coins":10,"playArea":{"passiveCards":[],"monsterCards":[]},"discardPile":[]}',
        ];
    }
}
