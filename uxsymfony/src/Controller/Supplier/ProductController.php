<?php

namespace App\Controller\Supplier;

use App\Entity\Product;

use App\Entity\ProductStock;
use App\Entity\ProductPrice;
use App\Entity\ProductShipping;
use App\Entity\Supplier;
use App\Entity\SupplierSubAccount;
use App\Entity\LogisticsCompany;
use App\Repository\ProductRepository;
use App\Repository\MenuCategoryRepository;
use App\Repository\MenuSubcategoryRepository;
use App\Repository\MenuItemRepository;
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

#[Route('/supplier{supplierLoginPath}', requirements: ['supplierLoginPath' => '[a-zA-Z0-9]+'])]
#[IsGranted('SUPPLIER_ACTIVE')]
class ProductController extends AbstractController
{
    /**
     * 商品列表页面
     */
    #[Route('/product/list', name: 'supplier_product_list')]
    public function list(string $supplierLoginPath): Response
    {
        // 权限检查
        if (!$this->isGranted('ROLE_SUPPLIER_SUPER') && !$this->isGranted('ROLE_SUPPLIER_PRODUCT')) {
            throw $this->createAccessDeniedException('Access Denied.');
        }

        // 验证路径是否正确
        if ($supplierLoginPath !== PathConfigService::getSupplierLoginPath()) {
            throw $this->createNotFoundException('Invalid supplier path');
        }

        return $this->render('supplier/product/list.html.twig', [
            'supplierLoginPath' => $supplierLoginPath,
        ]);
    }

    /**
     * 添加商品页面
     */
    #[Route('/product/add', name: 'supplier_product_add')]
    public function add(string $supplierLoginPath): Response
    {
        // 权限检查
        if (!$this->isGranted('ROLE_SUPPLIER_SUPER') && !$this->isGranted('ROLE_SUPPLIER_PRODUCT')) {
            throw $this->createAccessDeniedException('Access Denied.');
        }

        // 验证路径是否正确
        if ($supplierLoginPath !== PathConfigService::getSupplierLoginPath()) {
            throw $this->createNotFoundException('Invalid supplier path');
        }

        return $this->render('supplier/product/add.html.twig', [
            'supplierLoginPath' => $supplierLoginPath,
        ]);
    }

    /**
     * 获取商品列表数据（AJAX）
     */
    #[Route('/product/list/data', name: 'supplier_product_list_data', methods: ['GET'])]
    public function listData(
        Request $request,
        ProductRepository $productRepository,
        string $supplierLoginPath
    ): JsonResponse {
        // 权限检查
        if (!$this->isGranted('ROLE_SUPPLIER_SUPER') && !$this->isGranted('ROLE_SUPPLIER_PRODUCT')) {
            return new JsonResponse(['success' => false, 'message' => 'Access Denied'], 403);
        }

        // 验证路径是否正确
        if ($supplierLoginPath !== PathConfigService::getSupplierLoginPath()) {
            return new JsonResponse(['success' => false, 'message' => 'Invalid supplier path'], 404);
        }

        // 获取主供应商账号（支持子账号）
        $supplier = $this->getMainSupplier();

        // 获取分页参数
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 20);

        // 获取搜索筛选参数
        $searchParams = [
            'keyword' => $request->query->get('keyword', ''),
            'status' => $request->query->get('status', ''),
            'categoryId' => $request->query->get('categoryId', ''),
            'subcategoryId' => $request->query->get('subcategoryId', ''),
            'itemId' => $request->query->get('itemId', ''),
            'isNew' => $request->query->get('isNew', ''),
            'isHot' => $request->query->get('isHot', ''),
            'isPromotion' => $request->query->get('isPromotion', ''),
            'sortField' => $request->query->get('sortField', 'createdAt'),
            'sortOrder' => $request->query->get('sortOrder', 'DESC'),
        ];

        // 调用Repository的分页查询方法
        $result = $productRepository->findPaginatedBySupplier($supplier, $searchParams, $page, $limit);

        // 创建七牛云服务实例
        $qiniuService = new QiniuUploadService();

