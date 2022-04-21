<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Security\UserRole;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class UserVoter extends Voter
{
    public function __construct(
        private readonly AuthorizationCheckerInterface $authorizationChecker,
    ) {
    }

    protected function supports(string $attribute, $subject): bool
    {
        return str_starts_with($attribute, 'CAN_');
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (! $user instanceof UserInterface) {
            return false;
        }

        if ($this->authorizationChecker->isGranted(UserRole::ROLE_ADMIN)) {
            return true;
        }

        foreach ($user->getRoles() as $role) {
            foreach (UserRole::PERMISSIONS_PER_ROLE[$role] as $permission) {
                if ($attribute === $permission) {
                    return true;
                }
            }
        }

        return false;
    }
}
