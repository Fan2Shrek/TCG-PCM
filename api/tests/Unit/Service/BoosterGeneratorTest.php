<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Enum\CardRarityEnum;
use App\Enum\CardSetEnum;
use App\Game\AbstractCard;
use App\Game\GameContext;
use App\Service\BoosterGenerator;
use App\Tests\Resources\MockCardRegistry;
use PHPUnit\Framework\TestCase;

final class BoosterGeneratorTest extends TestCase
{
    public function testGenerateBooster(): void
    {
        $BoosterGenerator = new TestableBoosterGenerator();

        $booster = $BoosterGenerator->generateBooster();

        self::assertCount(1, $booster->getCards());
    }

    public function testRarity(): void
    {
        $BoosterGenerator = new class extends TestableBoosterGenerator {
            protected const RARITY_PROBABILITIES = [
                CardRarityEnum::LEGENDARY->value => 1,
                CardRarityEnum::COMMON->value => 0,
            ];
        };

        $booster = $BoosterGenerator->generateBooster();

        self::assertInstanceOf(LegendaryCardStub::class, $booster->getCards()[0]);
    }

    public function testSet(): void
    {
        $BoosterGenerator = new class extends TestableBoosterGenerator {
            protected const BOOSTER_SIZE = 1;
        };

        $booster = $BoosterGenerator->generateBooster(CardSetEnum::BTD6);

        self::assertCount(1, $booster->getCards());
    }

    public function testSize(): void
    {
        $BoosterGenerator = new class extends TestableBoosterGenerator {
            protected const BOOSTER_SIZE = 2;
        };

        $booster = $BoosterGenerator->generateBooster();

        $this->assertCount(2, $booster->getCards());
    }
}

class TestableBoosterGenerator extends BoosterGenerator
{
    protected const BOOSTER_SIZE = 1;

    protected const RARITY_PROBABILITIES = [
        CardRarityEnum::LEGENDARY->value => 0.5,
        CardRarityEnum::COMMON->value => 0.5,
    ];

    public function __construct()
    {
        parent::__construct(new MockCardRegistry(
            $this->getCardsList(),
        ));
    }

    public function getCardsList(): array
    {
        return [
            CommonCardStub::class => CommonCardStub::class,
            LegendaryCardStub::class => LegendaryCardStub::class,
        ];
    }
}

class CommonCardStub extends AbstractCard
{
    public static CardSetEnum $serie = CardSetEnum::BTD6;
    public static CardRarityEnum $rarity = CardRarityEnum::COMMON;

    public function getId(): string
    {
        return '';
    }

    public function getName(): string
    {
        return 'Common Card Stub';
    }

    public function getDescription(): string
    {
        return 'A stub for common card testing.';
    }

    public function play(GameContext $context): void
    {
        // No-op for testing
    }
}

class LegendaryCardStub extends AbstractCard
{
    public static CardRarityEnum $rarity = CardRarityEnum::LEGENDARY;
    public static CardSetEnum $serie = CardSetEnum::TBOI;

    public function getId(): string
    {
        return '';
    }

    public function getName(): string
    {
        return 'Legendary Card Stub';
    }

    public function getDescription(): string
    {
        return 'A stub for legendary card testing.';
    }

    public function play(GameContext $context): void
    {
        // No-op for testing
    }
}
