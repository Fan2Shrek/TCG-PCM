<?php

declare(strict_types=1);

namespace App\Service\Game;

use App\Entity\Deck;
use App\Entity\Room;
use App\Entity\User;
use App\Enum\RoomStatusEnum;
use App\Game\Card\Character\AbstractCharacterCard;
use App\Game\Player;
use App\Game\State\GameEvent;
use App\Game\State\GameState;
use App\Game\State\PlayerState;
use App\Service\Game\State\GameEventRepositoryInterface;
use App\Service\Game\State\GameStateRepositoryInterface;

class GameManager
{
    public function __construct(
        private GameStateRepositoryInterface $gameStateRepository,
        private GameEventRepositoryInterface $gameEventRepository,
        private CardManager $cardsManager,
    ) {}

    public function startGame(Room $room): void
    {
        $room->setStatus(RoomStatusEnum::PLAYING);

        if (!($opponent = $room->getOpponent()) || !$opponentDeck = $room->getOpponentDeck()) {
            throw new \RuntimeException('Room has no opponent');
        }

        $player1InitialState = $this->createPlayerStateFromUser($room->getOwner(), $room->getOwnerDeck());
        $player2InitialState = $this->createPlayerStateFromUser($opponent, $opponentDeck);

        $initialGameState = new GameState(
            $player1InitialState,
            $player2InitialState,
            null,
        );

        $this->gameStateRepository->save($initialGameState, $room);
    }

    public function play(GameEvent $event, GameState $gameState): void
    {
        dd($gameState);
    }

    public function playFromRoom(GameEvent $event, Room $room): void
    {
        $this->play($event, $this->getGameStateFromRoom($room));
    }

    private function getGameStateFromRoom(Room $room): GameState
    {
        $gameState = $this->gameStateRepository->get($room);

        foreach ($this->gameEventRepository->getEventsSince($gameState->lastEventid, $room->getId()->toString()) as $event) {
            $this->play($event, $gameState);
        }

        return $gameState;
    }

    private function createPlayerStateFromUser(User $user, Deck $deck): PlayerState
    {
        $characterCard = $this->cardsManager->initiateCard($deck->getCharacterCard());

        if (!$characterCard instanceof AbstractCharacterCard) {
            throw new \RuntimeException('Deck character card is not a character card');
        }

        $player = new Player(
            $user->getUsername(),
            $characterCard->getHealthPoints(),
        );

        // @todo deck
        // @todo draw cards
        return new PlayerState(
            $player,
            [],
            [],
        );
    }
}
