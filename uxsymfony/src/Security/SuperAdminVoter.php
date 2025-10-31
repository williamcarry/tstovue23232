<?php

namespace App\Security;

use App\Entity\Admin;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Bundle\SecurityBundle\Security;

class SuperAdminVoter extends Voter
{
    public const SUPER_ADMIN = 'SUPER_ADMIN';

    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, $subject): bool
    {
        return $attribute === self::SUPER_ADMIN;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof Admin) {
            // 用户未登录或不是管理员
            return false;
        }

        // 检查用户是否拥有超级管理员角色
        return in_array('ROLE_SUPER_ADMIN', $user->getRoles());
    }
}