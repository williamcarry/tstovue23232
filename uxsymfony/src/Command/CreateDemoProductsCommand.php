<?php

namespace App\Command;

use App\Entity\Product;

use App\Entity\ProductStock;
use App\Entity\ProductPrice;
use App\Entity\ProductShipping;
use App\Service\QiniuUploadService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:create-demo-products',
    description: '为不同供应商随机生成35个商品demo，供应商ID必须在供应商列表中存在',
)]
class CreateDemoProductsCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private QiniuUploadService $qiniuService;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->qiniuService = new QiniuUploadService();
    }

    protected function configure(): void
    {
        // 移除原来的supplier_id参数，改为无参数执行
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('开始为不同供应商创建Demo商品');

        // 获取所有供应商
        $supplierRepository = $this->entityManager->getRepository(\App\Entity\Supplier::class);
        $suppliers = $supplierRepository->findAll();
        
        if (empty($suppliers)) {
            $io->error("没有找到任何供应商！");
            return Command::FAILURE;
        }

        $io->text("找到 " . count($suppliers) . " 个供应商");

        // 定义商品数据模板（扩展到35个商品）
        $productTemplates = [
            [
                'title' => '时尚休闲运动鞋 男女同款',
                'titleEn' => 'Fashion Casual Sports Shoes Unisex',
                'mainImages' => ['aaaaa/主图.jpg'], // 主图改为数组格式
                'detailImages' => ['aaaaa/详情图.jpg', 'aaaaa/详情图1.jpg'],
                'description' => '采用透气网面设计，轻便舒适，适合日常运动和休闲穿搭',
                'brand' => 'SportMax',
                'category' => '鞋靴',
                'subcategory' => '运动鞋',
                'basePrice' => '299.00',
                'stock' => 500
            ],
            [
                'title' => '春夏新款纯棉T恤 简约百搭款',
                'titleEn' => 'Spring Summer New Cotton T-shirt Simple Versatile',
                'mainImages' => ['aaaaa/主图1.jpg'], // 主图改为数组格式
                'detailImages' => ['aaaaa/详情图2.jpg', 'aaaaa/详情图3.jpg'],
                'description' => '100%纯棉面料，亲肤透气，多色可选，适合春夏季节穿着',
                'brand' => 'BasicStyle',
                'category' => '服装',
                'subcategory' => 'T恤',
                'basePrice' => '89.00',
                'stock' => 1000
            ],
            [
                'title' => '商务休闲双肩包 大容量电脑包',
                'titleEn' => 'Business Casual Backpack Large Capacity Computer Bag',
                'mainImages' => ['aaaaa/主图2.jpg'], // 主图改为数组格式
                'detailImages' => ['aaaaa/详情图4.jpg', 'aaaaa/详情图5.jpg'],
                'description' => '防水材质，独立电脑仓，USB充电接口，适合商务出行',
                'brand' => 'TechPack',
                'category' => '箱包',
                'subcategory' => '双肩包',
                'basePrice' => '399.00',
                'stock' => 300
            ],
            [
                'title' => '智能蓝牙耳机 降噪通话 长续航',
                'titleEn' => 'Smart Bluetooth Earphone Noise Cancelling Long Battery Life',
                'mainImages' => ['aaaaa/主图3.jpg'], // 主图改为数组格式
                'detailImages' => ['aaaaa/详情图1.jpg', 'aaaaa/详情图2.jpg'],
                'description' => 'CVC8.0降噪技术，30小时续航，IPX7防水，支持快充',
                'brand' => 'SoundPro',
                'category' => '数码',
                'subcategory' => '耳机',
                'basePrice' => '599.00',
                'stock' => 800
            ],
            [
                'title' => '真皮商务手表 机械表 男士腕表',
                'titleEn' => 'Genuine Leather Business Watch Mechanical Watch Men',
                'mainImages' => ['aaaaa/主图4.jpg'], // 主图改为数组格式
                'detailImages' => ['aaaaa/详情图3.jpg', 'aaaaa/详情图4.jpg', 'aaaaa/详情图5.jpg'],
                'description' => '自动机械机芯，蓝宝石表镜，真皮表带，50米防水',
                'brand' => 'TimeMaster',
                'category' => '配饰',
                'subcategory' => '手表',
                'basePrice' => '1299.00',
                'stock' => 200
            ],
            [
                'title' => '无线充电器 快充版 支持多设备',
                'titleEn' => 'Wireless Charger Fast Charging Support Multiple Devices',
                'mainImages' => ['aaaaa/主图5.jpg'], // 主图改为数组格式
                'detailImages' => ['aaaaa/详情图6.jpg', 'aaaaa/详情图7.jpg'],
                'description' => '支持Qi协议，15W快充，智能识别设备，多重安全保护',
                'brand' => 'ChargeTech',
                'category' => '数码',
                'subcategory' => '充电器',
                'basePrice' => '129.00',
                'stock' => 1500
            ],
            [
                'title' => '户外野营帐篷 双人防雨三季帐篷',
                'titleEn' => 'Outdoor Camping Tent Double Person Waterproof Three-Season Tent',
                'mainImages' => ['aaaaa/主图6.jpg'], // 主图改为数组格式
                'detailImages' => ['aaaaa/详情图8.jpg', 'aaaaa/详情图9.jpg'],
                'description' => '防水涂层，轻量化设计，快速搭建，适合家庭露营',
                'brand' => 'NatureHike',
                'category' => '户外',
                'subcategory' => '帐篷',
                'basePrice' => '499.00',
                'stock' => 400
            ],
            [
                'title' => '不锈钢保温杯 大容量真空保温',
                'titleEn' => 'Stainless Steel Thermos Large Capacity Vacuum Insulated',
                'mainImages' => ['aaaaa/主图7.jpg'], // 主图改为数组格式
                'detailImages' => ['aaaaa/详情图10.jpg', 'aaaaa/详情图11.jpg'],
                'description' => '304不锈钢材质，24小时保温保冷，防漏设计，便携把手',
                'brand' => 'ThermoMax',
                'category' => '家居',
                'subcategory' => '水杯',
                'basePrice' => '159.00',
                'stock' => 2000
            ],
            [
                'title' => '电动牙刷 声波震动 清洁升级',
                'titleEn' => 'Electric Toothbrush Sonic Vibration Cleaning Upgrade',
                'mainImages' => ['aaaaa/主图8.jpg'], // 主图改为数组格式
                'detailImages' => ['aaaaa/详情图12.jpg', 'aaaaa/详情图13.jpg'],
                'description' => '声波震动技术，31000次/分钟，多种清洁模式，IPX7防水',
                'brand' => 'CleanSmile',
                'category' => '个护',
                'subcategory' => '电动牙刷',
                'basePrice' => '199.00',
                'stock' => 1200
            ],
            [
                'title' => '瑜伽垫 加厚防滑 瑜伽健身必备',
                'titleEn' => 'Yoga Mat Thick Anti-slip Yoga Fitness Essential',
                'mainImages' => ['aaaaa/主图9.jpg'], // 主图改为数组格式
                'detailImages' => ['aaaaa/详情图14.jpg', 'aaaaa/详情图15.jpg'],
                'description' => '天然橡胶材质，6mm加厚，防滑纹理，环保无味，易于收纳',
                'brand' => 'FitLife',
                'category' => '运动',
                'subcategory' => '瑜伽用品',
                'basePrice' => '129.00',
                'stock' => 800
            ],
            // 新增商品模板以达到35个商品
            [
                'title' => '多功能料理锅 家用煎烤蒸煮一体',
                'titleEn' => 'Multi-functional Cooking Pot Home Frying Roasting Steaming Boiling All-in-one',
                'mainImages' => ['aaaaa/主图.jpg'], // 主图改为数组格式
                'detailImages' => ['aaaaa/详情图.jpg', 'aaaaa/详情图1.jpg'],
                'description' => '九大功能合一，智能控温，不粘涂层，健康少油烟',
                'brand' => 'KitchenPro',
                'category' => '家居',
                'subcategory' => '厨房电器',
                'basePrice' => '399.00',
                'stock' => 600
            ],
            [
                'title' => '儿童益智拼图玩具 早教启蒙',
                'titleEn' => 'Children Educational Puzzle Toy Early Learning',
                'mainImages' => ['aaaaa/主图1.jpg'], // 主图改为数组格式
                'detailImages' => ['aaaaa/详情图2.jpg', 'aaaaa/详情图3.jpg'],
                'description' => '环保材质，安全无毒，多种图案，开发智力',
                'brand' => 'KidSmart',
                'category' => '母婴',
                'subcategory' => '玩具',
                'basePrice' => '59.00',
                'stock' => 1500
            ],
            [
                'title' => '防晒霜 SPF50+ PA++++',
                'titleEn' => 'Sunscreen SPF50+ PA++++',
                'mainImages' => ['aaaaa/主图2.jpg'], // 主图改为数组格式
                'detailImages' => ['aaaaa/详情图4.jpg', 'aaaaa/详情图5.jpg'],
                'description' => '清爽不油腻，防水防汗，适合户外活动',
                'brand' => 'SkinCare',
                'category' => '美妆',
                'subcategory' => '防晒',
                'basePrice' => '89.00',
                'stock' => 2000
            ],
            [
                'title' => '车载手机支架 磁吸无线充',
                'titleEn' => 'Car Phone Mount Magnetic Wireless Charging',
                'mainImages' => ['aaaaa/主图3.jpg'], // 主图改为数组格式
                'detailImages' => ['aaaaa/详情图1.jpg', 'aaaaa/详情图2.jpg'],
                'description' => '强力磁吸，360度旋转，支持无线充电',
                'brand' => 'AutoTech',
                'category' => '汽车',
                'subcategory' => '车载配件',
                'basePrice' => '129.00',
                'stock' => 1200
            ],
            [
                'title' => '办公椅 人体工学转椅',
                'titleEn' => 'Office Chair Ergonomic Swivel Chair',
                'mainImages' => ['aaaaa/主图4.jpg'], // 主图改为数组格式
                'detailImages' => ['aaaaa/详情图3.jpg', 'aaaaa/详情图4.jpg'],
                'description' => '人体工学设计，可调节高度，舒适透气网布',
                'brand' => 'OfficeComfort',
                'category' => '家具',
                'subcategory' => '办公家具',
                'basePrice' => '599.00',
                'stock' => 300
            ],
            [
                'title' => '咖啡豆 精品阿拉比卡',
                'titleEn' => 'Coffee Beans Premium Arabica',
                'mainImages' => ['aaaaa/主图5.jpg'], // 主图改为数组格式
                'detailImages' => ['aaaaa/详情图6.jpg', 'aaaaa/详情图7.jpg'],
                'description' => '100%阿拉比卡咖啡豆，中深度烘焙，香醇浓郁',
                'brand' => 'CoffeeMaster',
                'category' => '食品',
                'subcategory' => '咖啡',
                'basePrice' => '69.00',
                'stock' => 2000
            ],
            [
                'title' => '蓝牙音箱 户外便携防水',
                'titleEn' => 'Bluetooth Speaker Outdoor Portable Waterproof',
                'mainImages' => ['aaaaa/主图6.jpg'], // 主图改为数组格式
                'detailImages' => ['aaaaa/详情图8.jpg', 'aaaaa/详情图9.jpg'],
                'description' => 'IPX7防水，20小时续航，震撼低音，户外必备',
                'brand' => 'SoundBoom',
                'category' => '数码',
                'subcategory' => '音箱',
                'basePrice' => '199.00',
                'stock' => 800
            ],
            [
                'title' => '电动剃须刀 三刀头智能',
                'titleEn' => 'Electric Shaver Triple Blade Smart',
                'mainImages' => ['aaaaa/主图7.jpg'], // 主图改为数组格式
                'detailImages' => ['aaaaa/详情图10.jpg', 'aaaaa/详情图11.jpg'],
                'description' => '浮动刀头，智能感应，干湿两用，快充续航',
                'brand' => 'GroomTech',
                'category' => '个护',
                'subcategory' => '剃须',
                'basePrice' => '299.00',
                'stock' => 1000
            ],
            [
                'title' => '儿童自行车 12寸平衡车',
                'titleEn' => 'Children Bicycle 12-inch Balance Bike',
                'mainImages' => ['aaaaa/主图8.jpg'], // 主图改为数组格式
                'detailImages' => ['aaaaa/详情图12.jpg', 'aaaaa/详情图13.jpg'],
                'description' => '轻量铝合金，可调节座椅，培养平衡感',
                'brand' => 'KidRide',
                'category' => '运动',
                'subcategory' => '自行车',
                'basePrice' => '199.00',
                'stock' => 500
            ],
            [
                'title' => '按摩椅 全身零重力',
                'titleEn' => 'Massage Chair Full Body Zero Gravity',
                'mainImages' => ['aaaaa/主图9.jpg'], // 主图改为数组格式
                'detailImages' => ['aaaaa/详情图14.jpg', 'aaaaa/详情图15.jpg'],
                'description' => 'SL导轨机芯，全身按摩，零重力体验，智能检测',
                'brand' => 'RelaxPro',
                'category' => '家居',
                'subcategory' => '按摩器材',
                'basePrice' => '8999.00',
                'stock' => 50
            ],
            [
                'title' => '游戏手柄 无线蓝牙',
                'titleEn' => 'Game Controller Wireless Bluetooth',
                'mainImages' => ['aaaaa/主图.jpg'], // 主图改为数组格式
                'detailImages' => ['aaaaa/详情图.jpg', 'aaaaa/详情图1.jpg'],
                'description' => '人体工学设计，低延迟，兼容多平台',
                'brand' => 'GameMax',
                'category' => '数码',
                'subcategory' => '游戏配件',
                'basePrice' => '199.00',
                'stock' => 1000
            ],
            [
                'title' => '空气炸锅 容量升级',
                'titleEn' => 'Air Fryer Upgraded Capacity',
                'mainImages' => ['aaaaa/主图1.jpg'], // 主图改为数组格式
                'detailImages' => ['aaaaa/详情图2.jpg', 'aaaaa/详情图3.jpg'],
                'description' => '健康少油，智能菜单，易清洗，家庭必备',
                'brand' => 'KitchenMaster',
                'category' => '家居',
                'subcategory' => '厨房电器',
                'basePrice' => '299.00',
                'stock' => 800
            ],
            [
                'title' => '婴儿推车 轻便折叠',
                'titleEn' => 'Baby Stroller Lightweight Foldable',
                'mainImages' => ['aaaaa/主图2.jpg'], // 主图改为数组格式
                'detailImages' => ['aaaaa/详情图4.jpg', 'aaaaa/详情图5.jpg'],
                'description' => '一键收车，避震轮子，可躺可坐，出行方便',
                'brand' => 'BabyCare',
                'category' => '母婴',
                'subcategory' => '出行用品',
                'basePrice' => '599.00',
                'stock' => 300
            ],
            [
                'title' => '智能门锁 指纹密码',
                'titleEn' => 'Smart Door Lock Fingerprint Password',
                'mainImages' => ['aaaaa/主图3.jpg'], // 主图改为数组格式
                'detailImages' => ['aaaaa/详情图1.jpg', 'aaaaa/详情图2.jpg'],
                'description' => '多种开锁方式，防撬报警，远程控制，安全可靠',
                'brand' => 'HomeSecure',
                'category' => '家居',
                'subcategory' => '智能家居',
                'basePrice' => '899.00',
                'stock' => 200
            ],
            [
                'title' => '跑步机 家用静音',
                'titleEn' => 'Treadmill Home Quiet',
                'mainImages' => ['aaaaa/主图4.jpg'], // 主图改为数组格式
                'detailImages' => ['aaaaa/详情图3.jpg', 'aaaaa/详情图4.jpg'],
                'description' => '静音马达，减震跑板，多种训练程序，健康管理',
                'brand' => 'FitnessPlus',
                'category' => '运动',
                'subcategory' => '健身器材',
                'basePrice' => '1999.00',
                'stock' => 100
            ],
            [
                'title' => '电动牙刷头 多效护理',
                'titleEn' => 'Electric Toothbrush Heads Multi-care',
                'mainImages' => ['aaaaa/主图5.jpg'], // 主图改为数组格式
                'detailImages' => ['aaaaa/详情图6.jpg', 'aaaaa/详情图7.jpg'],
                'description' => '原装替换装，深层清洁，呵护牙龈，4支装',
                'brand' => 'CleanSmile',
                'category' => '个护',
                'subcategory' => '口腔护理',
                'basePrice' => '99.00',
                'stock' => 3000
            ],
            [
                'title' => '移动电源 30000mAh快充',
                'titleEn' => 'Power Bank 30000mAh Fast Charging',
                'mainImages' => ['aaaaa/主图6.jpg'], // 主图改为数组格式
                'detailImages' => ['aaaaa/详情图8.jpg', 'aaaaa/详情图9.jpg'],
                'description' => '双向快充，数显屏，自带数据线，多设备适用',
                'brand' => 'PowerMax',
                'category' => '数码',
                'subcategory' => '移动电源',
                'basePrice' => '159.00',
                'stock' => 1500
            ],
            [
                'title' => '加湿器 静音大雾量',
                'titleEn' => 'Humidifier Silent High Mist Output',
                'mainImages' => ['aaaaa/主图7.jpg'], // 主图改为数组格式
                'detailImages' => ['aaaaa/详情图10.jpg', 'aaaaa/详情图11.jpg'],
                'description' => '超声波技术，静音运行，大容量水箱，智能恒湿',
                'brand' => 'AirFresh',
                'category' => '家居',
                'subcategory' => '环境电器',
                'basePrice' => '129.00',
                'stock' => 1000
            ],
            [
                'title' => '冲锋衣 三合一户外',
                'titleEn' => 'Soft Shell Jacket Three-in-one Outdoor',
                'mainImages' => ['aaaaa/主图8.jpg'], // 主图改为数组格式
                'detailImages' => ['aaaaa/详情图12.jpg', 'aaaaa/详情图13.jpg'],
                'description' => '防风防水，保暖透气，多口袋设计，户外探险',
                'brand' => 'OutdoorKing',
                'category' => '服装',
                'subcategory' => '户外服',
                'basePrice' => '499.00',
                'stock' => 400
            ],
            [
                'title' => '红酒 开瓶器电动',
                'titleEn' => 'Red Wine Electric Corkscrew',
                'mainImages' => ['aaaaa/主图9.jpg'], // 主图改为数组格式
                'detailImages' => ['aaaaa/详情图14.jpg', 'aaaaa/详情图15.jpg'],
                'description' => '一键开启，优雅便捷，适用于各种酒瓶',
                'brand' => 'WineMaster',
                'category' => '家居',
                'subcategory' => '酒具',
                'basePrice' => '199.00',
                'stock' => 500
            ],
            [
                'title' => '儿童学习桌椅套装',
                'titleEn' => 'Children Study Desk and Chair Set',
                'mainImages' => ['aaaaa/主图.jpg'], // 主图改为数组格式
                'detailImages' => ['aaaaa/详情图.jpg', 'aaaaa/详情图1.jpg'],
                'description' => '可升降调节，环保材质，保护视力，健康成长',
                'brand' => 'KidsStudy',
                'category' => '家具',
                'subcategory' => '儿童家具',
                'basePrice' => '899.00',
                'stock' => 200
            ],
            [
                'title' => '无线鼠标 办公静音',
                'titleEn' => 'Wireless Mouse Office Silent',
                'mainImages' => ['aaaaa/主图1.jpg'], // 主图改为数组格式
                'detailImages' => ['aaaaa/详情图2.jpg', 'aaaaa/详情图3.jpg'],
                'description' => '人体工学设计，静音按键，长续航，精准定位',
                'brand' => 'MousePro',
                'category' => '数码',
                'subcategory' => '鼠标',
                'basePrice' => '79.00',
                'stock' => 2000
            ],
            [
                'title' => '护眼台灯 LED智能调光',
                'titleEn' => 'Eye Protection Desk Lamp LED Smart Dimming',
                'mainImages' => ['aaaaa/主图2.jpg'], // 主图改为数组格式
                'detailImages' => ['aaaaa/详情图4.jpg', 'aaaaa/详情图5.jpg'],
                'description' => '无频闪护眼，多档调光，触摸控制，学习工作好伴侣',
                'brand' => 'LightCare',
                'category' => '家居',
                'subcategory' => '灯具',
                'basePrice' => '129.00',
                'stock' => 1000
            ],
            [
                'title' => '电热水壶 不锈钢快速',
                'titleEn' => 'Electric Kettle Stainless Steel Fast',
                'mainImages' => ['aaaaa/主图3.jpg'], // 主图改为数组格式
                'detailImages' => ['aaaaa/详情图1.jpg', 'aaaaa/详情图2.jpg'],
                'description' => '304不锈钢内胆，快速沸腾，自动断电，安全放心',
                'brand' => 'WaterBoil',
                'category' => '家居',
                'subcategory' => '厨房小电',
                'basePrice' => '89.00',
                'stock' => 1500
            ],
            [
                'title' => '行李箱 20寸登机箱',
                'titleEn' => 'Luggage 20-inch Carry-on Suitcase',
                'mainImages' => ['aaaaa/主图4.jpg'], // 主图改为数组格式
                'detailImages' => ['aaaaa/详情图3.jpg', 'aaaaa/详情图4.jpg'],
                'description' => 'PC材质轻便耐用，万向轮静音顺滑，TSA海关锁',
                'brand' => 'TravelPro',
                'category' => '箱包',
                'subcategory' => '行李箱',
                'basePrice' => '299.00',
                'stock' => 600
            ],
        ];

        $created = 0;
        $timestamp = time();
        
        // 为每个供应商创建商品，总共35个商品
        // 计算每个供应商需要创建多少个商品
        $totalProducts = 35;
        $productsPerSupplier = intval($totalProducts / count($suppliers));
        $remainingProducts = $totalProducts % count($suppliers);
        
        $io->text("每个供应商将创建大约 {$productsPerSupplier} 个商品，剩余 {$remainingProducts} 个商品将分配给前几个供应商");
        
        $supplierIndex = 0;
        foreach ($suppliers as $supplier) {
            $io->section("为供应商 {$supplier->getUsername()} (ID: {$supplier->getId()}) 创建商品");
            
            // 计算当前供应商需要创建的商品数量
            $currentSupplierProductCount = $productsPerSupplier;
            if ($supplierIndex < $remainingProducts) {
                $currentSupplierProductCount++;
            }
            
            // 为当前供应商随机选择指定数量的商品模板
            $availableTemplateIndices = array_keys($productTemplates);
            $selectedTemplates = [];
            
            // 确保不会超出模板数量
            $actualProductCount = min($currentSupplierProductCount, count($availableTemplateIndices));
            
            // 随机选择不重复的商品模板
            shuffle($availableTemplateIndices);
            $selectedTemplates = array_slice($availableTemplateIndices, 0, $actualProductCount);
            
            foreach ($selectedTemplates as $templateIndex) {
                $data = $productTemplates[$templateIndex];
                
                // 生成随机SKU和SPU
                $randomNumber = rand(1000, 9999);
                $data['sku'] = 'DEMO-SKU-' . $randomNumber;
                $data['spu'] = 'DEMO-SPU-' . $randomNumber;
                
                try {
                    $io->text("正在创建商品: {$data['title']}");

                    // 上传主图到七牛云（支持多张主图）
                    $io->text('上传主图...');
                    $mainImageKeys = [];
                    foreach ($data['mainImages'] as $mainImage) {
                        $mainImagePath = __DIR__ . '/../../' . $mainImage;
                        $mainImageKey = $this->uploadImage($mainImagePath, 'product_main');
                        
                        if (!$mainImageKey) {
                            $io->error("主图上传失败: {$mainImage}");
                            continue;
                        }
                        $mainImageKeys[] = $mainImageKey;
                        $io->text("主图上传成功: {$mainImageKey}");
                    }
                    
                    if (empty($mainImageKeys)) {
                        $io->error("没有主图上传成功: {$data['title']}");
                        continue;
                    }

                    // 创建商品主表
                    $product = new Product();
                    $product->setSupplier($supplier);  // 使用setSupplier方法
                    $product->setSku($data['sku'] . '-' . $timestamp . '-' . $supplier->getId());  // SKU加上时间戳和供应商ID避免重复
                    $product->setSpu($data['spu']);
                    $product->setTitle($data['title']);
                    $product->setTitleEn($data['titleEn']);
                    $product->setMainImages($mainImageKeys); // 设置主图数组
                    $product->setBrand($data['brand']);
                    // category和subcategory需要MenuCategory和MenuSubcategory对象，暂时不设置
                    $product->setStatus('approved');
                    $product->setIsNew(true);
                    $product->setIsHot(rand(0, 1) == 1);
                    $product->setIsPromotion(rand(0, 1) == 1);
                    $product->setSupportDropship(true);
                    $product->setSupportWholesale(true);
                    $product->setViewCount(rand(100, 1000));
                    $product->setSalesCount(rand(10, 500));
                    $product->setCreatedAt(new \DateTime());
                    $product->setUpdatedAt(new \DateTime());

                    $this->entityManager->persist($product);
                    $this->entityManager->flush();

                    // 上传详情图
                    // 已移除ProductImage实体，不再处理详情图

                    // 创建库存记录
                    $stock = new ProductStock();
                    $stock->setProduct($product);
                    $stock->setRegion('CN');
                    $stock->setWarehouseCode('WH001');
                    $stock->setWarehouseName('默认仓库');
                    $stock->setAvailableStock($data['stock']);
                    $this->entityManager->persist($stock);

                    // 创建价格记录
                    $price = new ProductPrice();
                    $price->setProduct($product);
                    $price->setRegion('CN');
                    $price->setCurrency('CNY');
                    $price->setBusinessType('general');
                    $price->setSellingPrice($data['basePrice']);
                    $price->setOriginalPrice($data['basePrice']);
                    $price->setIsActive(true);
                    $this->entityManager->persist($price);

                    // 创建物流记录
                    $shipping = new ProductShipping();
                    $shipping->setProduct($product);
                    $shipping->setRegion('CN');
                    $shipping->setShippingMethod('standard');
                    $shipping->setShippingPrice('0.0000');
                    $shipping->setCurrency('CNY');
                    $shipping->setDeliveryTime('3-5天');
                    $shipping->setIsDefault(true);
                    $shipping->setIsActive(true);
                    $this->entityManager->persist($shipping);

                    $this->entityManager->flush();

                    $created++;
                    $io->success("商品创建成功: {$data['title']} (ID: {$product->getId()})");

                } catch (\Exception $e) {
                    $io->error("创建商品失败: {$data['title']} - " . $e->getMessage());
                }
            }
            
            $supplierIndex++;
        }

        $io->success("成功为 " . count($suppliers) . " 个供应商创建了 {$created} 个商品！");
        return Command::SUCCESS;
    }

    private function uploadImage(string $filePath, string $prefix): ?string
    {
        if (!file_exists($filePath)) {
            return null;
        }

        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $fileName = $prefix . '_' . time() . '_' . uniqid() . '.' . $extension;

        $result = $this->qiniuService->uploadFile($filePath, $fileName);

        if ($result['success']) {
            return $result['key'];
        }

        return null;
    }
}