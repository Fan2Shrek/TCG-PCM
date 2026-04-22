<?php

declare(strict_types=1);

namespace App\Service;

use App\Enum\GameEventTypeEnum;
use App\Game\State\GameEvent;
use App\Game\State\GameState;
use App\Service\Game\GameStateConverter;

final class GameEventPresenter
{
    private int $player1Offset = 0;
    private int $player2Offset = 0;

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
                    'playerId' => $event->data['playerId'] ?? $event->data['targetId'] ?? null,
                ],
                $this->buildView($event, $state, $isPrivate, $viewerId),
            ),
        ];
    }

    private function buildView(GameEvent $event, GameState $state, bool $isPrivate, ?string $viewerId = null): array
    {
        if (null === ($event->data['playerId'] ?? $event->data['targetId'] ?? null)) {
            return $this->handleAnonymousEvent($event, $state, $isPrivate, $viewerId);
        }
        /** @var string $player */
        $player = $event->data['playerId'] ?? $event->data['targetId'];

        return match ($event->type) {
            GameEventTypeEnum::CARD_DRAWN => $this->cardDrawnView($event, $state, $isPrivate, $viewerId),
            GameEventTypeEnum::TURN_STARTED => [
                'currentPlayer' => $state->currentPlayer,
            ],
            GameEventTypeEnum::CARD_DISCARDED, GameEventTypeEnum::CARD_PLACE_IN_PLAY_AREA, GameEventTypeEnum::CARD_PLACE_IN_MONSTER_AREA => [
                'cardId' => $event->data['cardId'] ?? null,
                'card' => GameEventTypeEnum::CARD_DISCARDED === $event->type
                    ? null
                    : $this->gameStateConverter->createCardDTO($state->cards[$event->data['cardId']]),
            ],
            GameEventTypeEnum::COINS_GAINED, GameEventTypeEnum::COINS_LOST => [
                'total' => $state->getPlayer($player)->coins,
            ],
            GameEventTypeEnum::HEAL, GameEventTypeEnum::DAMAGE => [
                'total' => $state->getPlayer($player)->healthPoints,
            ],
            default => [],
        };
    }

    private function handleAnonymousEvent(GameEvent $event, GameState $state, bool $isPrivate, ?string $viewerId): array
    {
        return match ($event->type) {
            GameEventTypeEnum::UPDATE_CARD_STATE => [
                // @var string $cardId
                'cardId' => $cardId = $event->data['cardId'],
                'card' => $this->gameStateConverter->createCardDTO($state->getCardState($cardId)),
            ],
            default => [],
        };
    }

    private function cardDrawnView(GameEvent $event, GameState $state, bool $isPrivate, ?string $viewerId = null): array
    {
        /** @var string $playerId */
        $playerId = $event->data['playerId'];
        // this sucks as fck
        $player = $state->getPlayer($playerId);
        $cardId = array_slice($player->hand, -($player === $state->player1 ? $this->player1Offset : $this->player2Offset), 1)[0];

        $player === $state->player1 ? ++$this->player1Offset : ++$this->player2Offset;

        if (!$playerId || !$cardId) {
            throw new \LogicException('CARD_DRAWN requires playerId and cardId');
        }

        $view = [
            'playerId' => $playerId,
            'cardId' => $cardId,
        ];

        if ($isPrivate && $viewerId === $playerId) {
            // this still sucks ass btw
            $player === $state->player1 ? --$this->player1Offset : --$this->player2Offset;
            $cardId = array_slice($player->hand, -($player === $state->player1 ? $this->player1Offset : $this->player2Offset), 1)[0];
            $player === $state->player1 ? --$this->player1Offset : --$this->player2Offset;

            $cardState = $state->cards[$cardId] ?? null;

            if (!$cardState) {
                throw new \LogicException(\sprintf('Card %s not found in state', $cardId));
            }

            $view['card'] = $this->gameStateConverter->createCardDTO($cardState);
        }

        return $view;
    }
}
