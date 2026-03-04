<?php

declare(strict_types=1);

namespace App\Tests\Unit\Game\Card\Trait;

use App\Game\AbstractCard;
use App\Game\Card\CardState;
use App\Game\Card\Trait\BaseOnTurnTrait;
use App\Game\GameContext;
use PHPUnit\Framework\TestCase;

final class BaseOnTurnTraitTest extends TestCase
{
    public function testNoAction()
    {
        $card = new TestCard();
        $gameContext = $this->createStub(GameContext::class);
        $card->setState(new CardState(
            '',
            TestCard::class,
            '',
            [],
            [],
        ));

        $card->onTurnStart($gameContext);

        self::assertFalse($card::$actionExecuted);
    }

    public function testAction()
    {
        $card = new TestCard();
        $gameContext = $this->createStub(GameContext::class);

        $card->setState(new CardState(
            '',
            TestCard::class,
            '',
            [],
            [
                'turnRemainingBeforeAction' => 1,
            ],
        ));

        $card->onTurnStart($gameContext);

        self::assertTrue($card::$actionExecuted);
    }
}

class TestCard extends AbstractCard
{
    use BaseOnTurnTrait;

    public static bool $actionExecuted = false;

    public function getId(): string
    {
        return self::class;
    }

    public function getDescription(): string
    {
        return '';
    }

    public function getName(): string
    {
        return '';
    }

    public function getTurnDelay(): int
    {
        return 5;
    }

    public function getInstanceId(): ?string
    {
        return (string) spl_object_id($this);
    }

    public function onTurnAction(GameContext $gameContext): void
    {
        self::$actionExecuted = true;
    }

    public function setState(CardState $state): void
    {
        parent::setState($state);

        $this->initFromState($state);
    }
}
