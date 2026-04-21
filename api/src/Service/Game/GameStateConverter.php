<?php

declare(strict_types=1);

namespace App\Service\Game;

use App\Api\DTO\CardDTO;
use App\Api\DTO\GameStateDTO;
use App\Api\DTO\PlayerStateDTO;
use App\Game\Card\CardState;
use App\Game\State\GameState;

final class GameStateConverter
{
    public function __construct(
        private CardRegistryInterface $cardRegistry,
    ) {}

    public function convertGameState(GameState $gameState, string $playerId): GameStateDTO
    {
        $cards = $this->removeCardsNotVisibleToPlayer($gameState, $playerId) |> $this->convertCards(...);

        return new GameStateDTO(
            player1: PlayerStateDTO::fromPlayerState($gameState->player1),
            player2: PlayerStateDTO::fromPlayerState($gameState->player2),
            currentPlayer: $gameState->currentPlayer,
            cards: $cards,
        );
    }

    public function createCardDTO(CardState $state): CardDTO
    {
        $template = $this->cardRegistry->getCardTemplateById($state->templateId);
        $template->setState($state);

        return new CardDTO(
            name: $template->getName(),
            description: $template->getDescription(),
            image: $template->getImage(),
            rarity: $template::$rarity,
            set: $template::$serie,
            instanceId: $state->instanceId,
            effects: $state->effects,
        );
    }

    /**
     * @param array<string, CardState> $cardsState
     *
     * @return array<string, CardDTO>
     */
    private function convertCards(array $cardsState): array
    {
        return array_map($this->createCardDTO(...), $cardsState);
    }

    /**
     * @return array<string, CardState>
     */
    private function removeCardsNotVisibleToPlayer(GameState $gameState, string $playerId): array
    {
        return array_filter(
            $gameState->cards,
            static fn(CardState $state) => !\in_array($state->instanceId, $gameState->getOtherPlayerStateById($playerId)->hand, true),
        );
    }
}
