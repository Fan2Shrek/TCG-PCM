<?php

declare(strict_types=1);

namespace App\Domain\Command\User;

use App\Service\Auth\CurrentUserProviderInterface;
use App\Service\User\UserProfilePictureUpdater;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class SetProfilePictureHandler
{
    public function __construct(
        private UserProfilePictureUpdater $userProfilePictureUpdater,
        private CurrentUserProviderInterface $currentUserProvider,
    ) {}

    public function __invoke(SetProfilePictureCommand $command): array
    {
        $user = $this->currentUserProvider->getCurrentUser();

        return [
            'profilePicturePath' => $this->userProfilePictureUpdater->update($user, $command->profilePicture),
        ];
    }
}
