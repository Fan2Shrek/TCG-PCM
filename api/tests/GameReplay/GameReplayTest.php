<?php

declare(strict_types=1);

namespace App\Tests\GameReplay;

use App\Enum\GameEventTypeEnum;
use App\Service\Game\CardFactory;
use App\Service\Game\GameEventApplier;
use App\Service\Game\Factory\ReplayableGameContextFactory;
use App\Service\Game\GameManager;
use App\Service\Game\GameStateRebuilder;
use App\Tests\Resources\MockCardRegistry;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Cache\CacheInterface;

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

        $gameState = $data['gameState'];
        $events = $data['events'];

        $gameReplayer = $this->getGameStateRebuilder($this->filterRolls($events));
        $gameState = $gameReplayer->rebuild($gameState, $events);

        self::assertEquals($data['finalGameState'], $gameState);
    }

    public static function replayProvider(): array
    {
        return [
            'replay1' => ['replay1'],
        ];
    }

    private function getGameStateRebuilder(array $rolls): GameStateRebuilder
    {
        $cardsListPath = dirname(__DIR__, 2).'/resources/cards_list.php';

        return new GameStateRebuilder(
            new GameEventApplier(),
            new GameManager(
                new CardFactory(
                    new MockCardRegistry(require $cardsListPath),
                    new class implements CacheInterface {
                        public function get(string $name, callable $callable, ?float $beta = null, array &$metadata = null): mixed{
                            return $callable();
                        }

                        public function delete(string $key): bool
                        {
                            // no-op
                            return true;
                        }
                    }
                ),
                $factory = new ReplayableGameContextFactory($rolls),
            ),
            $factory,
        );
    }

    private function filterRolls(array &$events): array
    {
        $rolls = [];

        foreach ($events as $event) {
            if ($event->type === GameEventTypeEnum::DICE_ROLLED) {
                $rolls[] = $event->data['result'];
            }
        }

        $events = array_filter($events, fn ($event) => $event->type !== GameEventTypeEnum::DICE_ROLLED);

        return $rolls;
    }
}
