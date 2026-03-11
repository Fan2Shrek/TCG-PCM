<?php

declare(strict_types=1);

namespace App\Command;

use App\Game\State\GameEvent;
use App\Game\State\GameState;
use App\Repository\RoomRepository;
use App\Service\Game\GameStateRebuilder;
use App\Service\Game\State\DoctrineGameStateRepository;
use App\Service\Game\State\GameEventRepositoryInterface;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\VarExporter\VarExporter;

#[AsCommand(name: 'app:export:game-replay', description: 'Export a game replay to a file.')]
final class ExportGameReplayCommand
{
    private const REPLAY_FOLDER = 'replays';
    private const REPLAY_TEST_FOLDER = 'tests/GameReplay/resources';

    public function __construct(
        private GameStateRebuilder $gameStateRebuilder,
        private GameEventRepositoryInterface $gameEventRepository,
        private DoctrineGameStateRepository $gameStateRepository,
        private RoomRepository $roomRepository,
    ) {}

    public function __invoke(OutputInterface $output, InputInterface $input, #[Argument('RoomId')] ?string $roomId = null, #[Option('test')] bool $test = false)
    {
        $helper = new QuestionHelper();

        if (!$roomId) {
            $question = new Question('Please enter room to export : ');

            /** @var string $roomId */
            $roomId = $helper->ask($input, $output, $question);
        }

        $room = $this->roomRepository->find($roomId);

        if (!$room) {
            $output->writeln(sprintf('<error>Room "%s" not found.</error>', $roomId));

            return 1;
        }

        $gameState = $this->gameStateRepository->get($room);

        if (!$gameState) {
            $output->writeln(sprintf('<error>No game state found for room "%s".</error>', $roomId));

            return 1;
        }

        $dataToExport = [];
        $dataToExport['initialGameState'] = $gameState;
        $dataToExport['events'] = $this->gameEventRepository->getEventsSince(0, $roomId);

        if ($test) {
            $gameState = $this->gameStateRebuilder->rebuild($gameState, $dataToExport['events']);
            $dataToExport['finalGameState'] = new GameState(
                $gameState->player1,
                $gameState->player2,
                $gameState->lastEventId,
                $gameState->seed,
                $gameState->currentPlayer,
                $gameState->cards,
            );

            // make sure we have clean game state for the replay test
            $dataToExport['initialGameState'] = $this->gameStateRepository->get($room);
        }

        $this->exportFile($dataToExport, $roomId, $test);

        return 0;
    }

    /**
     * @param array{initialGameState: GameState, events: GameEvent[], finalGameState?: GameState} $data
     */
    private function exportFile(array $data, string $roomId, bool $test): void
    {
        $folder = $test ? self::REPLAY_TEST_FOLDER : self::REPLAY_FOLDER;
        $fileName = $test ? 'replay-'.$roomId : $roomId;

        if (!file_exists($folder)) {
            mkdir($folder, 0o777, true);
        }

        $filePath = \sprintf('%s/%s.php', $folder, $fileName);

        $content = <<<EOF
                <?php

                return

            EOF;

        $content .= VarExporter::export($data);

        $content .= ";\n";

        file_put_contents($filePath, $content);
    }
}
