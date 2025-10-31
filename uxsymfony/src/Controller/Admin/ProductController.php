<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Repository\SupplierRepository;
use App\Service\PathConfigService;
use App\Service\QiniuUploadService;
use App\Service\LogisticsCompanyService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin{adminLoginPath}', requirements: ['adminLoginPath' => '[a-zA-Z0-9]+'])]
#[IsGranted('ADMIN_ACTIVE')]
class ProductController extends AbstractController
{
    /**
     * 商品列表页面
     */
    #[Route('/product/list', name: 'admin_product_list')]
    public function list(string $adminLoginPath): Response
    {
        // 权限检查
        if (!$this->isGranted('ROLE_SUPER_ADMIN') && !$this->isGranted('ROLE_ADMIN_PRODUCT')) {
            throw $this->createAccessDeniedException('Access Denied.');
        }

        // 验证路径是否正确
        if ($adminLoginPath !== PathConfigService::getAdminLoginPath()) {
            throw $this->createNotFoundException('Invalid admin path');
        }

        return $this->render('admin/product/list.html.twig', [
            'adminLoginPath' => $adminLoginPath,
        ]);
    }

    /**
     * 查看商品页面
     */
    #[Route('/product/view/{id}', name: 'admin_product_view')]
    public function view(int $id, string $adminLoginPath): Response
    {
        // 权限检查
        if (!$this->isGranted('ROLE_SUPER_ADMIN') && !$this->isGranted('ROLE_ADMIN_PRODUCT')) {
            throw $this->createAccessDeniedException('Access Denied.');
        }

        // 验证路径是否正确
        if ($adminLoginPath !== PathConfigService::getAdminLoginPath()) {
            throw $this->createNotFoundException('Invalid admin path');
        }

        return $this->render('admin/product/view.html.twig', [
            'adminLoginPath' => $adminLoginPath,
            'productId' => $id
        ]);
    }

    /**
     * 获取商品列表数据（AJAX）
     */
    #[Route('/product/list/data', name: 'admin_product_list_data', methods: ['GET'])]
    public function listData(
        Request $request,
        ProductRepository $productRepository,
        string $adminLoginPath
    ): JsonResponse {
        // 权限检查
        if (!$this->isGranted('ROLE_SUPER_ADMIN') && !$this->isGranted('ROLE_ADMIN_PRODUCT')) {
            return new JsonResponse(['success' => false, 'message' => 'Access Denied'], 403);
        }

        // 验证路径是否正确
        if ($adminLoginPath !== PathConfigService::getAdminLoginPath()) {
            return new JsonResponse(['success' => false, 'message' => 'Invalid admin path'], 404);
        }

        // 获取分页参数
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 20);

        // 获取搜索筛选参数
        $searchParams = [
            'keyword' => $request->query->get('keyword', ''),
            'status' => $request->query->get('status', ''),
            'supplierId' => $request->query->get('supplierId', ''),
            'supplierUsername' => $request->query->get('supplierUsername', ''), // 添加供应商用户名参数
            'categoryId' => $request->query->get('categoryId', ''),
            'subcategoryId' => $request->query->get('subcategoryId', ''),
            'itemId' => $request->query->get('itemId', ''),
            'sortField' => $request->query->get('sortField', 'createdAt'),
            'sortOrder' => $request->query->get('sortOrder', 'DESC'),
        ];

        // 调用Repository的分页查询方法
        $result = $productRepository->findPaginatedForAdmin($searchParams, $page, $limit);

        // 创建七牛云服务实例
        $qiniuService = new QiniuUploadService();

