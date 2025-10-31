<?php

namespace App\Command;

use App\Entity\MenuCategory;
use App\Entity\MenuSubcategory;
use App\Entity\MenuItem;
use App\Entity\PromotionMenu;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ImportCategoriesCommand extends Command
{
    protected static $defaultName = 'app:import-categories';
    
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure(): void
    {
        $this
            ->setName('app:import-categories')
            ->setDescription('Import categories from JSON file to database');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // 读取JSON文件
        $jsonFile = __DIR__ . '/../Controller/Shop/data/categories.json';
        if (!file_exists($jsonFile)) {
            $io->error('JSON file not found: ' . $jsonFile);
            return Command::FAILURE;
        }

        $jsonData = file_get_contents($jsonFile);
        $categories = json_decode($jsonData, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $io->error('Error parsing JSON file: ' . json_last_error_msg());
            return Command::FAILURE;
        }

        $io->info('Starting import of ' . count($categories) . ' categories...');

        // 开始事务
        $this->entityManager->beginTransaction();
        try {
            // 清空现有数据
            $this->entityManager->createQuery('DELETE FROM App\Entity\MenuItem')->execute();
            $this->entityManager->createQuery('DELETE FROM App\Entity\MenuSubcategory')->execute();
            $this->entityManager->createQuery('DELETE FROM App\Entity\PromotionMenu')->execute();
            $this->entityManager->createQuery('DELETE FROM App\Entity\MenuCategory')->execute();

            $sortOrder = 0;
            foreach ($categories as $categoryData) {
                $category = new MenuCategory();
                $category->setTitle($categoryData['title']);
                $category->setTitleEn($this->translateToEnglish($categoryData['title'])); // 简单翻译
                $category->setIcon('Home');
                $category->setSortOrder($sortOrder++);
                $category->setIsActive(true);
                $category->setCreatedAt(new \DateTime());
                $category->setUpdatedAt(new \DateTime());

                $this->entityManager->persist($category);
                $this->entityManager->flush(); // 需要立即flush以获取ID

                // 处理促销信息
                if (isset($categoryData['promotion'])) {
                    $promotion = new PromotionMenu();
                    $promotion->setCategory($category);
                    $promotion->setCategoryId($category->getId());
                    $promotion->setTitle($categoryData['promotion']['title']);
                    $promotion->setTitleEn($this->translateToEnglish($categoryData['promotion']['title'])); // 简单翻译
                    $promotion->setImageUrl($categoryData['promotion']['imageUrl'] ?? null);
                    $promotion->setIsActive(true);
                    $promotion->setCreatedAt(new \DateTime());
                    $promotion->setUpdatedAt(new \DateTime());

                    $this->entityManager->persist($promotion);
                }

                // 处理子分类
                if (isset($categoryData['children'])) {
                    $subSortOrder = 0;
                    foreach ($categoryData['children'] as $subCategoryData) {
                        $subCategory = new MenuSubcategory();
                        $subCategory->setCategory($category);
                        $subCategory->setCategoryId($category->getId());
                        $subCategory->setTitle($subCategoryData['title']);
                        $subCategory->setTitleEn($this->translateToEnglish($subCategoryData['title'])); // 简单翻译
                        $subCategory->setSortOrder($subSortOrder++);
                        $subCategory->setIsActive(true);
                        $subCategory->setCreatedAt(new \DateTime());
                        $subCategory->setUpdatedAt(new \DateTime());

                        $this->entityManager->persist($subCategory);
                        $this->entityManager->flush(); // 需要立即flush以获取ID

                        // 处理分类项
                        if (isset($subCategoryData['items'])) {
                            $itemSortOrder = 0;
                            foreach ($subCategoryData['items'] as $itemData) {
                                $item = new MenuItem();
                                $item->setSubcategory($subCategory);
                                $item->setSubCategoryId($subCategory->getId());
                                $item->setTitle($itemData['title']);
                                $item->setTitleEn($this->translateToEnglish($itemData['title'])); // 简单翻译
                                $item->setSortOrder($itemSortOrder++);
                                $item->setIsActive(true);
                                $item->setCreatedAt(new \DateTime());
                                $item->setUpdatedAt(new \DateTime());

                                $this->entityManager->persist($item);
                            }
                        }
                    }
                }
            }

            $this->entityManager->flush();
            $this->entityManager->commit();

            $io->success('Categories imported successfully!');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            $io->error('Error importing categories: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * 简单的中英文翻译函数（实际项目中应该使用翻译服务）
     */
    private function translateToEnglish(string $chineseText): string
    {
        $translations = [
            '家居和家具' => 'Home & Furniture',
            '庭院和园艺' => 'Garden & Horticulture',
            '音乐艺术' => 'Music & Arts',
            '户外、娱乐和运动' => 'Outdoor, Entertainment & Sports',
            '电器类' => 'Electronics',
            '工商业设备' => 'Industrial Equipment',
            '健康和美容' => 'Health & Beauty',
            '办公、教育与安全' => 'Office, Education & Safety',
            '宠物用品' => 'Pet Supplies',
            '玩具/婴童用品' => 'Toys/Baby Products',
            '汽摩配件' => 'Automotive Parts',
            '服饰箱包' => 'Fashion & Bags',
            '消费电子/器材' => 'Consumer Electronics',
            '家具' => 'Furniture',
            '家居装饰' => 'Home Decor',
            '厨房用品' => 'Kitchenware',
            '卫浴用品' => 'Bathroom Supplies',
            '床上用品' => 'Bedding',
            '园艺工具' => 'Gardening Tools',
            '户外家居' => 'Outdoor Furniture',
            '园艺饰品' => 'Garden Ornaments',
            '户外照明' => 'Outdoor Lighting',
            '植物养护' => 'Plant Care',
            '弦乐' => 'String Instruments',
            '键盘乐' => 'Keyboard Instruments',
            '打击乐' => 'Percussion Instruments',
            '绘画用品' => 'Painting Supplies',
            '美术材料' => 'Art Materials',
            '露营用品' => 'Camping Gear',
            '运动装备' => 'Sports Equipment',
            '球类运动' => 'Ball Sports',
            '自行车配件' => 'Bicycle Accessories',
            '登山装备' => 'Mountaineering Gear',
            '厨房小电' => 'Small Kitchen Appliances',
            '大厨电' => 'Large Kitchen Appliances',
            '清洁电器' => 'Cleaning Appliances',
            '个护电器' => 'Personal Care Appliances',
            '取暖电器' => 'Heating Appliances',
            '生产设备' => 'Production Equipment',
            '包装设备' => 'Packaging Equipment',
            '输送设备' => 'Conveyor Equipment',
            '工业工具' => 'Industrial Tools',
            '安全防护' => 'Safety Protection',
            '面部护肤' => 'Facial Skincare',
            '身体护理' => 'Body Care',
            '美容工具' => 'Beauty Tools',
            '彩妆' => 'Cosmetics',
            '健康产品' => 'Health Products',
            '文具用品' => 'Stationery',
            '办公家具' => 'Office Furniture',
            '教育用品' => 'Educational Supplies',
            '办公电器' => 'Office Electronics',
            '宠物粮食' => 'Pet Food',
            '宠物玩具' => 'Pet Toys',
            '宠物护理' => 'Pet Care',
            '宠物家具' => 'Pet Furniture',
            '宠物用具' => 'Pet Accessories',
            '积木玩具' => 'Building Block Toys',
            '车模型' => 'Vehicle Models',
            '娃娃玩具' => 'Doll Toys',
            '婴儿用品' => 'Baby Products',
            '童装及配饰' => 'Children Clothing & Accessories',
            '发动机配件' => 'Engine Parts',
            '车灯配件' => 'Lighting Parts',
            '内饰用品' => 'Interior Accessories',
            '轮胎轮圈' => 'Tires & Wheels',
            '外装配件' => 'Exterior Accessories',
            '女性箱包' => 'Women Bags',
            '男性箱包' => 'Men Bags',
            '旅行箱' => 'Luggage',
            '运动背包' => 'Sports Backpacks',
            '配饰用品' => 'Accessories',
            '音频设备' => 'Audio Equipment',
            '移动设备' => 'Mobile Devices',
            '计算设备' => 'Computing Devices',
            '影音设备' => 'Audiovisual Equipment',
            '配件线缆' => 'Accessories & Cables',
            '品质生活' => 'Quality Life',
            '花园生活' => 'Garden Life',
            '艺术殿堂' => 'Art Palace',
            '运动健身' => 'Sports & Fitness',
            '智能家电' => 'Smart Home Appliances',
            '工业设备' => 'Industrial Equipment',
            '美丽人生' => 'Beautiful Life',
            '办公专区' => 'Office Zone',
            '宠物天地' => 'Pet World',
            '童趣世界' => 'Children Fun World',
            '汽配专区' => 'Auto Parts Zone',
            '时尚潮流' => 'Fashion Trends',
            '数码科技' => 'Digital Technology',
            '沙发' => 'Sofa',
            '床' => 'Bed',
            '桌椅' => 'Table & Chairs',
            '餐桌' => 'Dining Table',
            '书架' => 'Bookshelf',
            '衣柜' => 'Wardrobe',
            '电视柜' => 'TV Stand',
            '鞋柜' => 'Shoe Cabinet',
            '收纳柜' => 'Storage Cabinet',
            '茶几' => 'Coffee Table',
            '床头柜' => 'Nightstand',
            '办公桌' => 'Desk',
            '餐边柜' => 'Sideboard',
            '梳妆台' => 'Dressing Table',
            '鞋凳' => 'Shoe Bench',
            '玄关柜' => 'Entryway Cabinet',
            '转角沙发' => 'Corner Sofa',
            '躺椅' => 'Recliner',
            '休闲椅' => 'Lounge Chair',
            '摇椅' => 'Rocking Chair',
            '儿童床' => 'Kids Bed',
            '儿童桌椅' => 'Kids Table & Chairs',
            '书桌' => 'Study Desk',
            '工作台' => 'Workbench',
            '餐椅' => 'Dining Chair',
            '吧椅' => 'Bar Stool',
            '沙发床' => 'Sofa Bed',
            '角柜' => 'Corner Cabinet',
            '衣帽架' => 'Coat Rack',
            '置物架' => 'Storage Shelf',
            '收纳' => 'Storage',
            '灯具' => 'Lighting',
            '地毯' => 'Carpet',
            '窗帘' => 'Curtains',
            '壁画' => 'Mural',
            '镜子' => 'Mirror',
            '屏风' => 'Screen',
            '挂画' => 'Hanging Picture',
            '花瓶' => 'Vase',
            '工艺品' => 'Crafts',
            '摆件' => 'Ornaments',
            '锅具' => 'Cookware',
            '砧板' => 'Cutting Board',
            '刀具' => 'Knives',
            '餐具' => 'Tableware',
            '筷子' => 'Chopsticks',
            '勺子' => 'Spoons',
            '切菜板' => 'Chopping Board',
            '砂锅' => 'Casserole',
            '压力锅' => 'Pressure Cooker',
            '烤盘' => 'Baking Pan',
            '毛巾' => 'Towel',
            '浴垫' => 'Bath Mat',
            '肥皂盒' => 'Soap Dish',
            '牙刷架' => 'Toothbrush Holder',
            '浴帘' => 'Shower Curtain',
            '浴巾' => 'Bath Towel',
            '浴刷' => 'Bath Brush',
            '擦背巾' => 'Back Scrubber',
            '沐浴液架' => 'Shampoo Holder',
            '床单' => 'Bed Sheet',
            '被套' => 'Duvet Cover',
            '枕头' => 'Pillow',
            '床笠' => 'Mattress Cover',
            '床垫' => 'Mattress',
            '床罩' => 'Bedspread',
            '抱枕' => 'Pillowcase',
            '毛毯' => 'Blanket',
            '床头靠垫' => 'Bed Head Cushion',
            '床上四件套' => 'Bedding Set',
            '花盆' => 'Flower Pot',
            '种子' => 'Seeds',
            '园艺工具' => 'Gardening Tools',
            '户外灯' => 'Outdoor Light',
            '烧烤用品' => 'BBQ Supplies',
            '户外装饰' => 'Outdoor Decor',
            '遮阳篷' => 'Sun Awning',
            '户外沙发' => 'Outdoor Sofa',
            '风铃' => 'Wind Chime',
            '花架' => 'Flower Stand',
            '秋千' => 'Swing',
            '喷泉' => 'Fountain',
            '太阳能灯' => 'Solar Light',
            '路灯' => 'Street Light',
            '壁灯' => 'Wall Light',
            '草坪灯' => 'Lawn Light',
            '肥料' => 'Fertilizer',
            '农药' => 'Pesticide',
            '喷雾器' => 'Sprayer',
            '剪刀' => 'Scissors',
            '吉他' => 'Guitar',
            '贝司' => 'Bass',
            '小提琴' => 'Violin',
            '琴弦' => 'Strings',
            '电钢琴' => 'Electric Piano',
            '合成器' => 'Synthesizer',
            '手风琴' => 'Accordion',
            '琴架' => 'Music Stand',
            '鼓' => 'Drum',
            '架子鼓' => 'Drum Kit',
            '锣' => 'Gong',
            '铃鼓' => 'Tambourine',
            '油画' => 'Oil Painting',
            '水彩' => 'Watercolor',
            '素描笔' => 'Sketch Pen',
            '画笔' => 'Paintbrush',
            '画布' => 'Canvas',
            '画板' => 'Drawing Board',
            '颜料' => 'Paint',
            '笔筒' => 'Pen Holder',
            '帐篷' => 'Tent',
            '睡袋' => 'Sleeping Bag',
            '露营灯' => 'Camp Light',
            '背包' => 'Backpack',
            '运动服' => 'Sportswear',
            '运动鞋' => 'Sports Shoes',
            '护具' => 'Protective Gear',
            '运动包' => 'Sports Bag',
            '篮球' => 'Basketball',
            '足球' => 'Football',
            '网球' => 'Tennis',
            '排球' => 'Volleyball',
            '山地车' => 'Mountain Bike',
            '公路车' => 'Road Bike',
            '车灯' => 'Bike Light',
            '头盔' => 'Helmet',
            '登山鞋' => 'Mountaineering Shoes',
            '登山包' => 'Mountaineering Backpack',
            '登山杖' => 'Trekking Pole',
            '安全绳' => 'Safety Rope',
            '电饭煲' => 'Rice Cooker',
            '豆浆机' => 'Soy Milk Maker',
            '榨汁机' => 'Juicer',
            '烤箱' => 'Oven',
            '冰箱' => 'Refrigerator',
            '洗衣机' => 'Washing Machine',
            '微波炉' => 'Microwave',
            '电磁炉' => 'Induction Cooker',
            '吸尘器' => 'Vacuum Cleaner',
            '扫地机' => 'Robotic Vacuum',
            '拖地机' => 'Floor Washer',
            '洗车机' => 'Car Washer',
            '吹风机' => 'Hair Dryer',
            '卷发棒' => 'Curling Iron',
            '剃须刀' => 'Shaver',
            '电动牙刷' => 'Electric Toothbrush',
            '空调' => 'Air Conditioner',
            '取暖器' => 'Heater',
            '风扇' => 'Fan',
            '加湿器' => 'Humidifier',
            '流水线' => 'Production Line',
            '焊接机' => 'Welding Machine',
            '冲压机' => 'Punching Machine',
            '铸造设备' => 'Casting Equipment',
            '打包机' => 'Packaging Machine',
            '充填机' => 'Filling Machine',
            '封口机' => 'Sealing Machine',
            '贴标机' => 'Labeling Machine',
            '传送带' => 'Conveyor Belt',
            '升降机' => 'Lift',
            '提升机' => 'Hoist',
            '托盘' => 'Pallet',
            '扳手' => 'Wrench',
            '螺丝刀' => 'Screwdriver',
            '电钻' => 'Electric Drill',
            '电锯' => 'Electric Saw',
            '防护服' => 'Protective Suit',
            '安全帽' => 'Safety Helmet',
            '手套' => 'Gloves',
            '护目镜' => 'Goggles',
            '洗面奶' => 'Cleanser',
            '爽肤水' => 'Toner',
            '面霜' => 'Face Cream',
            '面膜' => 'Face Mask',
            '沐浴露' => 'Body Wash',
            '身体乳' => 'Body Lotion',
            '防晒霜' => 'Sunscreen',
            '磨砂膏' => 'Scrub',
            '美容仪' => 'Beauty Device',
            '洁面仪' => 'Cleansing Device',
            '卷发棒' => 'Curling Iron',
            '直板夹' => 'Straightener',
            '口红' => 'Lipstick',
            '眼影' => 'Eyeshadow',
            '粉底' => 'Foundation',
            '眉笔' => 'Eyebrow Pencil',
            '足浴盆' => 'Foot Bath',
            '按摩垫' => 'Massage Pad',
            '颈椎仪' => 'Neck Massager',
            '腰枕' => 'Lumbar Pillow',
            '笔' => 'Pen',
            '笔记本' => 'Notebook',
            '便签' => 'Sticky Notes',
            '纸张' => 'Paper',
            '办公椅' => 'Office Chair',
            '办公桌' => 'Office Desk',
            '文件柜' => 'Filing Cabinet',
            '书架' => 'Bookshelf',
            '教科书' => 'Textbook',
            '练习本' => 'Exercise Book',
            '黑板' => 'Blackboard',
            '粉笔' => 'Chalk',
            '防护口罩' => 'Protective Mask',
            '手套' => 'Gloves',
            '护目镜' => 'Goggles',
            '防护服' => 'Protective Suit',
            '打印机' => 'Printer',
            '复印机' => 'Copier',
            '碎纸机' => 'Shredder',
            '订书机' => 'Stapler',
            '狗粮' => 'Dog Food',
            '猫粮' => 'Cat Food',
            '鸟粮' => 'Bird Food',
            '水族粮' => 'Aquarium Food',
            '球类' => 'Balls',
            '咬胶' => 'Chew Toy',
            '逗猫棒' => 'Cat Teaser',
            '毛球' => 'Fur Ball',
            '沐浴露' => 'Shampoo',
            '梳子' => 'Comb',
            '指甲剪' => 'Nail Clipper',
            '牙刷' => 'Toothbrush',
            '宠物床' => 'Pet Bed',
            '猫咪树' => 'Cat Tree',
            '狗窝' => 'Dog House',
            '笼子' => 'Cage',
            '牵引绳' => 'Leash',
            '颈圈' => 'Collar',
            '水碗' => 'Water Bowl',
            '食盆' => 'Food Bowl',
            '塑料积木' => 'Plastic Blocks',
            '木制积木' => 'Wooden Blocks',
            '磁力积木' => 'Magnetic Blocks',
            '益智拼图' => 'Puzzle',
            '汽车模型' => 'Car Model',
            '飞机模型' => 'Plane Model',
            '火车模型' => 'Train Model',
            '船舶模型' => 'Ship Model',
            '布娃娃' => 'Cloth Doll',
            '塑料娃娃' => 'Plastic Doll',
            '毛绒玩具' => 'Plush Toy',
            '手偶' => 'Hand Puppet',
            '奶瓶' => 'Baby Bottle',
            '尿布' => 'Diaper',
            '婴儿床' => 'Crib',
            '推车' => 'Stroller',
            '连体衣' => 'Bodysuit',
            '婴儿裤' => 'Baby Pants',
            '婴儿帽' => 'Baby Hat',
            '婴儿袜' => 'Baby Socks',
            '火花塞' => 'Spark Plug',
            '机油滤' => 'Oil Filter',
            '空气滤' => 'Air Filter',
            '活塞环' => 'Piston Ring',
            '前大灯' => 'Headlight',
            '后尾灯' => 'Taillight',
            '雾灯' => 'Fog Light',
            '灯泡' => 'Bulb',
            '脚垫' => 'Floor Mat',
            '座套' => 'Seat Cover',
            '方向盘套' => 'Steering Wheel Cover',
            '车垫' => 'Car Mat',
            '轮胎' => 'Tire',
            '轮圈' => 'Wheel Rim',
            '轮毂盖' => 'Hubcap',
            '轮胎塞' => 'Tire Plug',
            '侧镜' => 'Side Mirror',
            '挡泥板' => 'Fender',
            '扰流板' => 'Spoiler',
            '防护杠' => 'Bumper',
            '女包' => 'Women Bag',
            '斜挎包' => 'Crossbody Bag',
            '化妆包' => 'Cosmetic Bag',
            '手拿包' => 'Clutch',
            '男包' => 'Men Bag',
            '公文包' => 'Briefcase',
            '钱包' => 'Wallet',
            '腰包' => 'Waist Bag',
            '行李箱' => 'Luggage',
            '拉杆箱' => 'Suitcase',
            '旅行包' => 'Travel Bag',
            '登机箱' => 'Carry-on',
            '登山包' => 'Mountaineering Backpack',
            '双肩包' => 'Backpack',
            '户外包' => 'Outdoor Bag',
            '学生包' => 'School Bag',
            '皮带' => 'Belt',
            '手套' => 'Gloves',
            '围巾' => 'Scarf',
            '帽子' => 'Hat',
            '耳机' => 'Headphones',
            '蓝牙音箱' => 'Bluetooth Speaker',
            '麦克风' => 'Microphone',
            '耳机架' => 'Headphone Stand',
            '手机' => 'Mobile Phone',
            '平板' => 'Tablet',
            '电子阅读' => 'E-reader',
            '移动电源' => 'Power Bank',
            '笔记本' => 'Laptop',
            '台式电脑' => 'Desktop Computer',
            '平板电脑' => 'Tablet PC',
            '键盘鼠标' => 'Keyboard & Mouse',
            '摄像机' => 'Camcorder',
            '数码相机' => 'Digital Camera',
            '投影仪' => 'Projector',
            '电视' => 'TV',
            'USB线' => 'USB Cable',
            '充电器' => 'Charger',
            '数据线' => 'Data Cable',
            '保护套' => 'Protective Case'
        ];

        return $translations[$chineseText] ?? $chineseText;
    }
}