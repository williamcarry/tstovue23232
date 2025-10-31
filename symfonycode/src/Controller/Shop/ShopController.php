<?php

namespace App\Controller\Shop;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/shop', name: 'shop_')]
class ShopController extends AbstractController
{
    /**
     * 首页 - 展示全部商品
     */
    #[Route('/', name: 'index', methods: ['GET'])]
    #[Route('/all-products', name: 'all_products', methods: ['GET'])]
    public function index(Request $request): Response
    {
        // 从 $_GET 或请求参数获取筛选条件
        $filters = [
            'warehouse' => $request->query->get('warehouse'),
            'newTime' => $request->query->get('newTime'),
            'shipment' => $request->query->get('shipment'),
            'saleMode' => $request->query->get('saleMode'),
            'category1' => $request->query->get('category1'),
            'sortBy' => $request->query->get('sortBy', 'default'),
            'page' => (int)$request->query->get('page', 1),
            'pageSize' => (int)$request->query->get('pageSize', 20),
        ];

        // 模拟数据 - 实际项目中从数据库获取
        $products = $this->getMockProducts();
        
        // 获取筛选选项数据
        $filterOptions = [
            'warehouses' => $this->getWarehouses(),
            'categories' => $this->getCategories(),
            'newTimeOptions' => $this->getNewTimeOptions(),
            'shipmentOptions' => $this->getShipmentOptions(),
            'saleModeOptions' => $this->getSaleModeOptions(),
        ];

        return $this->render('shop/index.html.twig', [
            'products' => json_encode($products),
            'filters' => json_encode($filters),
            'filterOptions' => json_encode($filterOptions),
            'pageTitle' => '全部商品',
        ]);
    }

    /**
     * 商品详情页
     */
    #[Route('/product/{id}', name: 'product_detail', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function productDetail(int $id, Request $request): Response
    {
        // 从数据库获取商品详情（模拟）
        $product = $this->getMockProductDetail($id);

        if (!$product) {
            throw $this->createNotFoundException('商品不存在');
        }

        // 获取相关商品
        $relatedProducts = $this->getMockRelatedProducts($id);

        return $this->render('shop/product-detail.html.twig', [
            'product' => json_encode($product),
            'productId' => $id,
            'relatedProducts' => json_encode($relatedProducts),
            'pageTitle' => $product['name'] ?? '商品详情',
        ]);
    }

    /**
     * 热销商品页面
     */
    #[Route('/hot-sales', name: 'hot_sales', methods: ['GET'])]
    public function hotSales(): Response
    {
        $products = $this->getMockHotSalesProducts();

        return $this->render('shop/hot-sales.html.twig', [
            'products' => json_encode($products),
            'pageTitle' => '热销商品',
        ]);
    }

    /**
     * 折扣销售页面
     */
    #[Route('/discount-sale', name: 'discount_sale', methods: ['GET'])]
    public function discountSale(): Response
    {
        $products = $this->getMockDiscountProducts();

        return $this->render('shop/discount-sale.html.twig', [
            'products' => json_encode($products),
            'pageTitle' => '折扣商品',
        ]);
    }

    /**
     * 新品上市页面
     */
    #[Route('/new', name: 'new_products', methods: ['GET'])]
    public function newProducts(): Response
    {
        $products = $this->getMockNewProducts();

        return $this->render('shop/new-products.html.twig', [
            'products' => json_encode($products),
            'pageTitle' => '新品上市',
        ]);
    }

    /**
     * 直发页面
     */
    #[Route('/direct-delivery', name: 'direct_delivery', methods: ['GET'])]
    public function directDelivery(): Response
    {
        $products = $this->getMockDirectDeliveryProducts();

        return $this->render('shop/direct-delivery.html.twig', [
            'products' => json_encode($products),
            'pageTitle' => '直发商品',
        ]);
    }

    /**
     * 分类浏览页面
     */
    #[Route('/categories', name: 'categories', methods: ['GET'])]
    public function categories(): Response
    {
        $categories = $this->getCategories();

        return $this->render('shop/categories.html.twig', [
            'categories' => json_encode($categories),
            'pageTitle' => '全部分类',
        ]);
    }

