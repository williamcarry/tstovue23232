# Shop数据文件说明

本目录存放商城前端所需的JSON数据文件,用于模拟API返回数据。

## 文件列表

### categories.json
商品分类菜单数据，包含13个一级分类及其子分类。

**数据结构:**
```json
[
  {
    "key": "分类唯一标识",
    "title": "分类名称",
    "icon": "图标名称(Lucide图标)",
    "href": "链接地址",
    "children": [
      {
        "title": "子分类标题",
        "items": [
          {
            "title": "商品名称",
            "href": "链接地址"
          }
        ]
      }
    ]
  }
]
```

## 使用方式

### 1. 在控制器中使用

```php
// ShopController.php中已实现
private function getCategoriesData(): array
{
    $jsonFile = __DIR__ . '/data/categories.json';
    
    if (!file_exists($jsonFile)) {
        return [];
    }
    
    $jsonContent = file_get_contents($jsonFile);
    $data = json_decode($jsonContent, true);
    
    return $data ?? [];
}
```

### 2. API接口调用

**接口地址:** `GET /api/categories`

**返回示例:**
```json
[
  {
    "key": "home-furniture",
    "title": "家居和家具",
    "icon": "Home",
    "href": "#",
    "children": [...]
  }
]
```

### 3. 在Twig模板中使用

```twig
{# 在控制器中传递 categoriesJson 变量 #}
<script>
    window.__CATEGORIES__ = {{ categoriesJson|raw }};
</script>
```

### 4. 在Vue组件中使用

#### 方法A: 从window对象获取
```javascript
<script setup>
import { ref, onMounted } from 'vue'

const categories = ref([])

onMounted(() => {
  // 从Twig注入的全局变量获取
  categories.value = window.__CATEGORIES__ || []
})
</script>
```

#### 方法B: 通过API调用
```javascript
<script setup>
import { ref, onMounted } from 'vue'

const categories = ref([])

onMounted(async () => {
  try {
    const response = await fetch('/api/categories')
    categories.value = await response.json()
  } catch (error) {
    console.error('获取分类数据失败:', error)
  }
})
</script>
```

## 分类列表

当前包含以下13个主分类:

1. **家居和家具** (home-furniture) - Home图标
2. **庭院和园艺** (garden) - Sprout图标
3. **音乐艺术** (music-art) - Music图标
4. **户外、娱乐和运动** (outdoor) - Mountain图标
5. **电器类** (appliances) - Plug图标
6. **工商业设备** (industrial) - Cog图标
7. **健康和美容** (health) - HeartPulse图标
8. **办公、教育与安全** (office) - Briefcase图标
9. **宠物用品** (pets) - PawPrint图标
10. **玩具/婴童用品** (toys-baby) - Puzzle图标
11. **汽摩配件** (auto) - Car图标
12. **服饰箱包** (bags) - ShoppingBag图标
13. **消费电子/器材** (consumer-electronics) - Headphones图标

## 维护说明

### 添加新分类
1. 打开 `categories.json`
2. 在数组末尾添加新分类对象
3. 确保JSON格式正确
4. 保存文件

### 修改分类
直接编辑JSON文件中对应的分类数据即可。

### 验证JSON格式
```bash
# 使用PHP验证JSON格式
php -r "json_decode(file_get_contents('categories.json')); echo json_last_error() === JSON_ERROR_NONE ? 'Valid' : 'Invalid';"
```

## 注意事项

1. **编码**: 文件必须使用UTF-8编码
2. **格式**: 严格遵循JSON格式,注意逗号和引号
3. **图标**: icon字段使用Lucide Vue图标名称
4. **链接**: href字段暂时使用"#",后续可替换为实际路由
5. **性能**: 数据量较大时建议使用缓存机制

## 扩展建议

### 1. 添加缓存
```php
use Symfony\Contracts\Cache\CacheInterface;

private function getCategoriesData(CacheInterface $cache): array
{
    return $cache->get('shop_categories', function() {
        $jsonFile = __DIR__ . '/data/categories.json';
        $jsonContent = file_get_contents($jsonFile);
        return json_decode($jsonContent, true) ?? [];
    });
}
```

### 2. 数据库存储
未来可考虑将分类数据迁移到数据库,实现动态管理。

### 3. 多语言支持
可为每个分类添加多语言字段,支持国际化。
