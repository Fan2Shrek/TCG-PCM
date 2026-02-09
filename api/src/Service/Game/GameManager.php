<?php

declare(strict_types=1);

namespace App\Service\Game;

use App\Entity\Deck;
use App\Entity\Room;
use App\Entity\User;
use App\Enum\GameEventTypeEnum;
use App\Enum\RoomStatusEnum;
use App\Game\Card\Character\AbstractCharacterCard;
use App\Game\Player;
use App\Game\State\GameEvent;
use App\Game\State\GameState;
use App\Game\State\PlayerState;

class GameManager
{
    private const INITIAL_HAND_SIZE = 5;

    public function __construct(
        private CardRegistry $cardsRegistry,
        private GameEventApplier $gameEventApplier,
    ) {}

    public function startGame(Room $room): GameState
    {
        $room->setStatus(RoomStatusEnum::PLAYING);

        if (!($opponent = $room->getOpponent()) || !($opponentDeck = $room->getOpponentDeck())) {
            throw new \RuntimeException('Room has no opponent');
        }

        $player1InitialState = $this->createPlayerStateFromUser($room->getOwner(), $room->getOwnerDeck());
        $player2InitialState = $this->createPlayerStateFromUser($opponent, $opponentDeck);

        $initialGameState = new GameState($player1InitialState, $player2InitialState, null);

        $events = [];
        foreach ($initialGameState->getPlayers() as $player) {
            for ($i = 0; $i < self::INITIAL_HAND_SIZE; $i++) {
                $events[] = GameEvent::game(GameEventTypeEnum::CARD_DRAWN, ['playerId' => $player->id]);
            }
        }

        return $this->gameEventApplier->applyMultiple($events, $initialGameState);
    }

    public function play(GameEvent $event, GameState $gameState): GameState
    {
        return $this->gameEventApplier->apply($event, $gameState);
    }

    private function createPlayerStateFromUser(User $user, Deck $deck): PlayerState
    {
        $characterCard = $this->cardsRegistry->getCardInstanceById($deck->getCharacterCard());

        if (!$characterCard instanceof AbstractCharacterCard) {
            throw new \RuntimeException('Deck character card is not a character card');
        }

        $player = new Player((string) $user->getId(), $user->getUsername(), $characterCard->getHealthPoints());

        return new PlayerState($player, [], $deck->getCards());
    }
}