    /**
     * API: 获取商品列表（用于AJAX请求）
     */
    #[Route('/api/products', name: 'api_products', methods: ['GET'])]
    public function apiProducts(Request $request): JsonResponse
    {
        $filters = [
            'warehouse' => $request->query->get('warehouse'),
            'newTime' => $request->query->get('newTime'),
            'shipment' => $request->query->get('shipment'),
            'saleMode' => $request->query->get('saleMode'),
            'category1' => $request->query->get('category1'),
            'sortBy' => $request->query->get('sortBy', 'default'),
            'page' => (int)$request->query->get('page', 1),
            'pageSize' => (int)$request->query->get('pageSize', 20),
        ];

        // 应用筛选逻辑
        $products = $this->getMockProducts();
        $filteredProducts = $this->applyFilters($products, $filters);

        // 分页处理
        $totalCount = count($filteredProducts);
        $offset = ($filters['page'] - 1) * $filters['pageSize'];
        $paginatedProducts = array_slice($filteredProducts, $offset, $filters['pageSize']);

        return $this->json([
            'success' => true,
            'data' => $paginatedProducts,
            'total' => $totalCount,
            'page' => $filters['page'],
            'pageSize' => $filters['pageSize'],
            'totalPages' => ceil($totalCount / $filters['pageSize']),
        ]);
    }

    /**
     * API: 获取商品详情
     */
    #[Route('/api/product/{id}', name: 'api_product_detail', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function apiProductDetail(int $id): JsonResponse
    {
        $product = $this->getMockProductDetail($id);

        if (!$product) {
            return $this->json(['success' => false, 'message' => '商品不存在'], 404);
        }

        return $this->json([
            'success' => true,
            'data' => $product,
        ]);
    }

    // ==================== 辅助方法 ====================

    private function getMockProducts(): array
    {
        return [
            [
                'id' => 1,
                'name' => '电子产品 A',
                'price' => 199.99,
                'originalPrice' => 299.99,
                'warehouse' => 'warehouse_1',
                'newTime' => '7days',
                'shipment' => 'express',
                'saleMode' => 'normal',
                'category1' => 'electronics',
                'image' => 'https://via.placeholder.com/300x300?text=Product+1',
                'rating' => 4.5,
                'reviews' => 128,
                'sales' => 1250,
            ],
            [
                'id' => 2,
                'name' => '家居用品 B',
                'price' => 79.99,
                'originalPrice' => 99.99,
                'warehouse' => 'warehouse_2',
                'newTime' => '30days',
                'shipment' => 'standard',
                'saleMode' => 'discount',
                'category1' => 'home',
                'image' => 'https://via.placeholder.com/300x300?text=Product+2',
                'rating' => 4.2,
                'reviews' => 89,
                'sales' => 856,
            ],
            [
                'id' => 3,
                'name' => '服装配饰 C',
                'price' => 49.99,
                'originalPrice' => 69.99,
                'warehouse' => 'warehouse_1',
                'newTime' => '3days',
                'shipment' => 'express',
                'saleMode' => 'hot',
                'category1' => 'apparel',
                'image' => 'https://via.placeholder.com/300x300?text=Product+3',
                'rating' => 4.7,
                'reviews' => 234,
                'sales' => 3421,
            ],
        ];
    }

    private function getMockProductDetail(int $id): ?array
    {
        $baseProduct = [
            'id' => $id,
            'name' => "商品 #$id",
            'price' => 199.99,
            'originalPrice' => 299.99,
            'warehouse' => 'warehouse_1',
            'newTime' => '7days',
            'shipment' => 'express',
            'saleMode' => 'normal',
            'category1' => 'electronics',
            'category2' => 'electronics_sub',
            'category3' => 'electronics_detail',
            'image' => "https://via.placeholder.com/600x600?text=Product+$id",
            'images' => [
                "https://via.placeholder.com/600x600?text=Product+$id+View+1",
                "https://via.placeholder.com/600x600?text=Product+$id+View+2",
                "https://via.placeholder.com/600x600?text=Product+$id+View+3",
                "https://via.placeholder.com/600x600?text=Product+$id+View+4",
            ],
            'rating' => 4.5,
            'reviews' => 128,
            'sales' => 1250,
            'description' => "这是商品 #$id 的详细描述。包含详细的产品信息、规格说明和使用说明。",
            'specs' => [
                ['name' => '颜色', 'value' => '黑色, 白色, 蓝色'],
                ['name' => '尺寸', 'value' => 'S, M, L, XL'],
                ['name' => '材质', 'value' => '棉麻混纺'],
                ['name' => '产地', 'value' => '中国'],
            ],
            'tags' => ['热销', '新品', '折扣'],
            'sku' => "SKU-$id-001",
            'stock' => 450,
            'minOrder' => 1,
            'supplier' => '供应商 ' . chr(64 + ($id % 5)),
            'shippingFee' => 10,
        ];

        return $baseProduct;
    }

