<?php

namespace App\Controller\Shop;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/help', name: 'help_')]
class HelpController extends AbstractController
{
    /**
     * 帮助中心首页
     */
    #[Route('/', name: 'index', methods: ['GET'])]
    #[Route('/center', name: 'center', methods: ['GET'])]
    public function index(): Response
    {
        $categories = $this->getHelpCategories();
        $faqs = $this->getFAQs();

        return $this->render('shop/help/center.html.twig', [
            'categories' => json_encode($categories),
            'faqs' => json_encode($faqs),
            'pageTitle' => '帮助中心',
        ]);
    }

    /**
     * 常见问题详情
     */
    #[Route('/faq/{id}', name: 'faq_detail', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function faqDetail(int $id): Response
    {
        $faq = $this->getFAQById($id);

        if (!$faq) {
            throw $this->createNotFoundException('常见问题不存在');
        }

        $relatedFAQs = $this->getRelatedFAQs($id);

        return $this->render('shop/help/faq-detail.html.twig', [
            'faq' => json_encode($faq),
            'faqId' => $id,
            'relatedFAQs' => json_encode($relatedFAQs),
            'pageTitle' => $faq['title'] ?? '常见问题',
        ]);
    }

    /**
     * 操作指南
     */
    #[Route('/guide', name: 'guide', methods: ['GET'])]
    #[Route('/getting-started', name: 'getting_started', methods: ['GET'])]
    public function guide(): Response
    {
        $guides = $this->getOperationGuides();

        return $this->render('shop/help/guide.html.twig', [
            'guides' => json_encode($guides),
            'pageTitle' => '操作指南',
        ]);
    }

    /**
     * 联系我们
     */
    #[Route('/contact', name: 'contact', methods: ['GET', 'POST'])]
    public function contact(Request $request): Response
    {
        $contactInfo = $this->getContactInfo();

        if ($request->isMethod('POST')) {
            // 处理表单提交
            $name = $request->request->get('name');
            $email = $request->request->get('email');
            $message = $request->request->get('message');

            // 这里应该保存到数据库或发送邮件
            // 为演示目的，这里只是返回成功响应

            return $this->json([
                'success' => true,
                'message' => '感谢您的反馈，我们会尽快回复！',
            ]);
        }

        return $this->render('shop/help/contact.html.twig', [
            'contactInfo' => json_encode($contactInfo),
            'pageTitle' => '联系我们',
        ]);
    }

    /**
     * 关于 Saleyee
     */
    #[Route('/about', name: 'about', methods: ['GET'])]
    public function about(): Response
    {
        $aboutInfo = $this->getAboutInfo();

        return $this->render('shop/help/about.html.twig', [
            'aboutInfo' => json_encode($aboutInfo),
            'pageTitle' => '关于Saleyee',
        ]);
    }

    /**
     * 平台公告
     */
    #[Route('/announcements', name: 'announcements', methods: ['GET'])]
    public function announcements(): Response
    {
        $announcements = $this->getMockAnnouncements();

        return $this->render('shop/help/announcements.html.twig', [
            'announcements' => json_encode($announcements),
            'pageTitle' => '平台公告',
        ]);
    }

    /**
     * 公告详情
     */
    #[Route('/announcement/{id}', name: 'announcement_detail', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function announcementDetail(int $id): Response
    {
        $announcement = $this->getAnnouncementById($id);

        if (!$announcement) {
            throw $this->createNotFoundException('公告不存在');
        }

        $relatedAnnouncements = $this->getRelatedAnnouncements($id);

        return $this->render('shop/help/announcement-detail.html.twig', [
            'announcement' => json_encode($announcement),
            'announcementId' => $id,
            'relatedAnnouncements' => json_encode($relatedAnnouncements),
            'pageTitle' => $announcement['title'] ?? '公告详情',
        ]);
    }

    /**
     * API: 获取FAQ列表
     */
    #[Route('/api/faqs', name: 'api_faqs', methods: ['GET'])]
    public function apiFaqs(Request $request): JsonResponse
    {
        $category = $request->query->get('category');
        $search = $request->query->get('search');

        $faqs = $this->getFAQs();

        // 过滤
        if ($category) {
            $faqs = array_filter($faqs, function ($faq) use ($category) {
                return $faq['category'] === $category;
            });
        }

        if ($search) {
            $search = strtolower($search);
            $faqs = array_filter($faqs, function ($faq) use ($search) {
                return strpos(strtolower($faq['title']), $search) !== false ||
                       strpos(strtolower($faq['content']), $search) !== false;
            });
        }

        return $this->json([
            'success' => true,
            'data' => array_values($faqs),
            'total' => count($faqs),
        ]);
    }

