<?php

declare(strict_types=1);

namespace App\Service;

use App\Api\DTO\GameStateDTO;
use App\Entity\Room;
use App\Enum\GameEventTypeEnum;
use App\Game\State\GameEvent;
use App\Game\State\GameState;
use App\Service\Game\GameStateConverter;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

final class GameEventPublisher
{
    public function __construct(
        private HubInterface $hub,
        private GameStateConverter $gameStateConveter,
    ) {}

    /**
     * @param GameEvent[] $events
     */
    public function publish(array $events, GameState $state, Room $room): void
    {
        $roomId = $this->getId($room);

        $player1State = $this->gameStateConveter->convertGameState($state, $state->player1->player->id);
        $player2State = $this->gameStateConveter->convertGameState($state, $state->player2->player->id);

        foreach ($events as $event) {
            $this->handlePrivateEvents($event);
            $update = new Update($roomId, json_encode([
                'event' => $this->formatEvent($event, $player1State, $player2State),
            ], JSON_THROW_ON_ERROR));

            $this->hub->publish($update);
        }
    }

    private function handlePrivateEvents(GameEvent $event): void
    {
        // @ŧodo
    }

    /**
     * @return array<string, mixed>
     */
    private function formatEvent(GameEvent $event, GameStateDTO $player1DTO, GameStateDTO $player2DTO): array
    {
        $data = $event->data;

        /** @var ?string $eventPlayerId */
        $eventPlayerId = $event->data['playerId'] ?? null;

        if (!$eventPlayerId) {
            return [];
        }

        $newHand = $eventPlayerId = $player1DTO->player1->player->id ? $player1DTO->player1->hand : $player1DTO->player2->hand;
        $newCards = $eventPlayerId = $player1DTO->player1->player->id ? $player1DTO->cards : $player1DTO->cards;
        $newDrawpile = $eventPlayerId = $player1DTO->player1->player->id ? $player1DTO->player1->drawPile : $player1DTO->player2->drawPile;

        $partialState = match ($event->type) {
            GameEventTypeEnum::CARD_DRAWN => [
                'hand' => $newHand, // @todo publish to player private topic
                'drawPile' => $newDrawpile,
                'cards' => $newCards,
            ],
            default => null,
        };

        return [
            'type' => $event->type,
            'data' => $data,
            'partialState' => $partialState,
        ];
    }

    private function getId(Room $room): string
    {
        return \sprintf('game/%s', $room->getId());
    }
}
