<?php

declare(strict_types=1);

namespace App\Api\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Game\State\GameState;
use App\Repository\RoomRepository;
use App\Service\Auth\CurrentUserProviderInterface;
use App\Service\Game\GameStateConverter;
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
        private CurrentUserProviderInterface $currentUserProvider,
        private GameStateConverter $gameStateConverter,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object
    {
        if (!($room = $this->roomRepository->find($uriVariables['id'] ?? null))) {
            throw new NotFoundHttpException();
        }

        if (!($gameState = $this->gameStateRepository->get((string) $room->getId()))) {
            throw new NotFoundHttpException();
        }

        $user = $this->currentUserProvider->getCurrentUser();

        return $this->gameStateConverter->convertGameState($gameState, (string) $user->getId());
    }
}
