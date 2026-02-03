<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Game;

use App\Entity\Deck;
use App\Entity\Room;
use App\Entity\User;
use App\Enum\RoomStatusEnum;
use App\Game\Card\Character\AbstractCharacterCard;
use App\Game\GameContext;
use App\Game\Player;
use App\Service\Game\CardManager;
use App\Service\Game\GameContextRepositoryInterface;
use App\Service\Game\GameManager;
use App\Tests\Resources\InMemoryGameContextRepository;
use PHPUnit\Framework\TestCase;

final class GameManagerTest extends TestCase
{
    public function testRoomStartStatus()
    {
        $gm = new GameManager(
            new InMemoryGameContextRepository(),
            new CardManager(),
        );
        $room = $this->createRoom();

        $gm->startGame($room);

        self::assertSame(RoomStatusEnum::PLAYING, $room->getStatus());
    }

    public function testGameContextIsSavedOnStart()
    {
        $spyRepo = new SpyGameContextRepository();
        $gm = new GameManager(
            $spyRepo,
            new CardManager(),
        );
        $room = $this->createRoom();

        $gm->startGame($room);

        self::assertNotNull($spyRepo->gameContext);
    }

    public function testGameContextPlayers()
    {
        $spyRepo = new SpyGameContextRepository();
        $gm = new GameManager(
            $spyRepo,
            new CardManager(),
        );
        $owner = new User('user', 'email');
        $opponent = new User('opponent', 'email2');
        $ownerDeck = new Deck($owner, 'test', DummyCharacterCard::class);
        $opponentDeck = new Deck($opponent, 'test', DummyCharacterCardWithMoreHP::class);
        $room = new Room($owner);
        $room->setOpponent($opponent);
        $room->setOwnerDeck($ownerDeck);
        $room->setOpponentDeck($opponentDeck);

        $gm->startGame($room);
        $gameContext = $spyRepo->gameContext;

        $expectedPlayers = [
            new Player(
                'user',
                30,
            ),
            new Player(
                'opponent',
                40,
            ),
        ];

        self::assertEquals($expectedPlayers, $gameContext->getPlayers());
    }

    private function createRoom(): Room
    {
        $owner = $this->createStub(User::class);
        $deck = new Deck($owner, 'test', DummyCharacterCard::class);
        $room = new Room($owner);
        $room->setOpponent($this->createStub(User::class));
        $room->setOwnerDeck($deck);
        $room->setOpponentDeck($deck);

        return $room;
    }
}


class SpyGameContextRepository implements GameContextRepositoryInterface
{
    public ?GameContext $gameContext = null;

    public function save(GameContext $gameContext, Room $room): void
    {
        $this->gameContext = $gameContext;
    }

    public function get(Room $room): GameContext
    {
        return $this->gameContext;
    }
}

class DummyCharacterCard extends AbstractCharacterCard
{
    public function getHealthPoints(): int
    {
        return 30;
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
