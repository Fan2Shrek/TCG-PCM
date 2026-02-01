<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Enum\CardRarityEnum;
use App\Game\AbstractCard;
use App\Game\GameContext;
use App\Service\BoosterGenerator;
use PHPUnit\Framework\TestCase;

final class BoosterGeneratorTest extends TestCase
{
    public function testGenerateBooster(): void
    {
        $BoosterGenerator = new TestableBoosterGenerator();

        $booster = $BoosterGenerator->generateBooster();

        $this->assertCount(1, $booster->getCards());
    }

    public function testNoDuplicates(): void
    {
        $BoosterGenerator = new class extends TestableBoosterGenerator {
            protected const BOOSTER_SIZE = 2;
        };

        $booster = $BoosterGenerator->generateBooster();

        $this->assertCount(2, array_unique($booster->getCards(), SORT_REGULAR));
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

        $this->assertInstanceOf(LegendaryCardStub::class, $booster->getCards()[0]);
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
        parent::__construct('');
    }

    public function getCardsList(): array
    {
        return [
            CommonCardStub::class,
            LegendaryCardStub::class,
        ];
    }
}

class CommonCardStub extends AbstractCard
{
    public static CardRarityEnum $rarity = CardRarityEnum::COMMON;

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
