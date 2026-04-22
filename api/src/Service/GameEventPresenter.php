<?php

declare(strict_types=1);

namespace App\Service;

use App\Enum\GameEventTypeEnum;
use App\Game\State\GameEvent;
use App\Game\State\GameState;
use App\Service\Game\GameStateConverter;

final class GameEventPresenter
{
    public function __construct(
        private GameStateConverter $gameStateConverter,
    ) {}

    public function present(GameEvent $event, GameState $state, bool $isPrivate, ?string $viewerId = null): array
    {
        return [
            'type' => $event->type->value,
            'data' => $event->data,
            'view' => array_merge(
                [
                    'playerId' => $event->data['playerId'] ?? null,
                ],
                $this->buildView($event, $state, $isPrivate, $viewerId),
            ),
        ];
    }

    private function buildView(GameEvent $event, GameState $state, bool $isPrivate, ?string $viewerId = null): array
    {
        /** @var string $player */
        $player = $event->data['playerId'];

        return match ($event->type) {
            GameEventTypeEnum::CARD_DRAWN => $this->cardDrawnView($event, $state, $isPrivate, $viewerId),
            GameEventTypeEnum::TURN_STARTED => [
                'currentPlayer' => $state->currentPlayer,
            ],
            GameEventTypeEnum::CARD_DISCARDED, GameEventTypeEnum::CARD_PLACE_IN_PLAY_AREA, GameEventTypeEnum::CARD_PLACE_IN_MONSTER_AREA => [
                'cardId' => $event->data['cardId'] ?? null,
                'card' => GameEventTypeEnum::CARD_DISCARDED === $event->type
                    ? null
                    : $this->gameStateConverter->createCardDTO($state->cards[$event->data['cardId']]) ?? null,
            ],
            GameEventTypeEnum::COINS_GAINED, GameEventTypeEnum::COINS_LOST => [
                'total' => $state->getPlayer($player)->coins,
            ],
            default => [],
        };
    }

    private function cardDrawnView(GameEvent $event, GameState $state, bool $isPrivate, ?string $viewerId = null): array
    {
        /** @var string $playerId */
        $playerId = $event->data['playerId'];
        $cardId = array_last($state->getPlayer($playerId)->hand);

        if (!$playerId || !$cardId) {
            throw new \LogicException('CARD_DRAWN requires playerId and cardId');
        }

        $view = [
            'playerId' => $playerId,
            'cardId' => $cardId,
        ];

        if ($isPrivate && $viewerId === $playerId) {
            $cardState = $state->cards[$cardId] ?? null;

            if (!$cardState) {
                throw new \LogicException(\sprintf('Card %s not found in state', $cardId));
            }

            $view['card'] = $this->gameStateConverter->createCardDTO($cardState);
        }

        return $view;
    }
}
