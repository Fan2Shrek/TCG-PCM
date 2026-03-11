<?php

declare(strict_types=1);

namespace App\Game\Card\Character;

use App\Game\Card\Interface\TurnAwareInterface;
use App\Game\GameContext;
use App\Game\GameUtils;

final class StonksCard extends AbstractCharacterCard implements TurnAwareInterface
{
    private const COINS_BONUS = 1;
    private const COINS_INTEREST_POURCENT = 30;
    private const COINS_INTEREST_MAX = 7;

    public function getId(): string
    {
        return 'Stonks';
    }

    public function getName(): string
    {
        return 'Stonks';
    }

    public function getImg(): string
    {
        return 'https://lapasseduvent.com/wp-content/uploads/2022/08/meme-stonks.jpg';
    }

    public function getHealthPoints(): int
    {
        return 3000;
    }

    public function getDescription(): string
    {
        return GameUtils::formatDescription('Gains {{value}} coins at the start of each turn. Also, at the end of each turn, gain {{value2}}% of your current coins as interest (up to {{const}} coins).', [
            'value' => $this->getValue(self::COINS_BONUS, true),
            'value2' => $this->getValue(self::COINS_INTEREST_POURCENT, true),
            'const' => self::COINS_INTEREST_MAX,
        ]);
    }

    public function onTurnStart(GameContext $gameContext): void
    {
        if (!$gameContext->isCurrentPlayer($this->getOwnerId())) {
            return;
        }

        $gameContext->addCoins($this->getValue(self::COINS_BONUS, true));
    }

    public function onTurnEnd(GameContext $gameContext): void
    {
        // Inverted bc turn ended
        if ($gameContext->isCurrentPlayer($this->getOwnerId())) {
            return;
        }

        $currentCoins = $gameContext->getCurrentPlayerState()->coins;
        $interest = (int) floor(($currentCoins * $this->getValue(self::COINS_INTEREST_POURCENT, true)) / 100);
        $interest = min($interest, $this->getValue(self::COINS_INTEREST_MAX, true));

        $gameContext->addCoins($interest, $this->getOwnerId());
    }
}