        // 格式化商品数据
        $productsData = [];
        foreach ($result['data'] as $product) {
            // 处理主图数组
            $mainImageKeys = $product->getMainImages();
            $mainImages = [];
            $mainImageUrl = '';
            $mainImageKey = '';
            
            if (is_array($mainImageKeys) && count($mainImageKeys) > 0) {
                // 新格式：主图数组
                foreach ($mainImageKeys as $key) {
                    if ($key) {
                        // 处理旧数据（如果存的是完整URL，提取key）
                        if (str_starts_with($key, 'http')) {
                            $parts = parse_url($key);
                            $key = ltrim($parts['path'] ?? '', '/');
                            $key = preg_replace('/\?.*$/', '', $key);
                        }
                        // 生成签名URL
                        $signedUrl = $qiniuService->getPrivateUrl($key);
                        $mainImages[] = [
                            'url' => $signedUrl,
                            'imageKey' => $key
                        ];
                        
                        // 设置第一张图片作为主图（向后兼容）
                        if (empty($mainImageUrl)) {
                            $mainImageUrl = $signedUrl;
                            $mainImageKey = $key;
                        }
                    }
                }
            } else {
                // 旧格式：单个主图
                $mainImageKey = $product->getMainImage();
                if ($mainImageKey) {
                    // 处理旧数据（如果存的是完整URL，提取key）
                    if (str_starts_with($mainImageKey, 'http')) {
                        $parts = parse_url($mainImageKey);
                        $mainImageKey = ltrim($parts['path'] ?? '', '/');
                        $mainImageKey = preg_replace('/\?.*$/', '', $mainImageKey);
                    }
                    // 生成签名URL
                    $mainImageUrl = $qiniuService->getPrivateUrl($mainImageKey);
                    // 转换为新格式
                    $mainImages[] = [
                        'url' => $mainImageUrl,
                        'imageKey' => $mainImageKey
                    ];
                }
            }

            $productsData[] = [
                'id' => $product->getId(),
                'sku' => $product->getSku(),
                'spu' => $product->getSpu(),
                'title' => $product->getTitle(),
                'titleEn' => $product->getTitleEn(),
                'mainImage' => $mainImageUrl,  // 向后兼容
                'mainImageKey' => $mainImageKey,  // 向后兼容
                'mainImages' => $mainImages,  // 新格式
                'status' => $product->getStatus(),
                'statusText' => $this->getStatusText($product->getStatus()),
                'supplier' => $product->getSupplier() ? [
                    'id' => $product->getSupplier()->getId(),
                    'username' => $product->getSupplier()->getUsername(),
                    'companyName' => $product->getSupplier()->getCompanyName(),
                ] : null,
                'category' => $product->getCategory() ? $product->getCategory()->getTitle() : '',
                'subcategory' => $product->getSubcategory() ? $product->getSubcategory()->getTitle() : '',
                'item' => $product->getItem() ? $product->getItem()->getTitle() : '',
                'brand' => $product->getBrand(),
                'viewCount' => $product->getViewCount(),
                'salesCount' => $product->getSalesCount(),
                'createdAt' => $product->getCreatedAt() ? $product->getCreatedAt()->format('Y-m-d H:i:s') : '',
                'updatedAt' => $product->getUpdatedAt() ? $product->getUpdatedAt()->format('Y-m-d H:i:s') : '',
                // 添加审核相关信息
                'auditRemark' => $product->getAuditRemark(),
                'auditedBy' => $product->getAuditedBy(),
                'auditedAt' => $product->getAuditedAt() ? $product->getAuditedAt()->format('Y-m-d H:i:s') : '',
                'publishDate' => $product->getPublishDate() ? $product->getPublishDate()->format('Y-m-d H:i:s') : '',
            ];
        }

