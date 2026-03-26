<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Game\Pipeline\Middleware;

use App\Game\Exception\NotYourTurnException;
use App\Game\Exception\UnknowActionException;
use App\Game\Player;
use App\Game\PlayerAction;
use App\Game\State\GameState;
use App\Game\State\PlayArea;
use App\Game\State\PlayerState;
use App\Service\Game\Pipeline\GamePipelineContext;
use App\Service\Game\Pipeline\GamePipelineMiddlewareStack;
use App\Service\Game\Pipeline\Middleware\ValidateActionMiddleware;
use PHPUnit\Framework\TestCase;

final class ValidateActionMiddlewareTest extends TestCase
{
    public function testValidateUnknowAction()
    {
        self::expectException(UnknowActionException::class);

        $sut = $this->getSut();
        $gpc = new GamePipelineContext(new PlayerAction('', '', 'gameId', []));

        $sut->handle($gpc, new GamePipelineMiddlewareStack([]));
    }

    public function testNotYourTurn()
    {
        self::expectException(NotYourTurnException::class);

        $sut = $this->getSut();
        $gpc = new GamePipelineContext(new PlayerAction('', PlayerAction::END_TURN, 'gameId', []));
        $state = new GameState(
            new PlayerState(new Player('1', 'otherUsername'), 0, 0, '', [], [], 0, new PlayArea()),
            new PlayerState(new Player('2', 'otherUsername'), 0, 0, '', [], [], 0, new PlayArea()),
            null,
            0,
            '2',
        );
        $gpc->setGameState($state);

        $sut->handle($gpc, new GamePipelineMiddlewareStack([]));
    }

    public function testInvalidCardId()
    {
        self::expectException(\LogicException::class);

        $sut = $this->getSut();
        $gpc = new GamePipelineContext(new PlayerAction('1', PlayerAction::PLAY_CARD, 'gameId', ['cardId' => 'invalidCardId']));
        $state = new GameState(
            new PlayerState(new Player('1', 'otherUsername'), 1, 1, '', ['cardId'], [], 0, new PlayArea()),
            new PlayerState(new Player('2', 'otherUsername'), 1, 1, '', [], [], 0, new PlayArea()),
            null,
            0,
            '1',
            [
                'cardId' => '',
            ],
        );
        $gpc->setGameState($state);

        $sut->handle($gpc, new GamePipelineMiddlewareStack([]));
    }

    public function testOk()
    {
        $sut = $this->getSut();
        $gpc = new GamePipelineContext(new PlayerAction('1', PlayerAction::END_TURN, 'gameId', []));
        $state = new GameState(
            new PlayerState(new Player('1', 'otherUsername'), 1, 1, '', [], [], 0, new PlayArea()),
            new PlayerState(new Player('2', 'otherUsername'), 1, 1, '', [], [], 0, new PlayArea()),
            null,
            0,
            '1',
        );
        $gpc->setGameState($state);

        $sut->handle($gpc, new GamePipelineMiddlewareStack([]));

        self::expectNotToPerformAssertions();
    }

    private function getSut(): ValidateActionMiddleware
    {
        return new ValidateActionMiddleware();
    }
}
