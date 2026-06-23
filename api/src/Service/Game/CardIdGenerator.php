<?php

declare(strict_types=1);

namespace App\Service\Game;

use Symfony\Component\Uid\Uuid;

final class CardIdGenerator implements CardIdGeneratorInterface
{
    public function generateCardId(string $templateId): string
    {
        return Uuid::v4()->toString();
    }
}