        return new JsonResponse([
            'success' => true,
            'data' => $productsData,
            'pagination' => [
                'currentPage' => $result['page'],
                'totalPages' => $result['totalPages'],
                'totalItems' => $result['total'],
                'itemsPerPage' => $result['limit'],
            ]
        ]);
    }

    /**
     * 获取商品统计数据
     */
    #[Route('/product/statistics', name: 'admin_product_statistics', methods: ['GET'])]
    public function statistics(
        ProductRepository $productRepository,
        string $adminLoginPath
    ): JsonResponse {
        // 权限检查
        if (!$this->isGranted('ROLE_SUPER_ADMIN') && !$this->isGranted('ROLE_ADMIN_PRODUCT')) {
            return new JsonResponse(['success' => false, 'message' => 'Access Denied'], 403);
        }

        // 验证路径是否正确
        if ($adminLoginPath !== PathConfigService::getAdminLoginPath()) {
            return new JsonResponse(['success' => false, 'message' => 'Invalid admin path'], 404);
        }

        $statistics = $productRepository->getAdminStatistics();

        return new JsonResponse([
            'success' => true,
            'data' => $statistics
        ]);
    }

    /**
     * 审核商品
     */
    #[Route('/product/audit/{id}', name: 'admin_product_audit', methods: ['POST'])]
    public function audit(
        int $id,
        Request $request,
        ProductRepository $productRepository,
        EntityManagerInterface $entityManager,
        string $adminLoginPath
    ): JsonResponse {
        // 权限检查
        if (!$this->isGranted('ROLE_SUPER_ADMIN') && !$this->isGranted('ROLE_ADMIN_PRODUCT')) {
            return new JsonResponse(['success' => false, 'message' => 'Access Denied'], 403);
        }

        // 验证路径是否正确
        if ($adminLoginPath !== PathConfigService::getAdminLoginPath()) {
            return new JsonResponse(['success' => false, 'message' => 'Invalid admin path'], 404);
        }

        $product = $productRepository->find($id);

        if (!$product) {
            return new JsonResponse(['success' => false, 'message' => '商品不存在'], 404);
        }

        $data = json_decode($request->getContent(), true);
        $status = $data['status'] ?? '';
        $remark = $data['remark'] ?? '';

        if (!in_array($status, ['approved', 'rejected'])) {
            return new JsonResponse(['success' => false, 'message' => '无效的审核状态'], 400);
        }

        // 更新商品状态
        $product->setStatus($status);
        
        // 更新审核相关信息
        $product->setAuditRemark($remark);
        $product->setAuditedBy($this->getUser()->getUserIdentifier()); // 获取当前管理员用户名
        $product->setAuditedAt(new \DateTime()); // 设置审核时间
        
        // 如果是通过审核，设置首次上架时间（如果尚未设置）
        if ($status === 'approved' && !$product->getPublishDate()) {
            $product->setPublishDate(new \DateTime());
        }

        $entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'message' => $status === 'approved' ? '商品审核通过' : '商品审核拒绝'
        ]);
    }

    /**
     * 删除商品
     */
    #[Route('/product/delete/{id}', name: 'admin_product_delete', methods: ['POST'])]
    public function delete(
        int $id,
        ProductRepository $productRepository,
        EntityManagerInterface $entityManager,
        string $adminLoginPath
    ): JsonResponse {
        // 权限检查
        if (!$this->isGranted('ROLE_SUPER_ADMIN') && !$this->isGranted('ROLE_ADMIN_PRODUCT')) {
            return new JsonResponse(['success' => false, 'message' => 'Access Denied'], 403);
        }

        // 验证路径是否正确
        if ($adminLoginPath !== PathConfigService::getAdminLoginPath()) {
            return new JsonResponse(['success' => false, 'message' => 'Invalid admin path'], 404);
        }

        $product = $productRepository->find($id);

        if (!$product) {
            return new JsonResponse(['success' => false, 'message' => '商品不存在'], 404);
        }

        $entityManager->remove($product);
        $entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'message' => '商品删除成功'
        ]);
    }

    /**
     * 批量审核商品
     */
    #[Route('/product/batch-audit', name: 'admin_product_batch_audit', methods: ['POST'])]
    public function batchAudit(
        Request $request,
        ProductRepository $productRepository,
        EntityManagerInterface $entityManager,
        string $adminLoginPath
    ): JsonResponse {
        // 权限检查
        if (!$this->isGranted('ROLE_SUPER_ADMIN') && !$this->isGranted('ROLE_ADMIN_PRODUCT')) {
            return new JsonResponse(['success' => false, 'message' => 'Access Denied'], 403);
        }

        // 验证路径是否正确
        if ($adminLoginPath !== PathConfigService::getAdminLoginPath()) {
            return new JsonResponse(['success' => false, 'message' => 'Invalid admin path'], 404);
        }

        $data = json_decode($request->getContent(), true);
        $ids = $data['ids'] ?? [];
        $status = $data['status'] ?? '';

        if (!in_array($status, ['approved', 'rejected'])) {
            return new JsonResponse(['success' => false, 'message' => '无效的审核状态'], 400);
        }

        if (empty($ids)) {
            return new JsonResponse(['success' => false, 'message' => '请选择要审核的商品'], 400);
        }

        $count = 0;
        foreach ($ids as $id) {
            $product = $productRepository->find($id);
            if ($product) {
                $product->setStatus($status);
                
                // 更新审核相关信息
                $product->setAuditedBy($this->getUser()->getUserIdentifier()); // 获取当前管理员用户名
                $product->setAuditedAt(new \DateTime()); // 设置审核时间
                
                // 如果是通过审核，设置首次上架时间（如果尚未设置）
                if ($status === 'approved' && !$product->getPublishDate()) {
                    $product->setPublishDate(new \DateTime());
                }
                
                $count++;
            }
        }

        $entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'message' => "已{$this->getStatusText($status)}{$count}个商品"
        ]);
    }

    /**
     * 获取供应商列表（用于筛选）
     */
    #[Route('/product/suppliers', name: 'admin_product_suppliers', methods: ['GET'])]
    public function getSuppliers(
        SupplierRepository $supplierRepository,
        string $adminLoginPath
    ): JsonResponse {
        // 权限检查
        if (!$this->isGranted('ROLE_SUPER_ADMIN') && !$this->isGranted('ROLE_ADMIN_PRODUCT')) {
            return new JsonResponse(['success' => false, 'message' => 'Access Denied'], 403);
        }

        // 验证路径是否正确
        if ($adminLoginPath !== PathConfigService::getAdminLoginPath()) {
            return new JsonResponse(['success' => false, 'message' => 'Invalid admin path'], 404);
        }

        $suppliers = $supplierRepository->findBy(['isActive' => true], ['username' => 'ASC']);

        $data = [];
        foreach ($suppliers as $supplier) {
            $data[] = [
                'id' => $supplier->getId(),
                'username' => $supplier->getUsername(),
                'companyName' => $supplier->getCompanyName(),
            ];
        }

        return new JsonResponse([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * 获取状态文本
     */
    private function getStatusText(string $status): string
    {
        $statusMap = [
            'draft' => '草稿',
            'pending' => '待审核',
            'approved' => '已通过',
            'rejected' => '已拒绝',
            'offline' => '已下架',
        ];

        return $statusMap[$status] ?? $status;
    }

    /**
     * 验证数字字段精度
     */
    private function validateNumericFields(array $data): array
    {
        $errors = [];

        // 验证商品属性字段
        $attributes = [
            'length' => '长度',
            'width' => '宽度',
            'height' => '高度',
            'weight' => '重量',
            'packageLength' => '包装长度',
            'packageWidth' => '包装宽度',
            'packageHeight' => '包装高度',
            'packageWeight' => '包装重量'
        ];

        foreach ($attributes as $field => $label) {
            if (isset($data[$field]) && $data[$field] !== null && $data[$field] !== '') {
                $value = $data[$field];
                if (!$this->isValidTwoDecimalNumber($value)) {
                    $errors[] = $label . '最多只能输入两位小数';
                }
            }
        }

        // 验证库存字段
        if (isset($data['stock']) && is_array($data['stock'])) {
            $stockData = $data['stock'];
            if (isset($stockData['availableStock']) && $stockData['availableStock'] !== null && $stockData['availableStock'] !== '') {
                $value = $stockData['availableStock'];
                if (!$this->isValidNonNegativeInteger($value)) {
                    $errors[] = '可售库存必须为非负整数';
                }
            }
            if (isset($stockData['alertStockLine']) && $stockData['alertStockLine'] !== null && $stockData['alertStockLine'] !== '') {
                $value = $stockData['alertStockLine'];
                if (!$this->isValidNonNegativeInteger($value)) {
                    $errors[] = '库存预警线必须为非负整数';
                }
            }
        }

        // 验证价格字段
        if (isset($data['price']) && is_array($data['price'])) {
            $priceData = $data['price'];
            $priceFields = [
                'originalPrice' => '原价',
                'sellingPrice' => '售价',
                'shippingPrice' => '运费'
            ];

            foreach ($priceFields as $field => $label) {
                if (isset($priceData[$field]) && $priceData[$field] !== null && $priceData[$field] !== '') {
                    $value = $priceData[$field];
                    if (!$this->isValidTwoDecimalNumber($value)) {
                        $errors[] = $label . '最多只能输入两位小数';
                    }
                }
            }

            // 验证最小起订量
            if (isset($priceData['minWholesaleQuantity']) && $priceData['minWholesaleQuantity'] !== null && $priceData['minWholesaleQuantity'] !== '') {
                $value = $priceData['minWholesaleQuantity'];
                if (!$this->isValidNonNegativeInteger($value)) {
                    $errors[] = '最小起订量必须为非负整数';
                }
            }

            // 验证折扣率
            if (isset($priceData['discountRate']) && $priceData['discountRate'] !== null && $priceData['discountRate'] !== '') {
                $value = $priceData['discountRate'];
                if (!$this->isValidOneDecimalNumber($value)) {
                    $errors[] = '折扣率最多只能输入一位小数';
                } elseif ($value < 0 || $value > 90) {
                    $errors[] = '折扣率必须在0-90之间';
                }
            }

            // 验证会员折扣
            if (isset($priceData['memberDiscount']) && is_array($priceData['memberDiscount'])) {
                foreach ($priceData['memberDiscount'] as $level => $discount) {
                    if ($discount !== null && $discount !== '') {
                        if (!$this->isValidOneDecimalNumber($discount)) {
                            $errors[] = '会员等级' . $level . '折扣最多只能输入一位小数';
                        } elseif ($discount < 0 || $discount > 90) {
                            $errors[] = '会员等级' . $level . '折扣必须在0-90之间';
                        }
                    }
                }
            }
        }

        // 验证物流字段
        if (isset($data['shipping']) && is_array($data['shipping'])) {
            $shippingData = $data['shipping'];
            if (isset($shippingData['shippingPrice']) && $shippingData['shippingPrice'] !== null && $shippingData['shippingPrice'] !== '') {
                $value = $shippingData['shippingPrice'];
                if (!$this->isValidTwoDecimalNumber($value)) {
                    $errors[] = '运费最多只能输入两位小数';
                }
            }
        }

        return $errors;
    }

    /**
     * 验证是否为最多两位小数的数字
     */
    private function isValidTwoDecimalNumber($value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        // 检查是否为有效数字
        if (!is_numeric($value)) {
            return false;
        }

        // 转换为字符串检查小数位数
        $strValue = (string)$value;
        if (strpos($strValue, '.') !== false) {
            $decimalPart = explode('.', $strValue)[1];
            return strlen($decimalPart) <= 2;
        }

        return true;
    }

    /**
     * 验证是否为最多一位小数的数字
     */
    private function isValidOneDecimalNumber($value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        // 检查是否为有效数字
        if (!is_numeric($value)) {
            return false;
        }

        // 转换为字符串检查小数位数
        $strValue = (string)$value;
        if (strpos($strValue, '.') !== false) {
            $decimalPart = explode('.', $strValue)[1];
            return strlen($decimalPart) <= 1;
        }

        return true;
    }

    /**
     * 验证是否为非负整数
     */
    private function isValidNonNegativeInteger($value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        // 检查是否为有效数字
        if (!is_numeric($value)) {
            return false;
        }

        // 检查是否为整数且非负
        return floor($value) == $value && $value >= 0;
    }

    /**
     * 获取商品详情
     */
    #[Route('/product/detail/{id}', name: 'admin_product_detail', methods: ['GET'])]
    public function detail(
        int $id,
        ProductRepository $productRepository,
        string $adminLoginPath
    ): JsonResponse {
        // 权限检查
        if (!$this->isGranted('ROLE_SUPER_ADMIN') && !$this->isGranted('ROLE_ADMIN_PRODUCT')) {
            return new JsonResponse(['success' => false, 'message' => 'Access Denied'], 403);
        }

        // 验证路径是否正确
        if ($adminLoginPath !== PathConfigService::getAdminLoginPath()) {
            return new JsonResponse(['success' => false, 'message' => 'Invalid admin path'], 404);
        }

        $product = $productRepository->find($id);

        if (!$product) {
            return new JsonResponse(['success' => false, 'message' => '商品不存在'], 404);
        }

        // 创建七牛云服务实例
        $qiniuService = new QiniuUploadService();

        // 处理主图数组
        $mainImageKeys = $product->getMainImages();
        $mainImages = [];
        if (is_array($mainImageKeys)) {
            foreach ($mainImageKeys as $key) {
                if ($key) {
                    // 处理旧数据（如果存的是完整URL，提取key）
                    if (str_starts_with($key, 'http')) {
                        $parts = parse_url($key);
                        $key = ltrim($parts['path'] ?? '', '/');
                        $key = preg_replace('/\?.*$/', '', $key);
                    }
                    // 生成签名URL
                    $mainImages[] = [
                        'url' => $qiniuService->getPrivateUrl($key),
                        'imageKey' => $key
                    ];
                }
            }
        }

        // 获取库存信息
        $stockData = null;
        $stock = $product->getStocks()->first();
        if ($stock) {
            $stockData = [
                'id' => $stock->getId(),
                'warehouseCode' => $stock->getWarehouseCode(),
                'warehouseName' => $stock->getWarehouseName(),
                'warehouseAddress' => $stock->getWarehouseAddress(),
                'availableStock' => $stock->getAvailableStock(),
                'frozenStock' => $stock->getFrozenStock()
            ];
        }

        // 获取价格信息
        $priceData = null;
        $price = $product->getPrices()->first();
        if ($price) {
            $priceData = [
                'id' => $price->getId(),
                'currency' => $price->getCurrency(),
                'businessType' => $price->getBusinessType(),
                'originalPrice' => $price->getOriginalPrice(),
                'sellingPrice' => $price->getSellingPrice(),
                'discountRate' => $price->getDiscountRate(),
                'memberDiscount' => $price->getMemberDiscount(),
                'minWholesaleQuantity' => $price->getMinWholesaleQuantity()
            ];
        }

        // 获取物流信息
        $shippingData = null;
        $shipping = $product->getShippings()->first();
        if ($shipping) {
            $shippingData = [
                'id' => $shipping->getId(),
                'shippingMethod' => $shipping->getShippingMethod(),
                'shippingPrice' => $shipping->getShippingPrice(),
                'currency' => $shipping->getCurrency(),
                'deliveryTime' => $shipping->getDeliveryTime(),
                'carriers' => $shipping->getCarriers()  // 直接返回承运商名称数组
            ];
        }

        $productData = [
            'id' => $product->getId(),
            'sku' => $product->getSku(),
            'spu' => $product->getSpu(),
            'title' => $product->getTitle(),
            'titleEn' => $product->getTitleEn(),
            'mainImages' => $mainImages,
            'categoryId' => $product->getCategory() ? $product->getCategory()->getId() : null,
            'subcategoryId' => $product->getSubcategory() ? $product->getSubcategory()->getId() : null,
            'itemId' => $product->getItem() ? $product->getItem()->getId() : null,
            'brand' => $product->getBrand(),
            'length' => $product->getLength(),
            'width' => $product->getWidth(),
            'height' => $product->getHeight(),
            'weight' => $product->getWeight(),
            'packageLength' => $product->getPackageLength(),
            'packageWidth' => $product->getPackageWidth(),
            'packageHeight' => $product->getPackageHeight(),
            'packageWeight' => $product->getPackageWeight(),
            'tags' => $product->getTags(),
            'supportDropship' => $product->isSupportDropship(),
            'supportWholesale' => $product->isSupportWholesale(),
            'supportCircleBuy' => $product->isSupportCircleBuy(),
            'supportSelfPickup' => $product->isSupportSelfPickup(),
            'alertStockLine' => $product->getAlertStockLine(),
            'richContent' => $product->getRichContent(),
            'status' => $product->getStatus(),
            'auditRemark' => $product->getAuditRemark(),
            'auditedBy' => $product->getAuditedBy(),
            'auditedAt' => $product->getAuditedAt() ? $product->getAuditedAt()->format('Y-m-d H:i:s') : '',
            'publishDate' => $product->getPublishDate() ? $product->getPublishDate()->format('Y-m-d H:i:s') : '',
            'stock' => $stockData,
            'price' => $priceData,
            'shipping' => $shippingData,
            'createdAt' => $product->getCreatedAt() ? $product->getCreatedAt()->format('Y-m-d H:i:s') : '',
            'updatedAt' => $product->getUpdatedAt() ? $product->getUpdatedAt()->format('Y-m-d H:i:s') : ''
        ];

        return new JsonResponse([
            'success' => true,
            'data' => $productData
        ]);
    }

    /**
     * 获取会员等级信息
     */
    #[Route('/product/vip-levels', name: 'admin_product_vip_levels', methods: ['GET'])]
    public function getVipLevels(string $adminLoginPath): JsonResponse
    {
        // 权限检查
        if (!$this->isGranted('ROLE_SUPER_ADMIN') && !$this->isGranted('ROLE_ADMIN_PRODUCT')) {
            return new JsonResponse(['success' => false, 'message' => 'Access Denied'], 403);
        }

        // 验证路径是否正确
        if ($adminLoginPath !== PathConfigService::getAdminLoginPath()) {
            return new JsonResponse(['success' => false, 'message' => 'Invalid admin path'], 404);
        }

        // 从 VipLevel 类获取会员等级信息
        $vipLevels = \App\common\VipLevel::getLevelOptions();

        // 转换数据格式以匹配前端代码
        $formattedVipLevels = [];
        foreach ($vipLevels as $level) {
            $formattedVipLevels[] = [
                'value' => $level['value'],
                'label' => $level['label']
            ];
        }

        return new JsonResponse([
            'success' => true,
            'data' => $formattedVipLevels
        ]);
    }

    /**
     * 获取物流公司列表
     */
    #[Route('/product/logistics-companies', name: 'admin_product_logistics_companies', methods: ['GET'])]
    public function getLogisticsCompanies(
        LogisticsCompanyService $logisticsCompanyService,
        string $adminLoginPath
    ): JsonResponse {
        // 权限检查
        if (!$this->isGranted('ROLE_SUPER_ADMIN') && !$this->isGranted('ROLE_ADMIN_PRODUCT')) {
            return new JsonResponse(['success' => false, 'message' => 'Access Denied'], 403);
        }

        // 验证路径是否正确
        if ($adminLoginPath !== PathConfigService::getAdminLoginPath()) {
            return new JsonResponse(['success' => false, 'message' => 'Invalid admin path'], 404);
        }

        // 获取所有启用的物流公司，按热度排序
        $companies = $logisticsCompanyService->getActiveLogisticsCompanies();

        return new JsonResponse([
            'success' => true,
            'data' => $companies
        ]);
    }
}
