<?php

declare(strict_types=1);

namespace App\Tests\GameReplay;

use App\Game\AbstractCard;
use App\Service\Game\CardFactoryInterface;
use App\Service\Game\CardRegistryInterface;
use App\Service\Game\CardRuntimeMap;
use App\Service\Game\GameEventApplier;
use App\Service\Game\Factory\ReplayableGameContextFactory;
use App\Service\Game\GameManager;
use App\Service\Game\GameStateRebuilder;
use App\Tests\Resources\MockCardRegistry;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('replay')]
final class GameReplayTest extends TestCase
{
    private const REPLAY_DIR = __DIR__.'/resources';

    #[DataProvider('replayProvider')]
    public function testReplay(string $fileName)
    {
        if (!file_exists($fileName = \sprintf("%s/%s.php", self::REPLAY_DIR, $fileName))) {
            $this->markTestSkipped(sprintf('Replay file "%s.php" not found.', $fileName));
        }

        $data = require $fileName;

        $gameState = $data['initialGameState'];
        $events = $data['events'];

        $gameReplayer = $this->getGameStateRebuilder();
        $gameState = $gameReplayer->rebuild($gameState, $events);

        $property = (new \ReflectionClass($gameState))->getProperty('lastAddedCardId');
        $property->setValue($gameState, null);

        self::assertEquals($data['finalGameState'], $gameState);
    }

    public static function replayProvider(): array
    {
        return [
            'replay1' => ['replay1'],
            'replay2' => ['replay2'],
            'replay3' => ['replay3'],
        ];
    }

    private function getGameStateRebuilder(): GameStateRebuilder
    {
        $cardsListPath = dirname(__DIR__, 2).'/resources/cards_list.php';

        return new GameStateRebuilder(
            new GameManager(
                new CardRuntimeMap(
                    new TestCardFactory(
                        new MockCardRegistry(array_merge(require $cardsListPath, $this->getDummiesCard())),
                    ),
                ),
                new ReplayableGameContextFactory(),
                new GameEventApplier(),
            ),
        );
    }

    private function getDummiesCard(): array
    {
        return [
            'dummy_character' => DummyCharacterCard::class,
        ];
    }
}

class TestCardFactory implements CardFactoryInterface
{
    public function __construct(
        private CardRegistryInterface $cardRegistry,
    ) {
    }

    public function create(string $cardId): AbstractCard
    {
        return clone $this->cardRegistry->getCardTemplateById($cardId);
    }

    public function createWithState(string $cardId, \App\Game\Card\CardState $state): AbstractCard
    {
        $card = $this->create($cardId);
        $card->setState($state);

        return $card;
    }
}
