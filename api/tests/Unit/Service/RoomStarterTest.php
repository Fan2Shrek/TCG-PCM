<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Entity\Deck;
use App\Entity\Room;
use App\Entity\User;
use App\Enum\RoomStatusEnum;
use App\Game\Card\Character\AbstractCharacterCard;
use App\Game\Player;
use App\Service\Game\CardFactory;
use App\Service\RoomStarter;
use App\Tests\Resources\MockCardRegistry;
use App\Tests\Unit\Fixtures\DummyCard;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Cache\CacheInterface;

final class RoomStarterTest extends TestCase
{
    public function testRoomStartStatus()
    {
        $gm = $this->getSut();
        $room = $this->createRoom();

        $gm->startRoom($room);

        self::assertSame(RoomStatusEnum::PLAYING, $room->getStatus());
    }

    public function testGameStatePlayers()
    {
        $gm = $this->getSut();
        $owner = new TestUser('user', 'email');
        $opponent = new TestUser('opponent', 'email2');
        $ownerDeck = new Deck($owner, 'test', DummyCharacterCard::class, array_fill(0, 5, DummyCard::class));
        $opponentDeck = new Deck($opponent, 'test', DummyCharacterCardWithMoreHP::class, array_fill(0, 5, DummyCard::class));
        $room = new Room($owner);
        $room->setOpponent($opponent);
        $room->setOwnerDeck($ownerDeck);
        $room->setOpponentDeck($opponentDeck);

        $gameState = $gm->startRoom($room);

        $expectedPlayer1 = Player::fromUser($owner);
        $expectedPlayer2 = Player::fromUser($opponent);

        self::assertEquals($expectedPlayer1, $gameState->player1->player);
        self::assertEquals($expectedPlayer2, $gameState->player2->player);
    }

    public function testGameStartCharacterState()
    {
        $gm = $this->getSut();
        $owner = new TestUser('user', 'email');
        $opponent = new TestUser('opponent', 'email2');
        $ownerDeck = new Deck($owner, 'test', DummyCharacterCard::class, array_fill(0, 5, DummyCard::class));
        $opponentDeck = new Deck($opponent, 'test', DummyCharacterCardWithMoreHP::class, array_fill(0, 5, DummyCard::class));
        $room = new Room($owner);
        $room->setOpponent($opponent);
        $room->setOwnerDeck($ownerDeck);
        $room->setOpponentDeck($opponentDeck);

        $gameState = $gm->startRoom($room);

        $character1CardState = $gameState->cards[$gameState->player1->characterCardId];
        $character2CardState = $gameState->cards[$gameState->player2->characterCardId];

        self::assertSame(DummyCharacterCard::class, $character1CardState->templateId);
        self::assertSame(DummyCharacterCardWithMoreHP::class, $character2CardState->templateId);
    }

    public function testGameStartCharacterId()
    {
        $gm = $this->getSut();
        $owner = new TestUser('user', 'email');
        $opponent = new TestUser('opponent', 'email2');
        $ownerDeck = new Deck($owner, 'test', DummyCharacterCard::class, array_fill(0, 5, DummyCard::class));
        $opponentDeck = new Deck($opponent, 'test', DummyCharacterCardWithMoreHP::class, array_fill(0, 5, DummyCard::class));
        $room = new Room($owner);
        $room->setOpponent($opponent);
        $room->setOwnerDeck($ownerDeck);
        $room->setOpponentDeck($opponentDeck);

        $gameState = $gm->startRoom($room);

        self::assertArrayHasKey($gameState->player1->characterCardId, $gameState->cards);
        self::assertArrayHasKey($gameState->player2->characterCardId, $gameState->cards);
        self::assertNotSame($gameState->player1->characterCardId, $gameState->player2->characterCardId);
    }

    private function createRoom(): Room
    {
        $owner = $this->createStub(User::class);
        $deck = new Deck($owner, 'test', DummyCharacterCard::class, array_fill(0, 10, DummyCard::class));
        $room = new Room($owner);
        $room->setOpponent($this->createStub(User::class));
        $room->setOwnerDeck($deck);
        $room->setOpponentDeck($deck);

        return $room;
    }

    private function getSut(): RoomStarter
    {
        return new RoomStarter(new CardFactory(
            new MockCardRegistry([
                DummyCard::class => DummyCard::class,
                'other_card' => DummyCard::class,
                DummyCharacterCard::class => DummyCharacterCard::class,
                DummyCharacterCardWithMoreHP::class => DummyCharacterCardWithMoreHP::class,
            ]),
            new class implements CacheInterface {
                public function get(string $name, callable $callable, ?float $beta = null, ?array &$metadata = null): mixed
                {
                    return $callable();
                }

                public function delete(string $key): bool
                {
                    // no-op
                    return true;
                }
            },
        ));
    }
}

class DummyCharacterCard extends AbstractCharacterCard
{
    public function getHealthPoints(): int
    {
        return 30;
    }

    public function getId(): string
    {
        return static::class;
    }

    public function getName(): string
    {
        return 'Dummy';
    }

    public function getDescription(): string
    {
        return 'Dummy';
    }
}

class DummyCharacterCardWithMoreHP extends DummyCharacterCard
{
    public function getHealthPoints(): int
    {
        return 40;
    }
}

class TestUser extends User
{
    public function getId(): int
    {
        return spl_object_id($this);
    }
}
