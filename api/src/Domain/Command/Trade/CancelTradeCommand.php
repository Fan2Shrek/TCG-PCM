<?php

declare(strict_types=1);

namespace App\Domain\Command\Trade;

use App\Api\Serializer\CurrentResourceAwareInterface;
use App\Entity\Trade;

/**
 * @implements CurrentResourceAwareInterface<Trade>
 */
final class CancelTradeCommand implements CurrentResourceAwareInterface
{
    private Trade $trade;

    public function getCurrentResource(): Trade
    {
        return $this->trade;
    }

    public function setCurrentResource(object $resource): void
    {
        $this->trade = $resource;
    }
}
