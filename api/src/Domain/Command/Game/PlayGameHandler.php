<?php

declare(strict_types=1);

namespace App\Domain\Command\Game;

use App\Game\Exception\GameException;
use App\Game\PlayerAction;
use App\Service\Auth\CurrentUserProviderInterface;
use App\Service\PlayerActionHandler;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class PlayGameHandler
{
    public function __construct(
        private CurrentUserProviderInterface $currentUserProvider,
        private PlayerActionHandler $playerActionHandler,
        private LoggerInterface $gameLogger,
    ) {}

    public function __invoke(PlayGameCommand $command): void
    {
        $user = $this->currentUserProvider->getCurrentUser();

        if (!\in_array($command->actionId, PlayerAction::ACTIONS, true)) {
            throw HttpException::fromStatusCode(Response::HTTP_BAD_REQUEST, 'Invalid action id');
        }

        $room = $command->getCurrentResource();
        $action = new PlayerAction((string) $user->getId(), $command->actionId, $command->payload);

        try {
            $this->playerActionHandler->handle($action, $room);
        } catch (GameException $e) {
            $this->gameLogger->error('Game action failed', [
                'userId' => $user->getId(),
                'actionId' => $command->actionId,
                'payload' => $command->payload,
                'error' => $e->getMessage(),
            ]);
            throw HttpException::fromStatusCode(Response::HTTP_BAD_REQUEST, $e->getMessage());
        } catch (\Throwable $e) {
            // @todo retry without redis cache

            throw $e;
        }
    }
}
