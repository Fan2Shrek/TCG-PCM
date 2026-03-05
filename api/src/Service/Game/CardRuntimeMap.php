<?php

declare(strict_types=1);

namespace App\Service\Game;

use App\Game\AbstractCard;
use App\Game\Card\CardState;

final class CardRuntimeMap
{
    /*
     * @var array<string, AbstractCard>
     */
    private array $map = [];

    public function __construct(
        private CardFactoryInterface $cardFactory,
    ) {}

    public function getByState(CardState $cardState): AbstractCard
    {
        $cardId = $cardState->instanceId;

        $card = $this->map[$cardId] ??= $this->cardFactory->createWithState($cardState->templateId, $cardState);

        $card->setState($cardState);

        return $card;
    }

    public function create(string $cardId): AbstractCard
    {
        return $this->cardFactory->create($cardId);
    }
}
