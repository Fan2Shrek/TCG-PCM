<?php

declare(strict_types=1);

namespace App\Api\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Game\State\GameState;
use App\Repository\RoomRepository;
use App\Service\Game\State\GameStateRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @implements ProviderInterface<GameState>
 */
final class GameProvider implements ProviderInterface
{
    public function __construct(
        private RoomRepository $roomRepository,
        private GameStateRepositoryInterface $gameStateRepository,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object
    {
        if (!($room = $this->roomRepository->find($uriVariables['id'] ?? null))) {
            throw new NotFoundHttpException();
        }

        if (!($gameState = $this->gameStateRepository->get($room))) {
            throw new NotFoundHttpException();
        }

        // @todo Anonymize game state service
        return $gameState;
    }
}