        // 格式化商品数据
        $productsData = [];
        foreach ($result['data'] as $product) {
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

            $productsData[] = [
                'id' => $product->getId(),
                'sku' => $product->getSku(),
                'spu' => $product->getSpu(),
                'title' => $product->getTitle(),
                'titleEn' => $product->getTitleEn(),
                'mainImages' => $mainImages,
                'status' => $product->getStatus(),
                'statusText' => $this->getStatusText($product->getStatus()),
                'category' => $product->getCategory() ? $product->getCategory()->getTitle() : '',
                'subcategory' => $product->getSubcategory() ? $product->getSubcategory()->getTitle() : '',
                'item' => $product->getItem() ? $product->getItem()->getTitle() : '',
                'brand' => $product->getBrand(),
                'isNew' => $product->isNew(),
                'isHot' => $product->isHot(),
                'isPromotion' => $product->isPromotion(),
                'isFeatured' => $product->isFeatured(),
                'tags' => $product->getTags(),
                'viewCount' => $product->getViewCount(),
                'salesCount' => $product->getSalesCount(),
                'downloadCount' => $product->getDownloadCount(),
                'createdAt' => $product->getCreatedAt() ? $product->getCreatedAt()->format('Y-m-d H:i:s') : '',
                'updatedAt' => $product->getUpdatedAt() ? $product->getUpdatedAt()->format('Y-m-d H:i:s') : '',
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
    #[Route('/product/statistics', name: 'supplier_product_statistics', methods: ['GET'])]
    public function statistics(
        ProductRepository $productRepository,
        string $supplierLoginPath
    ): JsonResponse {
        // 权限检查
        if (!$this->isGranted('ROLE_SUPPLIER_SUPER') && !$this->isGranted('ROLE_SUPPLIER_PRODUCT')) {
            return new JsonResponse(['success' => false, 'message' => 'Access Denied'], 403);
        }

        // 验证路径是否正确
        if ($supplierLoginPath !== PathConfigService::getSupplierLoginPath()) {
            return new JsonResponse(['success' => false, 'message' => 'Invalid supplier path'], 404);
        }

        // 获取主供应商账号（支持子账号）
        $supplier = $this->getMainSupplier();

        $statistics = $productRepository->getSupplierStatistics($supplier);

        return new JsonResponse([
            'success' => true,
            'data' => $statistics
        ]);
    }

    /**
     * 获取当前用户关联的主供应商账号
     * 如果当前用户是子账号，则返回关联的主账号
     * 如果当前用户是主账号，则返回自身
     */
    private function getMainSupplier(): Supplier
    {
        $user = $this->getUser();
        
        // 如果是子账号，获取关联的主账号
        if ($user instanceof SupplierSubAccount) {
            $mainSupplier = $user->getSupplier();
            if (!$mainSupplier) {
                throw $this->createNotFoundException('找不到关联的主供应商账号');
            }
            return $mainSupplier;
        }
        
        // 如果是主账号，直接返回
        if ($user instanceof Supplier) {
            return $user;
        }
        
        throw $this->createAccessDeniedException('无效的用户类型');
    }

    /**
     * 获取一级分类列表
     */
    #[Route('/product/categories', name: 'supplier_product_categories', methods: ['GET'])]
    public function getCategories(
        MenuCategoryRepository $categoryRepository,
        string $supplierLoginPath
    ): JsonResponse {
        // 权限检查
        if (!$this->isGranted('ROLE_SUPPLIER_SUPER') && !$this->isGranted('ROLE_SUPPLIER_PRODUCT')) {
            return new JsonResponse(['success' => false, 'message' => 'Access Denied'], 403);
        }

        // 验证路径是否正确
        if ($supplierLoginPath !== PathConfigService::getSupplierLoginPath()) {
            return new JsonResponse(['success' => false, 'message' => 'Invalid supplier path'], 404);
        }

        // 获取主供应商账号（支持子账号）
        $supplier = $this->getMainSupplier();

        $categories = $categoryRepository->findBy(['isActive' => true], ['sortOrder' => 'ASC']);

        $data = [];
        foreach ($categories as $category) {
            $data[] = [
                'id' => $category->getId(),
                'name' => $category->getTitle(),
                'nameEn' => $category->getTitleEn(),
            ];
        }

        return new JsonResponse([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * 获取二级分类（子分类）列表
     */
    #[Route('/product/subcategories/{categoryId}', name: 'supplier_product_subcategories', methods: ['GET'])]
    public function getSubcategories(
        int $categoryId,
        MenuSubcategoryRepository $subcategoryRepository,
        string $supplierLoginPath
    ): JsonResponse {
        // 权限检查
        if (!$this->isGranted('ROLE_SUPPLIER_SUPER') && !$this->isGranted('ROLE_SUPPLIER_PRODUCT')) {
            return new JsonResponse(['success' => false, 'message' => 'Access Denied'], 403);
        }

        // 验证路径是否正确
        if ($supplierLoginPath !== PathConfigService::getSupplierLoginPath()) {
            return new JsonResponse(['success' => false, 'message' => 'Invalid supplier path'], 404);
        }

        // 获取主供应商账号（支持子账号）
        $supplier = $this->getMainSupplier();

        $subcategories = $subcategoryRepository->findBy(
            ['category' => $categoryId, 'isActive' => true],
            ['sortOrder' => 'ASC']
        );

        $data = [];
        foreach ($subcategories as $subcategory) {
            $data[] = [
                'id' => $subcategory->getId(),
                'name' => $subcategory->getTitle(),
                'nameEn' => $subcategory->getTitleEn(),
            ];
        }

        return new JsonResponse([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * 获取三级分类（商品项）列表
     */
    #[Route('/product/items/{subcategoryId}', name: 'supplier_product_items', methods: ['GET'])]
    public function getItems(
        int $subcategoryId,
        MenuItemRepository $itemRepository,
        string $supplierLoginPath
    ): JsonResponse {
        // 权限检查
        if (!$this->isGranted('ROLE_SUPPLIER_SUPER') && !$this->isGranted('ROLE_SUPPLIER_PRODUCT')) {
            return new JsonResponse(['success' => false, 'message' => 'Access Denied'], 403);
        }

        // 验证路径是否正确
        if ($supplierLoginPath !== PathConfigService::getSupplierLoginPath()) {
            return new JsonResponse(['success' => false, 'message' => 'Invalid supplier path'], 404);
        }

        // 获取主供应商账号（支持子账号）
        $supplier = $this->getMainSupplier();

        $items = $itemRepository->findBy(
            ['subcategory' => $subcategoryId, 'isActive' => true],
            ['sortOrder' => 'ASC']
        );

        $data = [];
        foreach ($items as $item) {
            $data[] = [
                'id' => $item->getId(),
                'name' => $item->getTitle(),
                'nameEn' => $item->getTitleEn(),
            ];
        }

        return new JsonResponse([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * 创建商品
     */
    #[Route('/product/create', name: 'supplier_product_create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        MenuCategoryRepository $categoryRepository,
        MenuSubcategoryRepository $subcategoryRepository,
        MenuItemRepository $itemRepository,
        string $supplierLoginPath
    ): JsonResponse {
        // 权限检查
        if (!$this->isGranted('ROLE_SUPPLIER_SUPER') && !$this->isGranted('ROLE_SUPPLIER_PRODUCT')) {
            return new JsonResponse(['success' => false, 'message' => 'Access Denied'], 403);
        }

        // 验证路径是否正确
        if ($supplierLoginPath !== PathConfigService::getSupplierLoginPath()) {
            return new JsonResponse(['success' => false, 'message' => 'Invalid supplier path'], 404);
        }

        // 获取主供应商账号（支持子账号）
        $supplier = $this->getMainSupplier();

        $data = json_decode($request->getContent(), true);

        // 验证主图
        $hasMainImage = false;
        if (isset($data['mainImages']) && is_array($data['mainImages'])) {
            foreach ($data['mainImages'] as $image) {
                if (!empty($image['url'])) {
                    $hasMainImage = true;
                    break;
                }
            }
        }
        
        if (!$hasMainImage) {
            return new JsonResponse(['success' => false, 'message' => '请至少上传一张商品主图'], 400);
        }

        // 验证数字字段精度
        $validationErrors = $this->validateNumericFields($data);
        if (!empty($validationErrors)) {
            return new JsonResponse(['success' => false, 'message' => implode('; ', $validationErrors)], 400);
        }

        // 开始事务
        $entityManager->beginTransaction();
        try {
            // 创建商品实体
            $product = new Product();
            $product->setSupplier($supplier);
            $product->setSku($data['sku'] ?? $this->generateSku());
            $product->setSpu($data['spu'] ?? $this->generateSpu());
            $product->setTitle($data['title'] ?? '');
            $product->setTitleEn($data['titleEn'] ?? null);
            
            // 处理主图数组
            if (isset($data['mainImages']) && is_array($data['mainImages'])) {
                $mainImageKeys = array_filter(array_map(function($image) {
                    return $image['url'] ?? null;
                }, $data['mainImages']));
                $product->setMainImages($mainImageKeys);
            } else {
                $product->setMainImages([]);
            }
            
            $product->setStatus($data['status'] ?? 'draft');

            // 设置分类
            if (!empty($data['categoryId'])) {
                $category = $categoryRepository->find($data['categoryId']);
                if ($category) {
                    $product->setCategory($category);
                }
            }
            if (!empty($data['subcategoryId'])) {
                $subcategory = $subcategoryRepository->find($data['subcategoryId']);
                if ($subcategory) {
                    $product->setSubcategory($subcategory);
                }
            }
            if (!empty($data['itemId'])) {
                $item = $itemRepository->find($data['itemId']);
                if ($item) {
                    $product->setItem($item);
                }
            }

            // 设置商品属性
            $product->setBrand($data['brand'] ?? null);
            $product->setLength($data['length'] ?? null);
            $product->setWidth($data['width'] ?? null);
            $product->setHeight($data['height'] ?? null);
            $product->setWeight($data['weight'] ?? null);
            
            // 设置包装信息
            $product->setPackageLength($data['packageLength'] ?? null);
            $product->setPackageWidth($data['packageWidth'] ?? null);
            $product->setPackageHeight($data['packageHeight'] ?? null);
            $product->setPackageWeight($data['packageWeight'] ?? null);
            
            // 设置商品标签
            $product->setTags($data['tags'] ?? []);
            
            // 设置业务类型
            $product->setSupportDropship($data['supportDropship'] ?? true);
            $product->setSupportWholesale($data['supportWholesale'] ?? false);
            $product->setSupportCircleBuy($data['supportCircleBuy'] ?? false);
            $product->setSupportSelfPickup($data['supportSelfPickup'] ?? false);
            
            // 设置库存预警线
            $product->setAlertStockLine($data['alertStockLine'] ?? 10);
            
            // 设置商品详情
            $product->setRichContent($data['richContent'] ?? null);

            $entityManager->persist($product);
            $entityManager->flush();



            // 处理库存信息
            if (!empty($data['stock'])) {
                $stockData = $data['stock'];
                $stock = new ProductStock();
                $stock->setProduct($product);
                $stock->setRegion('CN'); // 默认区域为中国
                $stock->setWarehouseCode($stockData['warehouseCode'] ?? 'WH001');
                $stock->setWarehouseName($stockData['warehouseName'] ?? '默认仓库');
                $stock->setWarehouseAddress($stockData['warehouseAddress'] ?? null);
                $stock->setAvailableStock($stockData['availableStock'] ?? 0);
                $stock->setFrozenStock(0); // 默认冻结库存为0
                $entityManager->persist($stock);
            }

            // 处理价格信息
            if (!empty($data['price'])) {
                $priceData = $data['price'];
                $price = new ProductPrice();
                $price->setProduct($product);
                $price->setRegion('CN'); // 默认区域为中国
                $price->setCurrency($priceData['currency'] ?? 'CNY');
                $price->setBusinessType($priceData['businessType'] ?? 'dropship');
                $price->setOriginalPrice($priceData['originalPrice'] ?? null);
                $price->setSellingPrice($priceData['sellingPrice'] ?? '0.0000');
                
                // 设置折扣率
                if (isset($priceData['discountRate'])) {
                    // 将百分比转换为小数形式存储
                    $discountRate = $priceData['discountRate'] / 100;
                    $price->setDiscountRate($discountRate);
                }
                
                // 设置会员折扣
                if (isset($priceData['memberDiscount']) && is_array($priceData['memberDiscount'])) {
                    $memberDiscount = [];
                    foreach ($priceData['memberDiscount'] as $level => $discount) {
                        // 确保会员等级是有效的(0-5)
                        if (in_array($level, ['0', '1', '2', '3', '4', '5'])) {
                            // 将百分比转换为小数形式存储
                            $memberDiscount[$level] = $discount / 100;
                        }
                    }
                    $price->setMemberDiscount($memberDiscount);
                }
                
                $price->setIsActive(true);
                $price->setMinWholesaleQuantity($priceData['minWholesaleQuantity'] ?? null);
                $entityManager->persist($price);
            }

            // 处理物流信息
            if (!empty($data['shipping'])) {
                $shippingData = $data['shipping'];
                $shipping = new ProductShipping();
                $shipping->setProduct($product);
                $shipping->setRegion('CN'); // 默认区域为中国
                $shipping->setShippingMethod($shippingData['shippingMethod'] ?? 'standard');
                $shipping->setShippingPrice($shippingData['shippingPrice'] ?? '0.0000');
                $shipping->setCurrency($shippingData['currency'] ?? 'CNY');
                $shipping->setDeliveryTime($shippingData['deliveryTime'] ?? null);
                
                // 直接保存承运商名称，不进行ID转换
                $carriers = $shippingData['carriers'] ?? [];
                if (!empty($carriers) && is_array($carriers)) {
                    // 过滤并确保只保存名称格式的数据
                    $carrierNames = array_filter($carriers, function($carrier) {
                        return !empty($carrier) && is_string($carrier) && strpos($carrier, ' / ') !== false;
                    });
                    $shipping->setCarriers(array_values($carrierNames));
                } else {
                    $shipping->setCarriers([]);
                }
                
                $shipping->setIsDefault(true);
                $shipping->setIsActive(true);
                $entityManager->persist($shipping);
            }

            $entityManager->flush();
            $entityManager->commit();

            return new JsonResponse([
                'success' => true,
                'message' => '商品创建成功',
                'data' => ['id' => $product->getId()]
            ]);
        } catch (\Exception $e) {
            $entityManager->rollback();
            return new JsonResponse([
                'success' => false,
                'message' => '商品创建失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 更新商品
     */
    #[Route('/product/update/{id}', name: 'supplier_product_update', methods: ['POST'])]
    public function update(
        int $id,
        Request $request,
        ProductRepository $productRepository,
        EntityManagerInterface $entityManager,
        MenuCategoryRepository $categoryRepository,
        MenuSubcategoryRepository $subcategoryRepository,
        MenuItemRepository $itemRepository,
        string $supplierLoginPath
    ): JsonResponse {
        // 权限检查
        if (!$this->isGranted('ROLE_SUPPLIER_SUPER') && !$this->isGranted('ROLE_SUPPLIER_PRODUCT')) {
            return new JsonResponse(['success' => false, 'message' => 'Access Denied'], 403);
        }

        // 验证路径是否正确
        if ($supplierLoginPath !== PathConfigService::getSupplierLoginPath()) {
            return new JsonResponse(['success' => false, 'message' => 'Invalid supplier path'], 404);
        }

        // 获取主供应商账号（支持子账号）
        $supplier = $this->getMainSupplier();

        $product = $productRepository->find($id);

        if (!$product) {
            return new JsonResponse(['success' => false, 'message' => '商品不存在'], 404);
        }

        // 确保只能编辑自己的商品
        if ($product->getSupplier()->getId() !== $supplier->getId()) {
            return new JsonResponse(['success' => false, 'message' => '无权编辑此商品'], 403);
        }

        $data = json_decode($request->getContent(), true);

        // 验证主图
        $hasMainImage = false;
        if (isset($data['mainImages']) && is_array($data['mainImages'])) {
            foreach ($data['mainImages'] as $image) {
                if (!empty($image['url'])) {
                    $hasMainImage = true;
                    break;
                }
            }
        }
        
        if (!$hasMainImage) {
            return new JsonResponse(['success' => false, 'message' => '请至少上传一张商品主图'], 400);
        }

        // 验证数字字段精度
        $validationErrors = $this->validateNumericFields($data);
        if (!empty($validationErrors)) {
            return new JsonResponse(['success' => false, 'message' => implode('; ', $validationErrors)], 400);
        }

        // 开始事务
        $entityManager->beginTransaction();
        try {
            // 更新商品基本信息
            if (isset($data['title'])) {
                $product->setTitle($data['title']);
            }
            if (isset($data['titleEn'])) {
                $product->setTitleEn($data['titleEn']);
            }
            
            // 处理主图数组
            if (isset($data['mainImages'])) {
                if (is_array($data['mainImages'])) {
                    $mainImageKeys = array_filter(array_map(function($image) {
                        return $image['url'] ?? null;
                    }, $data['mainImages']));
                    $product->setMainImages($mainImageKeys);
                } else {
                    $product->setMainImages([]);
                }
            }
            
            if (isset($data['status'])) {
                $product->setStatus($data['status']);
            }

            // 更新分类
            if (isset($data['categoryId'])) {
                $category = $data['categoryId'] ? $categoryRepository->find($data['categoryId']) : null;
                $product->setCategory($category);
            }
            if (isset($data['subcategoryId'])) {
                $subcategory = $data['subcategoryId'] ? $subcategoryRepository->find($data['subcategoryId']) : null;
                $product->setSubcategory($subcategory);
            }
            if (isset($data['itemId'])) {
                $item = $data['itemId'] ? $itemRepository->find($data['itemId']) : null;
                $product->setItem($item);
            }

            // 更新商品属性
            if (isset($data['brand'])) {
                $product->setBrand($data['brand']);
            }
            if (isset($data['length'])) {
                $product->setLength($data['length']);
            }
            if (isset($data['width'])) {
                $product->setWidth($data['width']);
            }
            if (isset($data['height'])) {
                $product->setHeight($data['height']);
            }
            if (isset($data['weight'])) {
                $product->setWeight($data['weight']);
            }
            if (isset($data['packageLength'])) {
                $product->setPackageLength($data['packageLength']);
            }
            if (isset($data['packageWidth'])) {
                $product->setPackageWidth($data['packageWidth']);
            }
            if (isset($data['packageHeight'])) {
                $product->setPackageHeight($data['packageHeight']);
            }
            if (isset($data['packageWeight'])) {
                $product->setPackageWeight($data['packageWeight']);
            }
            if (isset($data['tags'])) {
                $product->setTags($data['tags']);
            }
            if (isset($data['richContent'])) {
                $product->setRichContent($data['richContent']);
            }
            if (isset($data['supportDropship'])) {
                $product->setSupportDropship($data['supportDropship']);
            }
            if (isset($data['supportWholesale'])) {
                $product->setSupportWholesale($data['supportWholesale']);
            }
            if (isset($data['supportCircleBuy'])) {
                $product->setSupportCircleBuy($data['supportCircleBuy']);
            }
            if (isset($data['supportSelfPickup'])) {
                $product->setSupportSelfPickup($data['supportSelfPickup']);
            }
            if (isset($data['alertStockLine'])) {
                $product->setAlertStockLine($data['alertStockLine']);
            }



            // 更新库存信息
            if (isset($data['stock'])) {
                $stockData = $data['stock'];
                // 查找现有的库存记录，如果没有则创建新的
                $stock = $product->getStocks()->first() ?: new ProductStock();
                if (!$stock->getId()) {
                    $stock->setProduct($product);
                    $stock->setRegion('CN'); // 默认区域为中国
                }
                $stock->setWarehouseCode($stockData['warehouseCode'] ?? $stock->getWarehouseCode() ?? 'WH001');
                $stock->setWarehouseName($stockData['warehouseName'] ?? $stock->getWarehouseName() ?? '默认仓库');
                $stock->setWarehouseAddress($stockData['warehouseAddress'] ?? $stock->getWarehouseAddress());
                $stock->setAvailableStock($stockData['availableStock'] ?? $stock->getAvailableStock() ?? 0);
                if (!$stock->getId()) {
                    $entityManager->persist($stock);
                }
            }

            // 更新价格信息
            if (isset($data['price'])) {
                $priceData = $data['price'];
                // 查找现有的价格记录，如果没有则创建新的
                $price = $product->getPrices()->first() ?: new ProductPrice();
                if (!$price->getId()) {
                    $price->setProduct($product);
                    $price->setRegion('CN'); // 默认区域为中国
                }
                $price->setCurrency($priceData['currency'] ?? $price->getCurrency() ?? 'CNY');
                $price->setBusinessType($priceData['businessType'] ?? $price->getBusinessType() ?? 'dropship');
                $price->setOriginalPrice($priceData['originalPrice'] ?? $price->getOriginalPrice());
                $price->setSellingPrice($priceData['sellingPrice'] ?? $price->getSellingPrice() ?? '0.0000');
                
                // 设置折扣率
                if (isset($priceData['discountRate'])) {
                    // 将百分比转换为小数形式存储
                    $discountRate = $priceData['discountRate'] / 100;
                    $price->setDiscountRate($discountRate);
                } else if (isset($priceData['discountRate']) && $priceData['discountRate'] === null) {
                    $price->setDiscountRate(null);
                }
                
                // 设置会员折扣
                if (isset($priceData['memberDiscount']) && is_array($priceData['memberDiscount'])) {
                    $memberDiscount = [];
                    foreach ($priceData['memberDiscount'] as $level => $discount) {
                        // 确保会员等级是有效的(0-5)
                        if (in_array($level, ['0', '1', '2', '3', '4', '5'])) {
                            // 将百分比转换为小数形式存储
                            $memberDiscount[$level] = $discount / 100;
                        }
                    }
                    $price->setMemberDiscount($memberDiscount);
                }
                
                $price->setMinWholesaleQuantity($priceData['minWholesaleQuantity'] ?? $price->getMinWholesaleQuantity());
                $price->setIsActive(true);
                if (!$price->getId()) {
                    $entityManager->persist($price);
                }
            }

            // 更新物流信息
            if (isset($data['shipping'])) {
                $shippingData = $data['shipping'];
                // 查找现有的物流记录，如果没有则创建新的
                $shipping = $product->getShippings()->first() ?: new ProductShipping();
                if (!$shipping->getId()) {
                    $shipping->setProduct($product);
                    $shipping->setRegion('CN'); // 默认区域为中国
                }
                $shipping->setShippingMethod($shippingData['shippingMethod'] ?? $shipping->getShippingMethod() ?? 'standard');
                $shipping->setShippingPrice($shippingData['shippingPrice'] ?? $shipping->getShippingPrice() ?? '0.0000');
                $shipping->setCurrency($shippingData['currency'] ?? $shipping->getCurrency() ?? 'CNY');
                $shipping->setDeliveryTime($shippingData['deliveryTime'] ?? $shipping->getDeliveryTime());
                
                // 直接保存承运商名称，不进行ID转换
                $carriers = $shippingData['carriers'] ?? $shipping->getCarriers() ?? [];
                if (!empty($carriers) && is_array($carriers)) {
                    // 过滤并确保只保存名称格式的数据
                    $carrierNames = array_filter($carriers, function($carrier) {
                        return !empty($carrier) && is_string($carrier) && strpos($carrier, ' / ') !== false;
                    });
                    $shipping->setCarriers(array_values($carrierNames));
                } else {
                    $shipping->setCarriers([]);
                }
                
                $shipping->setIsDefault(true);
                $shipping->setIsActive(true);
                if (!$shipping->getId()) {
                    $entityManager->persist($shipping);
                }
            }

            $entityManager->flush();
            $entityManager->commit();

            return new JsonResponse([
                'success' => true,
                'message' => '商品更新成功'
            ]);
        } catch (\Exception $e) {
            $entityManager->rollback();
            return new JsonResponse([
                'success' => false,
                'message' => '商品更新失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 删除商品
     */
    #[Route('/product/delete/{id}', name: 'supplier_product_delete', methods: ['POST'])]
    public function delete(
        int $id,
        ProductRepository $productRepository,
        EntityManagerInterface $entityManager,
        string $supplierLoginPath
    ): JsonResponse {
        // 权限检查
        if (!$this->isGranted('ROLE_SUPPLIER_SUPER') && !$this->isGranted('ROLE_SUPPLIER_PRODUCT')) {
            return new JsonResponse(['success' => false, 'message' => 'Access Denied'], 403);
        }

        // 验证路径是否正确
        if ($supplierLoginPath !== PathConfigService::getSupplierLoginPath()) {
            return new JsonResponse(['success' => false, 'message' => 'Invalid supplier path'], 404);
        }

        // 获取主供应商账号（支持子账号）
        $supplier = $this->getMainSupplier();

        $product = $productRepository->find($id);

        if (!$product) {
            return new JsonResponse(['success' => false, 'message' => '商品不存在'], 404);
        }

        // 确保只能删除自己的商品
        if ($product->getSupplier()->getId() !== $supplier->getId()) {
            return new JsonResponse(['success' => false, 'message' => '无权删除此商品'], 403);
        }

        $entityManager->remove($product);
        $entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'message' => '商品删除成功'
        ]);
    }

    /**
     * 获取商品详情
     */
    #[Route('/product/detail/{id}', name: 'supplier_product_detail', methods: ['GET'])]
    public function detail(
        int $id,
        ProductRepository $productRepository,
        EntityManagerInterface $entityManager,
        string $supplierLoginPath
    ): JsonResponse {
        // 权限检查
        if (!$this->isGranted('ROLE_SUPPLIER_SUPER') && !$this->isGranted('ROLE_SUPPLIER_PRODUCT')) {
            return new JsonResponse(['success' => false, 'message' => 'Access Denied'], 403);
        }

        // 验证路径是否正确
        if ($supplierLoginPath !== PathConfigService::getSupplierLoginPath()) {
            return new JsonResponse(['success' => false, 'message' => 'Invalid supplier path'], 404);
        }

        // 获取主供应商账号（支持子账号）
        $supplier = $this->getMainSupplier();

        $product = $productRepository->find($id);

        if (!$product) {
            return new JsonResponse(['success' => false, 'message' => '商品不存在'], 404);
        }

        // 确保只能查看自己的商品
        if ($product->getSupplier()->getId() !== $supplier->getId()) {
            return new JsonResponse(['success' => false, 'message' => '无权查看此商品'], 403);
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

        // 处理详情图（已移除ProductImage实体，此处返回空数组）
        $detailImages = [];

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

        // 获取物流信息（直接返回数据库中的原始内容）
        $shippingData = null;
        $shipping = $product->getShippings()->first();
        if ($shipping) {
            $shippingData = [
                'id' => $shipping->getId(),
                'shippingMethod' => $shipping->getShippingMethod(),
                'shippingPrice' => $shipping->getShippingPrice(),
                'currency' => $shipping->getCurrency(),
                'deliveryTime' => $shipping->getDeliveryTime(),
                'carriers' => $shipping->getCarriers()  // 直接返回数据库中的原始承运商信息
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
            'detailImages' => [],
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
     * 文件上传 - 商品图片
     */
    #[Route('/product/upload-image', name: 'supplier_product_upload_image', methods: ['POST'])]
    public function uploadImage(Request $request, string $supplierLoginPath): JsonResponse
    {
        // 权限检查
        if (!$this->isGranted('ROLE_SUPPLIER_SUPER') && !$this->isGranted('ROLE_SUPPLIER_PRODUCT')) {
            return new JsonResponse(['success' => false, 'message' => 'Access Denied'], 403);
        }

        // 验证路径是否正确
        if ($supplierLoginPath !== PathConfigService::getSupplierLoginPath()) {
            return new JsonResponse(['success' => false, 'message' => 'Invalid supplier path'], 404);
        }

        // 获取主供应商账号（支持子账号）
        $supplier = $this->getMainSupplier();

        $uploadedFile = $request->files->get('image');

        if (!$uploadedFile) {
            return new JsonResponse(['success' => false, 'message' => '未上传文件'], 400);
        }

        // 验证文件类型
        $allowedMimeTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($uploadedFile->getMimeType(), $allowedMimeTypes)) {
            return new JsonResponse(['success' => false, 'message' => '不支持的文件类型'], 400);
        }

        // 验证文件大小（最大5MB）
        if ($uploadedFile->getSize() > 5 * 1024 * 1024) {
            return new JsonResponse(['success' => false, 'message' => '文件大小不能超过5MB'], 400);
        }

        try {
            $qiniuService = new QiniuUploadService();
            $extension = $uploadedFile->getClientOriginalExtension();
            $fileName = 'product_' . time() . '_' . uniqid() . '.' . $extension;

            $uploadResult = $qiniuService->uploadFile($uploadedFile->getPathname(), $fileName);

            if ($uploadResult['success']) {
                return new JsonResponse([
                    'success' => true,
                    'url' => $uploadResult['key'],
                    'key' => $uploadResult['key'],
                    'previewUrl' => $uploadResult['url'],
                    'message' => '图片上传成功'
                ]);
            } else {
                return new JsonResponse([
                    'success' => false,
                    'message' => '上传失败：' . $uploadResult['error']
                ], 500);
            }
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => '上传异常：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取状态文本
     */
    private function getStatusText(string $status): string
    {
        $statusMap = [
            'draft' => '草稿',
            'pending' => '待审核',
            'approved' => '已上架',
            'rejected' => '已拒绝',
            'offline' => '已下架',
        ];

        return $statusMap[$status] ?? $status;
    }

    /**
     * 获取会员等级选项
     */
    #[Route('/product/vip-levels', name: 'supplier_product_vip_levels', methods: ['GET'])]
    public function getVipLevels(string $supplierLoginPath): JsonResponse
    {
        // 权限检查
        if (!$this->isGranted('ROLE_SUPPLIER_SUPER') && !$this->isGranted('ROLE_SUPPLIER_PRODUCT')) {
            return new JsonResponse(['success' => false, 'message' => 'Access Denied'], 403);
        }

        // 验证路径是否正确
        if ($supplierLoginPath !== PathConfigService::getSupplierLoginPath()) {
            return new JsonResponse(['success' => false, 'message' => 'Invalid supplier path'], 404);
        }

        // 从 VipLevel 类获取会员等级信息
        $vipLevels = \App\common\VipLevel::getLevelOptions();

        $data = [];
        foreach ($vipLevels as $value => $label) {
            $data[] = [
                'value' => $value,
                'label' => $label
            ];
        }

        return new JsonResponse([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * 获取物流公司列表
     */
    #[Route('/product/logistics-companies', name: 'supplier_product_logistics_companies', methods: ['GET'])]
    public function getLogisticsCompanies(
        LogisticsCompanyService $logisticsCompanyService,
        string $supplierLoginPath
    ): JsonResponse {
        // 权限检查
        if (!$this->isGranted('ROLE_SUPPLIER_SUPER') && !$this->isGranted('ROLE_SUPPLIER_PRODUCT')) {
            return new JsonResponse(['success' => false, 'message' => 'Access Denied'], 403);
        }

        // 验证路径是否正确
        if ($supplierLoginPath !== PathConfigService::getSupplierLoginPath()) {
            return new JsonResponse(['success' => false, 'message' => 'Invalid supplier path'], 404);
        }

        // 获取所有启用的物流公司，按热度排序
        $companies = $logisticsCompanyService->getActiveLogisticsCompanies();

        return new JsonResponse([
            'success' => true,
            'data' => $companies
        ]);
    }

    /**
     * 生成SKU
     */
    private function generateSku(): string
    {
        // 确保生成的SKU是唯一的
        $sku = 'SKU' . time() . rand(1000, 9999);
        return $sku;
    }

    /**
     * 生成SPU
     */
    private function generateSpu(): string
    {
        // 确保生成的SPU是唯一的
        $spu = 'SPU' . time() . rand(1000, 9999);
        return $spu;
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
     * 验证是否为整数
     */
    private function isValidInteger($value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        // 检查是否为有效数字
        if (!is_numeric($value)) {
            return false;
        }

        // 检查是否为整数
        return floor($value) == $value;
    }
}