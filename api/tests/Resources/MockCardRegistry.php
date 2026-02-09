<?php

declare(strict_types=1);

namespace App\Tests\Resources;

use App\Service\Game\CardRegistry;
use App\Tests\Unit\Fixtures\DummyCard;

final class MockCardRegistry extends CardRegistry
{
    private array $mockCardsList;

    public function __construct(
        ?array $mockCardsList = null,
    ) {
        $this->mockCardsList = $mockCardsList ?? array_merge(require dirname(__DIR__, 2).'/resources/cards_list.php', [DummyCard::class => DummyCard::class]);
    }

    protected function getCardsList(): array
    {
        return $this->mockCardsList;
    }
}
