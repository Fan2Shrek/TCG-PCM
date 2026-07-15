<?php

declare(strict_types=1);

namespace App\Domain\Command\Trade;

use App\Api\Serializer\CurrentResourceAwareInterface;
use App\Entity\Trade;

/**
 * @implements CurrentResourceAwareInterface<Trade>
 */
final class OfferCardCommand implements CurrentResourceAwareInterface
{
    private Trade $trade;

    public function __construct(
        public readonly string $card,
    ) {}

    public function getCurrentResource(): Trade
    {
        return $this->trade;
    }

    public function setCurrentResource(object $resource): void
    {
        $this->trade = $resource;
    }
}
