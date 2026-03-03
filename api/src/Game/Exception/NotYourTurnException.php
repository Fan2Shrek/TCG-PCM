<?php

declare(strict_types=1);

namespace App\Game\Exception;

class NotYourTurnException extends GameException
{
    public function __construct()
    {
        parent::__construct('It is not your turn to play.');
    }
}