    private function getMockRelatedProducts(int $currentId): array
    {
        $products = [];
        for ($i = 1; $i <= 4; $i++) {
            if ($i !== $currentId) {
                $id = $currentId + $i;
                $products[] = [
                    'id' => $id,
                    'name' => "相关商品 #$id",
                    'price' => 99.99 + ($i * 10),
                    'originalPrice' => 149.99 + ($i * 10),
                    'image' => "https://via.placeholder.com/300x300?text=Related+$id",
                    'rating' => 4.0 + ($i * 0.1),
                    'sales' => 500 + ($i * 100),
                ];
            }
        }
        return $products;
    }

    private function getMockHotSalesProducts(): array
    {
        return array_slice($this->getMockProducts(), 0, 12);
    }

    private function getMockDiscountProducts(): array
    {
        return array_slice($this->getMockProducts(), 0, 12);
    }

    private function getMockNewProducts(): array
    {
        return array_slice($this->getMockProducts(), 0, 12);
    }

    private function getMockDirectDeliveryProducts(): array
    {
        return array_slice($this->getMockProducts(), 0, 12);
    }

    private function getWarehouses(): array
    {
        return [
            ['id' => 'warehouse_1', 'name' => '仓库A'],
            ['id' => 'warehouse_2', 'name' => '仓库B'],
            ['id' => 'warehouse_3', 'name' => '仓库C'],
        ];
    }

    private function getCategories(): array
    {
        return [
            ['id' => 'electronics', 'name' => '电子产品', 'icon' => '📱'],
            ['id' => 'home', 'name' => '家居用品', 'icon' => '🏠'],
            ['id' => 'apparel', 'name' => '服装配饰', 'icon' => '👕'],
            ['id' => 'sports', 'name' => '运动户外', 'icon' => '⚽'],
            ['id' => 'beauty', 'name' => '美妆护肤', 'icon' => '💄'],
        ];
    }

    private function getNewTimeOptions(): array
    {
        return [
            ['label' => '24小时', 'value' => '1day'],
            ['label' => '3天内', 'value' => '3days'],
            ['label' => '7天内', 'value' => '7days'],
            ['label' => '30天内', 'value' => '30days'],
        ];
    }

    private function getShipmentOptions(): array
    {
        return [
            ['label' => '快速发货', 'value' => 'express'],
            ['label' => '标准发货', 'value' => 'standard'],
            ['label' => '直发', 'value' => 'direct'],
        ];
    }

    private function getSaleModeOptions(): array
    {
        return [
            ['label' => '普通交易', 'value' => 'normal'],
            ['label' => '折扣销售', 'value' => 'discount'],
            ['label' => '热销', 'value' => 'hot'],
            ['label' => '秒杀', 'value' => 'flash'],
        ];
    }

    private function applyFilters(array $products, array $filters): array
    {
        return array_filter($products, function ($product) use ($filters) {
            if ($filters['warehouse'] && $product['warehouse'] !== $filters['warehouse']) {
                return false;
            }
            if ($filters['newTime'] && $product['newTime'] !== $filters['newTime']) {
                return false;
            }
            if ($filters['shipment'] && $product['shipment'] !== $filters['shipment']) {
                return false;
            }
            if ($filters['saleMode'] && $product['saleMode'] !== $filters['saleMode']) {
                return false;
            }
            if ($filters['category1'] && $product['category1'] !== $filters['category1']) {
                return false;
            }
            return true;
        });
    }
}
