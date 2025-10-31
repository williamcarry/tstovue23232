<?php

namespace App\Security;

use App\Entity\Admin;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class AdminActiveVoter extends Voter
{
    protected function supports(string $attribute, $subject): bool
    {
        return $attribute === 'ADMIN_ACTIVE';
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof Admin) {
            return false;
        }

        return $user->isActive();
    }
}