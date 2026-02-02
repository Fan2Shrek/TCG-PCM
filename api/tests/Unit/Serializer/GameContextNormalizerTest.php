<?php

declare(strict_types=1);

namespace App\Tests\Unit\Serializer;

use App\Game\GameContext;
use App\Game\Player;
use App\Serializer\GameContextNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

final class GameContextNormalizerTest extends TestCase
{
    public function testNormalization()
    {
        $gameContext = new GameContext(
            new Player('Alice', healthPoints: 30),
            new Player('Bob', healthPoints: 30),
        );
        $normalizer = $this->getNormalizer();

        $data = $normalizer->normalize($gameContext);

        self::assertSame([
            'players' => [
                ['name' => 'Alice', 'healthPoints' => 30],
                ['name' => 'Bob', 'healthPoints' => 30],
            ],
            'currentTurn' => 'Alice',
        ], $data);
    }

    public function testNormalizationWithPlayer2AsCurrentTurn()
    {
        $gameContext = new GameContext(
            new Player('Alice', healthPoints: 30),
            new Player('Bob', healthPoints: 30),
        );
        $gameContext->nextPlayer();
        $normalizer = $this->getNormalizer();

        $data = $normalizer->normalize($gameContext);

        self::assertSame([
            'players' => [
                ['name' => 'Alice', 'healthPoints' => 30],
                ['name' => 'Bob', 'healthPoints' => 30],
            ],
            'currentTurn' => 'Bob',
        ], $data);
    }

    public function testDenormalization()
    {
        $data = [
            'players' => [
                ['name' => 'Alice', 'healthPoints' => 30],
                ['name' => 'Bob', 'healthPoints' => 30],
            ],
            'currentTurn' => 'Alice',
        ];

        $denormalizer = $this->getNormalizer();

        $gameContext = $denormalizer->denormalize($data, GameContext::class);

        self::assertSame('Alice', $gameContext->getCurrentPlayer()->name);
    }

    public function testDenormalizationWithPlayer2AsCurrentTurn()
    {
        $data = [
            'players' => [
                ['name' => 'Alice', 'healthPoints' => 30],
                ['name' => 'Bob', 'healthPoints' => 30],
            ],
            'currentTurn' => 'Bob',
        ];

        $denormalizer = $this->getNormalizer();

        $gameContext = $denormalizer->denormalize($data, GameContext::class);

        self::assertSame('Bob', $gameContext->getCurrentPlayer()->name);
    }

    private function getNormalizer(): GameContextNormalizer
    {
        $gameContextNormalizer = new GameContextNormalizer();

        $serializer = new Serializer([new ObjectNormalizer(), new ArrayDenormalizer()]);
        $gameContextNormalizer->setDenormalizer($serializer);

        return $gameContextNormalizer;
    }
}
