<?php

declare(strict_types=1);

namespace App\Domain\Command\User;

use App\Service\Auth\CurrentUserProviderInterface;
use App\Service\User\UserGenerateBoosterTokens;

final class GenerateBoosterTokensHandler{

    public function __construct(
        private UserGenerateBoosterTokens $userGenerateBoosterTokens,
        private CurrentUserProviderInterface $currentUserProvider,
    ) {}

    public function __invoke(GenerateBoosterTokensCommand $generate): int
    {
        $user = $this->currentUserProvider->getCurrentUser();
        return $this->userGenerateBoosterTokens->generate($user);
    }
}
