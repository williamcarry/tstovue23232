<?php

namespace App\Security;

use App\Entity\Supplier;
use App\Entity\SupplierSubAccount;
use App\Repository\SupplierRepository;
use App\Repository\SupplierSubAccountRepository;
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

class SupplierLoginAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login_supplier';

    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private SupplierRepository $supplierRepository,
        private SupplierSubAccountRepository $supplierSubAccountRepository
    ) {
    }

    public function authenticate(Request $request): Passport
    {
        $username = $request->request->get('_username', '');
        $captcha = $request->request->get('_captcha', '');

        $request->getSession()->set('_security.last_username', $username);

        // 添加调试信息
        error_log("Supplier login attempt - Username: " . $username . ", Captcha: " . $captcha);

        // 验证验证码
        $sessionCaptcha = $request->getSession()->get('captcha_code');
        error_log("Session captcha: " . ($sessionCaptcha ?? 'null') . ", Submitted captcha: " . $captcha);
        
        // 检查验证码
        if (!$captcha || strtoupper($captcha) !== strtoupper($sessionCaptcha ?? '')) {
            error_log("Captcha validation failed for supplier login. Session: " . ($sessionCaptcha ?? 'null') . ", Submitted: " . $captcha);
            throw new CustomUserMessageAuthenticationException('验证码错误');
        }

        // 检查用户试图通过哪个登录路径登录
        $pathInfo = $request->getPathInfo();
        error_log("Path info: " . $pathInfo);
        
        // 验证路径是否正确
        $expectedPath = '/supplier' . PathConfigService::getSupplierLoginPath() . '/login';
        error_log("Expected path: " . $expectedPath);
        
        if (strpos($pathInfo, $expectedPath) !== 0) {
            error_log("Invalid supplier login path. Expected: " . $expectedPath . ", Got: " . $pathInfo);
            throw new BadCredentialsException('Invalid supplier login path.');
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
        error_log("Loading supplier user: " . $username);
        
        // 首先尝试加载主账号
        $supplier = $this->supplierRepository->findOneBy(['username' => $username]);
        
        if ($supplier) {
            error_log("Supplier user found: " . $username . ", Active: " . ($supplier->isActive() ? 'yes' : 'no') . ", Audit Status: " . $supplier->getAuditStatus());
            
            // 检查主账号是否激活
            if (!$supplier->isActive()) {
                throw new CustomUserMessageAuthenticationException('账号未激活');
            }
            
            return $supplier;
        }
        
        // 如果主账号不存在，尝试加载子账号
        $subAccount = $this->supplierSubAccountRepository->findOneBy(['username' => $username]);
        
        if ($subAccount) {
            error_log("Supplier sub-account found: " . $username . ", Active: " . ($subAccount->isActive() ? 'yes' : 'no'));
            
            // 检查子账号是否激活
            if (!$subAccount->isActive()) {
                throw new CustomUserMessageAuthenticationException('子账号未激活');
            }
            
            // 获取关联的主账号
            $mainSupplier = $subAccount->getSupplier();
            
            if (!$mainSupplier) {
                throw new CustomUserMessageAuthenticationException('子账号关联的主账号不存在');
            }
            
            error_log("Main supplier for sub-account: " . $mainSupplier->getUsername() . ", Active: " . ($mainSupplier->isActive() ? 'yes' : 'no') . ", Audit Status: " . $mainSupplier->getAuditStatus());
            
            // 检查主账号是否激活
            if (!$mainSupplier->isActive()) {
                throw new CustomUserMessageAuthenticationException('主账号未激活，子账号无法登录');
            }
            
            return $subAccount;
        }
        
        error_log("Supplier user not found: " . $username);
        throw new UserNotFoundException(sprintf('Supplier "%s" not found.', $username));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        error_log("Supplier authentication successful");
        
        // 设置最后登录时间
        $user = $token->getUser();
        if ($user instanceof Supplier) {
            $user->setLastLoginAt(new \DateTime());
            // 保存用户到数据库
            $this->supplierRepository->save($user, true);
        } elseif ($user instanceof SupplierSubAccount) {
            $user->setLastLoginAt(new \DateTime());
            // 保存子账号到数据库
            $this->supplierSubAccountRepository->save($user, true);
        }
        
        // 根据用户角色重定向到相应的后台
        $redirectUrl = $this->urlGenerator->generate('supplier_dashboard', ['supplierLoginPath' => PathConfigService::getSupplierLoginPath()]);
        error_log("Redirecting to: " . $redirectUrl);
        return new RedirectResponse($redirectUrl);
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate('app_login_supplier', ['supplierLoginPath' => PathConfigService::getSupplierLoginPath()]);
    }
}