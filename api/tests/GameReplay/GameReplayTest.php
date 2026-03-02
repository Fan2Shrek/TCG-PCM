<?php

declare(strict_types=1);

namespace App\Tests\GameReplay;

use App\Enum\GameEventTypeEnum;
use App\Service\Game\GameEventApplier;
use App\Service\Game\GameManager;
use App\Service\Game\Factory\ReplayableGameContextFactory;
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

        $gameState = $data['gameState'];
        $events = $data['events'];

        $gameManager = $this->getGameManager($this->filterRolls($events));

        foreach ($events as $event) {
            $gameState = $gameManager->play($event, $gameState);
        }

        self::assertEquals($data['finalGameState'], $gameState);
    }

    public static function replayProvider(): array
    {
        return [
            'replay1' => ['replay1'],
        ];
    }

    private function getGameManager(array $rolls): GameManager
    {
        $cardsListPath = dirname(__DIR__, 2).'/resources/cards_list.php';

        return new GameManager(
            new MockCardRegistry(require $cardsListPath),
            new GameEventApplier(),
            new ReplayableGameContextFactory($rolls),
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
