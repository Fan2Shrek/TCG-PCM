<?php

declare(strict_types=1);

namespace App\Tests\Unit\Game;

use App\Enum\CardEffectEnum;
use App\Game\GameUtils;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class GameUtilsTest extends TestCase
{
    #[DataProvider('formatDescriptionProvider')]
    public function testFormatDescription(string $expected, string $description, array $data): void
    {
        $formatted = GameUtils::formatDescription($description, $data);

        self::assertSame($expected, $formatted);
    }

    public static function formatDescriptionProvider(): iterable
    {
        yield [
            'expected' => 'Test <effect>Hacked</effect> with value <value>50</value>',
            'description' => 'Test {{effect}} with value {{value}}',
            'data' => [
                'effect' => CardEffectEnum::HACKED,
                'value' => 50,
            ],
        ];

        yield [
            'expected' => 'Test value <value>100</value>',
            'description' => 'Test value {{value}}',
            'data' => [
                'value' => 100,
            ],
        ];

        yield [
            'expected' => 'Test <value>1</value> and <value>2</value>',
            'description' => 'Test {{value1}} and {{value2}}',
            'data' => [
                'value1' => 1,
                'value2' => 2,
            ],
        ];
    }
}
