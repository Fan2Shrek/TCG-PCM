<?php

declare(strict_types=1);

namespace App\Service\Doctrine\Type;

use App\Doctrine\Type\CardListType;
use App\Game\Card\CardState;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CardListTypeTest extends TestCase
{
    #[DataProvider('provideConvertion')]
    public function testConvertToPHP(array $expected, string $value): void
    {
        $type = new CardListType();
        $platform = $this->createStub(AbstractPlatform::class);

        $value = $type->convertToPHPValue($value, $platform);

        self::assertEquals($expected, $value);
    }

    #[DataProvider('provideConvertion')]
    public function testConvertToDatabase(array $value, string $expected): void
    {
        $type = new CardListType();
        $platform = $this->createStub(AbstractPlatform::class);

        $value = $type->convertToDatabaseValue($value, $platform);

        self::assertEquals($expected, $value);
    }

    public static function provideConvertion(): \Generator
    {
        yield 'Basic' => [
            [
                'card1' => new CardState('instance1', 'template1', 'owner1'),
                'card2' => new CardState('instance2', 'template2', 'owner2'),
            ],
            '{"card1":{"instanceId":"instance1","templateId":"template1","ownerId":"owner1","effects":[],"values":[]},"card2":{"instanceId":"instance2","templateId":"template2","ownerId":"owner2","effects":[],"values":[]}}',
        ];

        yield 'Empty' => [
            [],
            '[]',
        ];
    }
}
