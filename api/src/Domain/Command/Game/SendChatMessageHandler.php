<?php

declare(strict_types=1);

namespace App\Domain\Command\Game;

use App\Entity\Game\ChatMessage;
use App\Repository\Game\ChatMessageRepository;
use App\Service\Auth\CurrentUserProviderInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class SendChatMessageHandler
{
    private const int MAX_MESSAGE_LENGTH = 500;

    public function __construct(
        private CurrentUserProviderInterface $currentUserProvider,
        private ChatMessageRepository $chatMessageRepository,
        private HubInterface $hub,
    ) {}

    public function __invoke(SendChatMessageCommand $command): void
    {
        $room = $command->getCurrentResource();
        $user = $this->currentUserProvider->getCurrentUser();

        if ($room->getOwner() !== $user && $room->getOpponent() !== $user) {
            throw HttpException::fromStatusCode(Response::HTTP_FORBIDDEN, 'You are not in this room.');
        }

        $message = trim($command->message);

        if ('' === $message) {
            throw HttpException::fromStatusCode(Response::HTTP_BAD_REQUEST, 'Message cannot be empty.');
        }

        if (mb_strlen($message) > self::MAX_MESSAGE_LENGTH) {
            throw HttpException::fromStatusCode(Response::HTTP_BAD_REQUEST, 'Message is too long.');
        }

        $roomId = (string) $room->getId();

        $chatMessage = new ChatMessage($roomId, (string) $user->getId(), $user->getUsername(), $message);
        $this->chatMessageRepository->save($chatMessage);

        $this->publish($roomId, $chatMessage);
    }

    private function publish(string $roomId, ChatMessage $chatMessage): void
    {
        $topic = "game/{$roomId}";
        $payload = json_encode([
            'type' => 'chat_message',
            'message' => [
                'id' => $chatMessage->getId(),
                'authorId' => $chatMessage->getAuthorId(),
                'authorUsername' => $chatMessage->getAuthorUsername(),
                'message' => $chatMessage->getMessage(),
                'createdAt' => $chatMessage->getCreatedAt()->format(\DateTimeInterface::ATOM),
            ],
        ], JSON_THROW_ON_ERROR);

        $this->hub->publish(new Update($topic, $payload, true));
    }
}
