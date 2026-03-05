<?php

declare(strict_types=1);

namespace App\Service\Game;

use App\Game\AbstractCard;
use App\Game\Card\CardState;
use App\Game\Card\Interface\ComputedCardInterface;
use Psr\Cache\CacheItemInterface;
use Symfony\Contracts\Cache\CacheInterface;

final class CardFactory implements CardFactoryInterface
{
    public function __construct(
        private CardRegistryInterface $cardRegistry,
        private CacheInterface $cache,
    ) {}

    public function create(string $cardId): AbstractCard
    {
        return clone $this->cardRegistry->getCardTemplateById($cardId);
    }

    public function createWithState(string $cardId, CardState $state): AbstractCard
    {
        $card = $this->create($cardId);
        $card->setState($state);

        if ($card instanceof ComputedCardInterface) {
            $value = $this->cache->get(sha1($card::class), static function (CacheItemInterface $item) use ($card) {
                $item->expiresAfter(60 * 60);

                return $card->computeValue();
            });

            $card->setComputedValue($value);
        }

        return $card;
    }
}
