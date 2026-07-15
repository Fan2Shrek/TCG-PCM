<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api;

use App\Entity\Game\ChatMessage;
use App\Tests\Functional\FunctionalTestCase;
use App\Tests\Resources\Fixtures\ThereIs;

final class ChatApiTest extends FunctionalTestCase
{
    protected const string CHAT_URI = '/api/game/{id}/chat';

    public function testOwnerCanSendMessage(): void
    {
        $room = ThereIs::aRoom()->withOwner($this->currentUser)->withOpponent()->build();

        $this->post($this->getUri(self::CHAT_URI, ['id' => (string) $room->getId()]), [
            'message' => 'Hello there',
        ]);

        self::assertResponseStatusCodeSame(204);
    }

    public function testOpponentCanSendMessage(): void
    {
        $room = ThereIs::aRoom()->withOpponent($this->currentUser)->build();

        $this->post($this->getUri(self::CHAT_URI, ['id' => (string) $room->getId()]), [
            'message' => 'Hello there',
        ]);

        self::assertResponseStatusCodeSame(204);
    }

    public function testNonParticipantCannotSendMessage(): void
    {
        $room = ThereIs::aRoom()->withOpponent()->build();

        $this->post($this->getUri(self::CHAT_URI, ['id' => (string) $room->getId()]), [
            'message' => 'Hello there',
        ]);

        self::assertResponseStatusCodeSame(403);
    }

    public function testEmptyMessageIsRejected(): void
    {
        $room = ThereIs::aRoom()->withOwner($this->currentUser)->withOpponent()->build();

        $this->post($this->getUri(self::CHAT_URI, ['id' => (string) $room->getId()]), [
            'message' => '   ',
        ]);

        self::assertResponseStatusCodeSame(400);
    }

    public function testTooLongMessageIsRejected(): void
    {
        $room = ThereIs::aRoom()->withOwner($this->currentUser)->withOpponent()->build();

        $this->post($this->getUri(self::CHAT_URI, ['id' => (string) $room->getId()]), [
            'message' => str_repeat('a', 501),
        ]);

        self::assertResponseStatusCodeSame(400);
    }

    public function testChatHistoryIsReturnedInOrder(): void
    {
        $room = ThereIs::aRoom()->withOwner($this->currentUser)->withOpponent()->build();

        $roomId = (string) $room->getId();
        $this->getEm()->persist(new ChatMessage($roomId, (string) $this->currentUser->getId(), $this->currentUser->getUsername(), 'first'));
        $this->getEm()->persist(new ChatMessage($roomId, (string) $room->getOpponent()->getId(), $room->getOpponent()->getUsername(), 'second'));
        $this->getEm()->flush();

        $response = $this->get($this->getUri(self::CHAT_URI, ['id' => $roomId]));

        self::assertResponseIsSuccessful();

        $messages = $response->toArray();

        self::assertCount(2, $messages);
        self::assertSame('first', $messages[0]['message']);
        self::assertSame('second', $messages[1]['message']);
        self::assertSame($this->currentUser->getUsername(), $messages[0]['authorUsername']);
    }

    public function testNonParticipantCannotReadHistory(): void
    {
        $room = ThereIs::aRoom()->withOpponent()->build();

        $this->get($this->getUri(self::CHAT_URI, ['id' => (string) $room->getId()]));

        self::assertResponseStatusCodeSame(403);
    }
}
