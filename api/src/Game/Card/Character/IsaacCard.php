<?php

declare(strict_types=1);

namespace App\Game\Card\Character;

use App\Enum\CardSetEnum;
use App\Game\Card\Interface\TurnAwareInterface;
use App\Game\Card\Trait\TurnAwareTrait;
use App\Game\GameContext;

final class IsaacCard extends AbstractCharacterCard implements TurnAwareInterface
{
    use TurnAwareTrait;

    public static CardSetEnum $serie = CardSetEnum::TBOI;

    private const DAMAGE = 5;

    public function getId(): string
    {
        return 'Isaac';
    }

    public function getHealthPoints(): int
    {
        return 150;
    }

    public function onTurnStart(GameContext $gameContext): void
    {
        if (!$this->isOwnerTurn($gameContext)) {
            return;
        }

        $opponentState = $gameContext->getPlayerStateById($gameContext->getOtherPlayerId($this->getOwnerId()));
        $targetPool = [...$opponentState->playArea->monsterCards, $opponentState->characterCardId];
        $targetId = $gameContext->selectRandomCardIn($targetPool);

        $gameContext->damageCard($targetId, self::DAMAGE);
    }
}
