<?php

declare(strict_types=1);

namespace App\Interface;

interface DeployAwareInterface
{
    public function onDeploy(): void;
}
