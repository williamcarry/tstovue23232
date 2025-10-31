<?php

namespace App\Security;

use App\Entity\Admin;
use App\Repository\AdminRepository;
use App\Service\PathConfigService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class AdminLoginAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login_admin';

    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private AdminRepository $adminRepository
    ) {
    }

    public function authenticate(Request $request): Passport
    {
        $username = $request->request->get('_username', '');
        $captcha = $request->request->get('_captcha', '');

        $request->getSession()->set('_security.last_username', $username);

        // 添加调试信息
        error_log("Admin login attempt - Username: " . $username . ", Captcha: " . $captcha);

        // 验证验证码
        $sessionCaptcha = $request->getSession()->get('captcha_code');
        error_log("Session captcha: " . ($sessionCaptcha ?? 'null') . ", Submitted captcha: " . $captcha);
        
        // 检查验证码
        if (!$captcha || strtoupper($captcha) !== strtoupper($sessionCaptcha ?? '')) {
            error_log("Captcha validation failed for admin login");
            throw new CustomUserMessageAuthenticationException('验证码错误');
        }

        // 检查用户试图通过哪个登录路径登录
        $pathInfo = $request->getPathInfo();
        error_log("Path info: " . $pathInfo);
        
        // 验证路径是否正确
        $expectedPath = '/admin' . PathConfigService::getAdminLoginPath() . '/login';
        error_log("Expected path: " . $expectedPath);
        
        if (strpos($pathInfo, $expectedPath) !== 0) {
            error_log("Invalid admin login path. Expected: " . $expectedPath . ", Got: " . $pathInfo);
            throw new BadCredentialsException('Invalid admin login path.');
        }

        // 创建用户徽章，使用回调函数加载用户
        $userBadge = new UserBadge($username, function ($username) {
            // 这个回调函数将在需要时被调用以加载用户
            return $this->loadUserByUsername($username);
        });

        return new Passport(
            $userBadge,
            new PasswordCredentials($request->request->get('_password', '')),
            [
                new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
            ]
        );
    }

    private function loadUserByUsername(string $username): UserInterface
    {
        error_log("Loading admin user: " . $username);
        
        // 从数据库加载管理员用户
        $admin = $this->adminRepository->findOneBy(['username' => $username]);
        
        if (!$admin) {
            error_log("Admin user not found: " . $username);
            throw new UserNotFoundException(sprintf('Admin "%s" not found.', $username));
        }
        
        error_log("Admin user found: " . $username . ", Active: " . ($admin->isActive() ? 'yes' : 'no'));
        
        // 检查用户是否激活
        if (!$admin->isActive()) {
            throw new CustomUserMessageAuthenticationException('账号被禁用');
        }
        
        return $admin;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        error_log("Admin authentication successful");
        
        // 设置最后登录时间
        $user = $token->getUser();
        if ($user instanceof Admin) {
            $user->setLastLoginAt(new \DateTime());
            // 保存用户到数据库
            $this->adminRepository->save($user, true);
        }
        
        // 根据用户角色重定向到相应的后台
        $redirectUrl = $this->urlGenerator->generate('admin_dashboard', ['adminLoginPath' => PathConfigService::getAdminLoginPath()]);
        error_log("Redirecting to: " . $redirectUrl);
        return new RedirectResponse($redirectUrl);
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate('app_login_admin', ['adminLoginPath' => PathConfigService::getAdminLoginPath()]);
    }
}