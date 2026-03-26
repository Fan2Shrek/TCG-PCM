<?php

declare(strict_types=1);

namespace App\Domain\Command\Game;

use App\Game\PlayerAction;
use App\Service\Auth\CurrentUserProviderInterface;
use App\Service\Game\Pipeline\GamePipeline;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class PlayGameHandler
{
    public function __construct(
        private CurrentUserProviderInterface $currentUserProvider,
        private GamePipeline $gamePipeline,
    ) {}

    public function __invoke(PlayGameCommand $command): void
    {
        $user = $this->currentUserProvider->getCurrentUser();

        if (!\in_array($command->actionId, PlayerAction::ACTIONS, true)) {
            throw HttpException::fromStatusCode(Response::HTTP_BAD_REQUEST, 'Invalid action id');
        }

        $room = $command->getCurrentResource();

        $action = new PlayerAction((string) $user->getId(), $command->actionId, (string) $room->getId(), $command->payload);

        $this->gamePipeline->start($action);
    }
}
