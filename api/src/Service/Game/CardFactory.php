<?php

declare(strict_types=1);

namespace App\Service\Game;

use App\Game\AbstractCard;
use App\Game\Card\CardState;
use App\Game\Card\Interface\ComputedCardInterface;
use Psr\Cache\CacheItemInterface;
use Symfony\Contracts\Cache\CacheInterface;

final class CardFactory
{
    public function __construct(
        private CardRegistryInterface $cardRegistry,
        private CacheInterface $cache,
    ) {}

    public function create(string $cardId): AbstractCard
    {
        return $this->cardRegistry->getCardTemplateById($cardId);
    }

    public function createWithState(string $cardId, CardState $state): AbstractCard
    {
        $cardClass = $this->cardRegistry->getCardTemplateById($cardId);
        $cardClass->setState($state);

        if ($cardClass instanceof ComputedCardInterface) {
            $value = $this->cache->get(sha1($cardClass::class), static function (CacheItemInterface $item) use ($cardClass) {
                $item->expiresAfter(60 * 60);

                return $cardClass->computeValue();
            });

            $cardClass->setComputedValue($value);
        }

        return $cardClass;
    }
}