    /**
     * API: 获取帮助分类
     */
    #[Route('/api/categories', name: 'api_help_categories', methods: ['GET'])]
    public function apiCategories(): JsonResponse
    {
        $categories = $this->getHelpCategories();

        return $this->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    // ==================== 辅助方法 ====================

    private function getHelpCategories(): array
    {
        return [
            ['id' => 1, 'name' => '账户管理', 'icon' => '👤', 'count' => 12],
            ['id' => 2, 'name' => '商品相关', 'icon' => '📦', 'count' => 18],
            ['id' => 3, 'name' => '订单物流', 'icon' => '🚚', 'count' => 15],
            ['id' => 4, 'name' => '支付结算', 'icon' => '💳', 'count' => 10],
            ['id' => 5, 'name' => '售后服务', 'icon' => '🔧', 'count' => 14],
            ['id' => 6, 'name' => '平台规则', 'icon' => '📋', 'count' => 8],
        ];
    }

    private function getFAQs(): array
    {
        return [
            [
                'id' => 1,
                'title' => '如何注册账户？',
                'content' => '点击右上角"注册"按钮，填写邮箱和密码即可完成注册。...',
                'category' => '账户管理',
                'views' => 1250,
                'helpful' => 856,
                'createdAt' => '2024-01-15',
            ],
            [
                'id' => 2,
                'title' => '如何修改账户信息？',
                'content' => '登录后进入"个人中心"，点击"基本信息"即可修改...',
                'category' => '账户管理',
                'views' => 892,
                'helpful' => 745,
                'createdAt' => '2024-01-16',
            ],
            [
                'id' => 3,
                'title' => '商品上架需要哪些资料？',
                'content' => '需要提供商品名称、描述、图片、价格等基本信息...',
                'category' => '商品相关',
                'views' => 1450,
                'helpful' => 1120,
                'createdAt' => '2024-01-17',
            ],
            [
                'id' => 4,
                'title' => '订单如何发货？',
                'content' => '订单确认后，在"我的订单"中选择订单，点击"发货"...',
                'category' => '订单物流',
                'views' => 2100,
                'helpful' => 1890,
                'createdAt' => '2024-01-18',
            ],
            [
                'id' => 5,
                'title' => '支持哪些支付方式？',
                'content' => '目前支持支付宝、微信支付、银行卡等多种方式...',
                'category' => '支付结算',
                'views' => 1680,
                'helpful' => 1450,
                'createdAt' => '2024-01-19',
            ],
        ];
    }

    private function getFAQById(int $id): ?array
    {
        $faqs = $this->getFAQs();
        foreach ($faqs as $faq) {
            if ($faq['id'] === $id) {
                $faq['views'] += 1; // 增加浏览数
                return $faq;
            }
        }
        return null;
    }

    private function getRelatedFAQs(int $currentId): array
    {
        $faqs = $this->getFAQs();
        $related = [];
        foreach ($faqs as $faq) {
            if ($faq['id'] !== $currentId) {
                $related[] = $faq;
            }
            if (count($related) >= 3) {
                break;
            }
        }
        return $related;
    }

    private function getOperationGuides(): array
    {
        return [
            [
                'id' => 1,
                'title' => '新手入门指南',
                'description' => '快速了解平台基本操作',
                'sections' => [
                    ['title' => '第一步：注册账户', 'content' => '...'],
                    ['title' => '第二步：完善信息', 'content' => '...'],
                    ['title' => '第三步：首次发布商品', 'content' => '...'],
                ],
            ],
            [
                'id' => 2,
                'title' => '商品管理完全指南',
                'description' => '学习如何有效管理您的商品',
                'sections' => [
                    ['title' => '��传商品', 'content' => '...'],
                    ['title' => '编辑商品信息', 'content' => '...'],
                    ['title' => '管理库存', 'content' => '...'],
                ],
            ],
        ];
    }

    private function getContactInfo(): array
    {
        return [
            'email' => 'support@saleyee.com',
            'phone' => '+86-800-123-4567',
            'wechat' => 'saleyee_support',
            'qq' => '123456789',
            'address' => '北京市朝阳区某某街道123号',
            'workingHours' => '周一至周五 9:00-18:00',
        ];
    }

    private function getAboutInfo(): array
    {
        return [
            'companyName' => 'Saleyee',
            'description' => '我们是一个领先的在线交易平台...',
            'founded' => '2015年',
            'team' => '500+专业团队',
            'users' => '100万+活跃用户',
            'mission' => '让交易更简单，让生意更好做',
            'values' => ['诚实', '专业', '创新', '服务'],
        ];
    }

    private function getMockAnnouncements(): array
    {
        return [
            [
                'id' => 1,
                'title' => '系统维护通知',
                'content' => '平台将于...',
                'type' => 'system',
                'importance' => 'high',
                'createdAt' => '2024-01-20',
            ],
            [
                'id' => 2,
                'title' => '新功能上线：直发物流',
                'content' => '我们新上线了...',
                'type' => 'feature',
                'importance' => 'normal',
                'createdAt' => '2024-01-19',
            ],
        ];
    }

    private function getAnnouncementById(int $id): ?array
    {
        $announcements = $this->getMockAnnouncements();
        foreach ($announcements as $announcement) {
            if ($announcement['id'] === $id) {
                return $announcement;
            }
        }
        return null;
    }

    private function getRelatedAnnouncements(int $currentId): array
    {
        $announcements = $this->getMockAnnouncements();
        $related = [];
        foreach ($announcements as $announcement) {
            if ($announcement['id'] !== $currentId) {
                $related[] = $announcement;
            }
            if (count($related) >= 3) {
                break;
            }
        }
        return $related;
    }
}
