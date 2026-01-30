<?php

declare(strict_types=1);

namespace App\Domain\Command\Booster;

use App\Domain\Model\Booster;
use App\Game\AbstractCard;
use App\Service\BoosterGenerator;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

// @todo add tests
#[AsMessageHandler]
final class OpenBoosterHandler
{
    public function __construct(
        private BoosterGenerator $boosterGenerator,
    ) {
    }

    /**
    * @return AbstractCard[]
    */
    public function __invoke(OpenBoosterCommand $command): Booster
    {
        //@todo dispatch event here ?
        return $this->boosterGenerator->generateBooster();
    }
}
