<?php

declare(strict_types=1);

namespace App\Tests\Unit\Game;

use App\Enum\CardEffectEnum;
use App\Game\GameUtils;
use App\Tests\Unit\Game\Card\GameUtilsContainerTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class GameUtilsTest extends TestCase
{
    use GameUtilsContainerTrait;

    protected function setUp(): void
    {
        parent::setUp();

        $translator = $this->createStub(TranslatorInterface::class);
        $translator->method('trans')->willReturnCallback(static fn(string $id): string => 'effects.Hacked.name' === $id ? 'Hacké' : $id);

        $this->setGameUtilsContainer(new class($translator) implements ContainerInterface {
            public function __construct(
                private TranslatorInterface $translator,
            ) {}

            public function get(string $id): mixed
            {
                return 'translator' === $id ? $this->translator : throw new \RuntimeException("Unexpected service \"{$id}\"");
            }

            public function has(string $id): bool
            {
                return 'translator' === $id;
            }
        });
    }

    protected function tearDown(): void
    {
        $this->restoreGameUtilsContainer();
        parent::tearDown();
    }

    #[DataProvider('formatDescriptionProvider')]
    public function testFormatDescription(string $expected, string $description, array $data): void
    {
        $formatted = GameUtils::formatDescription($description, $data);

        self::assertSame($expected, $formatted);
    }

    public static function formatDescriptionProvider(): iterable
    {
        yield [
            'expected' => 'Test <effect>Hacké</effect> with value <value>50</value>',
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
