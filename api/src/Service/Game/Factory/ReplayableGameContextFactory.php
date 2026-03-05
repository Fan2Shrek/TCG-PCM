<?php

declare(strict_types=1);

namespace App\Service\Game\Factory;

use App\Game\GameContext;
use App\Game\State\GameState;
use Symfony\Component\DependencyInjection\Attribute\Exclude;

#[Exclude]
final class ReplayableGameContextFactory implements GameContextFactoryInterface
{
    /**
     * @param int[] $rolls
     */
    public function __construct(
        private array $rolls = [],
    ) {}

    public function createGameContext(GameState $gameState, string $playerId): GameContext
    {
        $context = new ReplayableGameContext($gameState, $playerId);
        $context->setRollsFactory($this->getNextRolls(...));

        return $context;
    }

    private function getNextRolls(): int|float|string
    {
        return array_shift($this->rolls) ?? throw new \BadMethodCallException('No more rolls available for replay.');
    }
}

class ReplayableGameContext extends GameContext
{
    /**
     * @var \Closure(): mixed
     */
    private \Closure $rollsFactory;

    public function __construct(GameState $state, string $playerId)
    {
        parent::__construct($state, $playerId);
    }

    /**
     * @param \Closure(): mixed $rollsFactory
     */
    public function setRollsFactory(\Closure $rollsFactory): void
    {
        $this->rollsFactory = $rollsFactory;
    }

    public function rollDice(int $faces): int
    {
        return (int) ($this->rollsFactory)();
    }

    public function randomBetween(float $min, float $max): float
    {
        return floatval(($this->rollsFactory)());
    }

    public function getOneRandomCard(?string $playerId): string
    {
        return (string) ($this->rollsFactory)();
    }
}
