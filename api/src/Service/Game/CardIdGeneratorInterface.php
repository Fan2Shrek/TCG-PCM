<?php

declare(strict_types=1);

namespace App\Service\Game;

interface CardIdGeneratorInterface
{
    public function generateCardId(string $templateId): string;
}
