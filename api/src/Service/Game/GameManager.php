<?php

declare(strict_types=1);

namespace App\Service\Game;

use App\Entity\Deck;
use App\Entity\Room;
use App\Entity\User;
use App\Enum\RoomStatusEnum;
use App\Game\AbstractCard;
use App\Game\Card\Character\AbstractCharacterCard;
use App\Game\GameContext;
use App\Game\Player;

final class GameManager
{
    public function __construct(
        private GameContextRepositoryInterface $gameContextRepository,
        private CardManager $cardsManager,
    ) {}

    public function startGame(Room $room): void
    {
        $room->setStatus(RoomStatusEnum::PLAYING);

        if (!($opponent = $room->getOpponent())) {
            throw new \RuntimeException('Room has no opponent');
        }

        $gameContext = new GameContext(
            $this->createPlayerFromUser($room->getOwner(), $room->getOwnerDeck()),
            $this->createPlayerFromUser($opponent, $room->getOpponentDeck()),
        );

        $this->gameContextRepository->save($gameContext, $room);
    }

    public function play(AbstractCard $card, Room $room): void
    {
        $gameContext = $this->gameContextRepository->get($room);
    }

    private function createPlayerFromUser(User $user, Deck $deck): Player
    {
        $characterCard = $this->cardsManager->initiateCard($deck->getCharacterCard());

        if (!$characterCard instanceof AbstractCharacterCard) {
            throw new \RuntimeException('Deck character card is not a character card');
        }

        // @todo deck
        // @todo draw cards
        return new Player(
            $user->getUsername(),
            $characterCard->getHealthPoints(),
        );
    }
}
