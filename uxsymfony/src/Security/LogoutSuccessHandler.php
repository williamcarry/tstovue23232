<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class LogoutSuccessHandler implements LogoutSuccessHandlerInterface
{
    // 管理员登录路径
    private const ADMIN_LOGIN_PATH = '2ed4f5a3deec5fa9675b5345ffb9e5f2';
    
    // 供应商登录路径
    private const SUPPLIER_LOGIN_PATH = 'ea8ecb91b44d465ed530b17cfd3affd5';

    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    public function onLogoutSuccess(Request $request): RedirectResponse
    {
        // 从会话中获取用户角色信息（如果有的话）
        $session = $request->getSession();
        $userRoles = $session->get('_security.last_roles', []);
        
        // 根据用户角色重定向到相应的登录页面
        if (in_array('ROLE_ADMIN', $userRoles)) {
            return new RedirectResponse($this->urlGenerator->generate('app_login_admin'));
        }
        
        if (in_array('ROLE_SUPPLIER', $userRoles)) {
            return new RedirectResponse($this->urlGenerator->generate('app_login_supplier'));
        }

        // 默认重定向到主页
        return new RedirectResponse($this->urlGenerator->generate('app_homepage'));
    }
}