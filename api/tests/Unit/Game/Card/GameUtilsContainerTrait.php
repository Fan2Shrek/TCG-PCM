<?php

declare(strict_types=1);

namespace App\Tests\Unit\Game\Card;

use App\Game\GameUtils;
use Psr\Container\ContainerInterface;

/**
 * Saves/restores GameUtils' static container around a test so that stubbing
 * a fake container for one test class doesn't leak into unrelated tests run
 * later in the same PHPUnit process.
 */
trait GameUtilsContainerTrait
{
    private mixed $previousGameUtilsContainer = null;
    private bool $hadPreviousGameUtilsContainer = false;

    protected function setGameUtilsContainer(ContainerInterface $container): void
    {
        $property = new \ReflectionProperty(GameUtils::class, 'container');
        $this->hadPreviousGameUtilsContainer = $property->isInitialized();
        if ($this->hadPreviousGameUtilsContainer) {
            $this->previousGameUtilsContainer = $property->getValue();
        }

        GameUtils::setContainer($container);
    }

    protected function restoreGameUtilsContainer(): void
    {
        if (!$this->hadPreviousGameUtilsContainer) {
            return;
        }

        GameUtils::setContainer($this->previousGameUtilsContainer);
    }
}
