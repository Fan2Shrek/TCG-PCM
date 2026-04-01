<?php

declare(strict_types=1);

namespace App\Domain\Command\User;

use App\Service\Auth\CurrentUserProviderInterface;
use App\Service\User\UserGenerateBoosterTokens;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class GenerateBoosterTokensHandler
{
    public function __construct(
        private UserGenerateBoosterTokens $userGenerateBoosterTokens,
        private CurrentUserProviderInterface $currentUserProvider,
    ) {}

    public function __invoke(GenerateBoosterTokensCommand $generate): array
    {
        $user = $this->currentUserProvider->getCurrentUser();

        return [
            'tokens' => $this->userGenerateBoosterTokens->generate($user),
        ];
    }
}
