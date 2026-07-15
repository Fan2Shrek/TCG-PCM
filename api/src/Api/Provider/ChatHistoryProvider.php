<?php

declare(strict_types=1);

namespace App\Api\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Game\ChatMessage;
use App\Repository\Game\ChatMessageRepository;
use App\Repository\RoomRepository;
use App\Service\Auth\CurrentUserProviderInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @implements ProviderInterface<ChatMessage>
 */
final class ChatHistoryProvider implements ProviderInterface
{
    public function __construct(
        private RoomRepository $roomRepository,
        private CurrentUserProviderInterface $currentUserProvider,
        private ChatMessageRepository $chatMessageRepository,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        if (!($room = $this->roomRepository->find($uriVariables['id'] ?? null))) {
            throw new NotFoundHttpException();
        }

        $user = $this->currentUserProvider->getCurrentUser();

        if ($room->getOwner() !== $user && $room->getOpponent() !== $user) {
            throw HttpException::fromStatusCode(Response::HTTP_FORBIDDEN, 'You are not in this room.');
        }

        return $this->chatMessageRepository->findByRoom((string) $room->getId());
    }
}
