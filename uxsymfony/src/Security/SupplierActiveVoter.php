<?php

namespace App\Security;

use App\Entity\Supplier;
use App\Entity\SupplierSubAccount;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class SupplierActiveVoter extends Voter
{
    protected function supports(string $attribute, $subject): bool
    {
        return $attribute === 'SUPPLIER_ACTIVE';
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // 检查主账号
        if ($user instanceof Supplier) {
            // 检查供应商是否激活
            return $user->isActive();
        }
        
        // 检查子账号
        if ($user instanceof SupplierSubAccount) {
            // 检查子账号是否激活
            if (!$user->isActive()) {
                return false;
            }
            
            // 获取关联的主账号
            $mainSupplier = $user->getSupplier();
            
            if (!$mainSupplier) {
                return false;
            }
            
            // 检查主账号是否激活
            return $mainSupplier->isActive();
        }

        return false;
    }
}