<?php

namespace App\Controller\Admin;

use App\Entity\Admin;
use App\Repository\AdminRepository;
use App\Service\PathConfigService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin{adminLoginPath}', requirements: ['adminLoginPath' => '[a-zA-Z0-9]+'])]
#[IsGranted('ADMIN_ACTIVE')]
class DashboardController extends AbstractController
{
    #[Route('/admin', name: 'admin_dashboard')]
    #[Route('/admin/dashboard', name: 'admin_dashboard_alt')]
    public function index(string $adminLoginPath)
    {
        // 获取当前管理员用户
        /** @var Admin $admin */
        $admin = $this->getUser();
        
        // 准备管理员数据
        $adminData = [
            'username' => $admin->getUsername(),
            'email' => $admin->getEmail(),
            'fullName' => $admin->getMark()
        ];
        
        return $this->render('admin/dashboard.html.twig', [
            'dashboard_title' => '总后台',
            'user_name' => $admin->getMark() ?: $admin->getUsername(),
            'admin_data' => $adminData
        ]);
    }
}