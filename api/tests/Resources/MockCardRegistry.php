<?php

declare(strict_types=1);

namespace App\Tests\Resources;

use App\Service\Game\CardRegistry;

final class MockCardRegistry extends CardRegistry
{
    private array $mockCardsList;

    public function __construct(
        ?array $mockCardsList = null,
    ) {
        $this->mockCardsList = $mockCardsList ?? require dirname(__DIR__, 2).'/resoources/card_list.php';
    }

    protected function getCardsList(): array
    {
        return $this->mockCardsList;
    }
}
