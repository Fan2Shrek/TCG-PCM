<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Deck;
use App\Entity\Room;
use App\Entity\User;
use App\Enum\RoomStatusEnum;
use App\Game\Card\CardState;
use App\Game\Card\Character\AbstractCharacterCard;
use App\Game\Player;
use App\Game\State\GameState;
use App\Game\State\PlayArea;
use App\Game\State\PlayerState;
use App\Service\Game\CardFactoryInterface;
use App\Service\Game\CardIdGeneratorInterface;

final class RoomStarter
{
    public function __construct(
        private CardFactoryInterface $cardFactory,
        private CardIdGeneratorInterface $cardIdGenerator,
    ) {}

    public function startRoom(Room $room): GameState
    {
        $room->setStatus(RoomStatusEnum::PLAYING);

        if (!($opponent = $room->getOpponent()) || !($opponentDeck = $room->getOpponentDeck())) {
            throw new \RuntimeException('Room has no opponent');
        }

        return $this->initializeGameState($room, $opponent, $opponentDeck);
    }

    private function initializeGameState(Room $room, User $opponent, Deck $opponentDeck): GameState
    {
        $player1CharacterCard = $this->cardFactory->create($room->getOwnerDeck()->getCharacterCard());
        $player2CharacterCard = $this->cardFactory->create($opponentDeck->getCharacterCard());

        if (!$player1CharacterCard instanceof AbstractCharacterCard || !$player2CharacterCard instanceof AbstractCharacterCard) {
            throw new \LogicException('Character card must be an instance of AbstractCharacterCard');
        }

        $player1State = $this->createPlayerStateFromUser($room->getOwner(), $room->getOwnerDeck(), $player1CharacterCard);
        $player2State = $this->createPlayerStateFromUser($opponent, $opponentDeck, $player2CharacterCard);

        $player1CharacterCardState = new CardState($player1State->characterCardId, $player1CharacterCard->getId(), $player1State->player->id);
        $player2CharacterCardState = new CardState($player2State->characterCardId, $player2CharacterCard->getId(), $player2State->player->id);

        $player1CharacterCard->setState($player1CharacterCardState);
        $player2CharacterCard->setState($player2CharacterCardState);

        return new GameState($player1State, $player2State, null, $this->generateSeed(), $player1State->player->id, [
            $player1CharacterCardState->instanceId => $player1CharacterCardState,
            $player2CharacterCardState->instanceId => $player2CharacterCardState,
        ]);
    }

    private function createPlayerStateFromUser(User $user, Deck $deck, AbstractCharacterCard $characterCard): PlayerState
    {
        $player = Player::fromUser($user);
        $cardsIds = $this->createCardsFromDeck($deck);

        return new PlayerState(
            $player,
            $characterCard->getHealthPoints(),
            $characterCard->getHealthPoints(),
            $this->cardIdGenerator->generateCardId($characterCard->getId()),
            [],
            $cardsIds,
            0,
            new PlayArea(),
        );
    }

    /**
     * @return array<string, string>
     */
    private function createCardsFromDeck(Deck $deck): array
    {
        $cardsIds = [];
        $cards = $deck->getCards();
        shuffle($cards);

        foreach ($cards as $card) {
            $cardsIds[$this->cardIdGenerator->generateCardId($card)] = $card;
        }

        return $cardsIds;
    }

    private function generateSeed(): int
    {
        return random_int(0, 0xFFFF_FFFF);
    }
}
