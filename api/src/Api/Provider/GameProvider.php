<?php

declare(strict_types=1);

namespace App\Api\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Game\State\GameState;
use App\Repository\RoomRepository;
use App\Service\Auth\CurrentUserProviderInterface;
use App\Service\Game\GameStateConverter;
use App\Service\Game\State\GameStateProvider;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Mercure\HubInterface;

/**
 * @implements ProviderInterface<GameState>
 */
final class GameProvider implements ProviderInterface
{
    public function __construct(
        private RoomRepository $roomRepository,
        private CurrentUserProviderInterface $currentUserProvider,
        private GameStateConverter $gameStateConverter,
        private GameStateProvider $gameStateProvider,
        private HubInterface $hub,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        if (!($room = $this->roomRepository->find($uriVariables['id'] ?? null))) {
            throw new NotFoundHttpException();
        }

        if (!($gameState = $this->gameStateProvider->get((string) $room->getId()))) {
            throw new NotFoundHttpException();
        }

        $user = $this->currentUserProvider->getCurrentUser();
        $topic = \sprintf('game/%s', $room->getId());
        $privateTopic = $topic.'-'.($user->getId() === $gameState->player1->player->id ? '1' : '2');
        $token = $this->hub->getFactory()?->create([$topic, $privateTopic], []);
        $url = \sprintf('%s?topic=%s&topic=%s', $this->hub->getPublicUrl(), $topic, $privateTopic);

        return [
            'state' => $this->gameStateConverter->convertGameState($gameState, (string) $user->getId()),
            'mercure_url' => $url,
            'mercure_token' => $token,
        ];
    }
}
