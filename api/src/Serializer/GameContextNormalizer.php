<?php

declare(strict_types=1);

namespace App\Serializer;

use App\Game\GameContext;
use App\Game\Player;
use ArrayObject;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class GameContextNormalizer implements NormalizerInterface, DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    /**
     * @param GameContext $data
     */
    public function normalize(
        mixed $data,
        ?string $format = null,
        array $context = [],
    ): array|string|int|float|bool|ArrayObject|null {
        return [
            'players' => array_map(
                fn (Player $player) => ['name' => $player->name, 'healthPoints' => $player->healthPoints],
                $data->getPlayers(),
            ),
            'currentTurn' => $data->getCurrentPlayer()->name,
        ];
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof GameContext;
    }

    /**
     * @param array{
     *    players: array<array{name: string, healthPoints: int}>,
     *    currentTurn: string,
     * } $data
     */
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        /*
         * @var array{0: Player, 1: Player} $players
         */
        $players = $this->denormalizer->denormalize($data['players'], Player::class.'[]', $format, $context);

        $gameContext = new GameContext($players[0], $players[1]);

        if ($data['currentTurn'] === $players[1]->name) {
            $gameContext->nextPlayer();
        }

        return $gameContext;
    }

    public function supportsDenormalization(
        mixed $data,
        string $type,
        ?string $format = null,
        array $context = [],
    ): bool {
        return $type === GameContext::class;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [GameContext::class => true];
    }
}
