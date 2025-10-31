<?php

namespace App\Security;

use App\Entity\Admin;
use App\Repository\AdminRepository;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class AdminUserProvider implements UserProviderInterface
{
    public function __construct(private AdminRepository $adminRepository)
    {
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $admin = $this->adminRepository->findOneBy(['username' => $identifier]);
        
        if (!$admin) {
            throw new UserNotFoundException(sprintf('Admin "%s" not found.', $identifier));
        }
        
        return $admin;
    }

    public function loadUserByUsername(string $username): UserInterface
    {
        return $this->loadUserByIdentifier($username);
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof Admin) {
            throw new \Symfony\Component\Security\Core\Exception\UnsupportedUserException(sprintf('Invalid user class "%s".', get_class($user)));
        }

        $admin = $this->adminRepository->find($user->getId());
        
        if (!$admin) {
            throw new UserNotFoundException(sprintf('Admin with id "%s" not found.', $user->getId()));
        }
        
        return $admin;
    }

    public function supportsClass(string $class): bool
    {
        return Admin::class === $class || is_subclass_of($class, Admin::class);
    }
}