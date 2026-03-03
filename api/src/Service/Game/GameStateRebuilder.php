<?php

declare(strict_types=1);

namespace App\Service\Game;

use App\Enum\GameEventTypeEnum;
use App\Game\AbstractCard;
use App\Game\Card\AbstractPlayableCard;
use App\Game\Card\CardState;
use App\Game\State\GameEvent;
use App\Game\State\GameState;
use App\Service\Game\Factory\GameContextFactoryInterface;

class GameStateRebuilder
{
    public function __construct(
        private GameEventApplierInterface $applier,
        private CardRegistryInterface $cardRegistry,
        private GameContextFactoryInterface $gameContextFactory,
    ) {}

    /**
     * @param GameEvent[] $events
     */
    public function rebuild(GameState $initial, array $events): GameState
    {
        $state = $initial;

        foreach ($events as $event) {
            $state = match ($event->type) {
                GameEventTypeEnum::CARD_PLAYED => $this->replayCard($event, $state),
                default => $this->applier->apply($event, $state),
            };
            $state = $state->withLastEventId($event->id);
        }

        return $state;
    }

    private function replayCard(GameEvent $event, GameState $state): GameState
    {
        /**
         * @var array{playerId: string, cardId: string, data?: array} $data
         */
        $data = $event->data;
        $card = $this->createCardFromState($state->cards[$data['cardId']]);

        if (!$card instanceof AbstractPlayableCard) {
            throw new \RuntimeException(sprintf('Card with id %s is not an instance of AbstractCard', $card->getId()));
        }

        $ctx = $this->gameContextFactory->createGameContext($state, $data['playerId']);
        $card->play($ctx, $data);

        $events = array_merge([$event], $ctx->flushEvents(), [
            GameEvent::game(GameEventTypeEnum::CARD_DISCARDED, [
                'playerId' => $data['playerId'],
                'cardId' => $data['cardId'],
            ]),
        ]);

        return $this->applier->applyMultiple($events, $state);
    }

    private function createCardFromState(CardState $state): AbstractCard
    {
        $card = $this->cardRegistry->getCardTemplateById($state->templateId);
        $card->setState($state);

        return $card;
    }
}
