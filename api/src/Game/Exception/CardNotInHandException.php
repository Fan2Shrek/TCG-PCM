<?php

declare(strict_types=1);

namespace App\Game\Exception;

use App\Game\Player;
use Throwable;

class CardNotInHandException extends GameException
{
    public function __construct(Player $player, string $cardName, int $code = 0, ?Throwable $previous = null)
    {
        return parent::__construct(\sprintf('Player %s has not the %s card.', $player->name, $cardName), $code, $previous);
    }
}
