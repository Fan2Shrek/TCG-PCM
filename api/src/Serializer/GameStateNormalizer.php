<?php

declare(strict_types=1);

namespace App\Serializer;

use App\Game\State\GameState;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class GameStateNormalizer implements NormalizerInterface
{
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof GameState;
    }

    public function normalize(mixed $data, ?string $format = null, array $context = []): array
    {
        if (!$data instanceof GameState) {
            throw new \InvalidArgumentException('Data must be an instance of GameState');
        }

        return [
            'player1' => $data->player1,
            'player2' => $data->player2,
            'lastEventId' => $data->lastEventid,
            'currentPlayer' => $data->lastEventid,
        ];
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            GameState::class => true,
        ];
    }
}
