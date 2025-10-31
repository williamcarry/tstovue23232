<?php

namespace App\Security;

use App\Entity\Supplier;
use App\Entity\SupplierSubAccount;
use App\Repository\SupplierRepository;
use App\Repository\SupplierSubAccountRepository;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class SupplierUserProvider implements UserProviderInterface
{
    public function __construct(
        private SupplierRepository $supplierRepository,
        private SupplierSubAccountRepository $supplierSubAccountRepository
    )
    {
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        // 首先尝试加载主账号
        $supplier = $this->supplierRepository->findOneBy(['username' => $identifier]);
        
        if ($supplier) {
            return $supplier;
        }
        
        // 如果主账号不存在，尝试加载子账号
        $subAccount = $this->supplierSubAccountRepository->findOneBy(['username' => $identifier]);
        
        if ($subAccount) {
            return $subAccount;
        }
        
        throw new UserNotFoundException(sprintf('Supplier or sub-account "%s" not found.', $identifier));
    }

    public function loadUserByUsername(string $username): UserInterface
    {
        return $this->loadUserByIdentifier($username);
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if ($user instanceof Supplier) {
            $supplier = $this->supplierRepository->find($user->getId());
            
            if (!$supplier) {
                throw new UserNotFoundException(sprintf('Supplier with id "%s" not found.', $user->getId()));
            }
            
            return $supplier;
        }
        
        if ($user instanceof SupplierSubAccount) {
            $subAccount = $this->supplierSubAccountRepository->find($user->getId());
            
            if (!$subAccount) {
                throw new UserNotFoundException(sprintf('Supplier sub-account with id "%s" not found.', $user->getId()));
            }
            
            return $subAccount;
        }
        
        throw new \Symfony\Component\Security\Core\Exception\UnsupportedUserException(sprintf('Invalid user class "%s".', get_class($user)));
    }

    public function supportsClass(string $class): bool
    {
        return Supplier::class === $class || is_subclass_of($class, Supplier::class) ||
               SupplierSubAccount::class === $class || is_subclass_of($class, SupplierSubAccount::class);
    }
}