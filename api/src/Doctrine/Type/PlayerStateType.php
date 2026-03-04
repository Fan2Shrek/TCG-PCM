<?php

declare(strict_types=1);

namespace App\Doctrine\Type;

use App\Game\Player;
use App\Game\State\PlayArea;
use App\Game\State\PlayerState;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

final class PlayerStateType extends Type
{
    public const NAME = 'player_state';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getJsonTypeDeclarationSQL($column);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        if (!$value instanceof PlayerState) {
            throw new \InvalidArgumentException('Expected PlayerState.');
        }

        return json_encode($value, JSON_THROW_ON_ERROR);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?PlayerState
    {
        if ($value === null || $value instanceof PlayerState) {
            return $value;
        }

        /** @var array{player: array{id: string, name: string}, healthPoints: int, maxHealthPoints: int, characterCardId: string, hand: string[], drawPile: array<string, string>} $data */
        $data = json_decode($value, true, 512, JSON_THROW_ON_ERROR);

        return new PlayerState(
            new Player($data['player']['id'], $data['player']['name']),
            $data['healthPoints'],
            $data['maxHealthPoints'],
            $data['characterCardId'],
            $data['hand'],
            $data['drawPile'],
            new PlayArea(),
        );
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
