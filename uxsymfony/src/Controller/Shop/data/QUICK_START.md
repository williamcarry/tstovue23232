# 快速开始使用分类菜单API

## 🚀 5分钟快速启动

### 步骤1: 验证API可用性

打开浏览器访问:
```
http://localhost:8000/api/categories
```

**预期结果:** 看到JSON格式的分类数据

---

### 步骤2: 测试API功能

访问测试页面:
```
http://localhost:8000/test-categories-api.html
```

点击各个测试按钮验证功能。

---

### 步骤3: 在Vue组件中使用

#### 方法A: 使用全局变量(推荐)

```vue
<script setup>
import { ref, onMounted } from 'vue'

const categories = ref([])

onMounted(() => {
  // 从Twig注入的全局变量获取
  categories.value = window.__CATEGORIES__ || []
})
</script>

<template>
  <div v-for="cat in categories" :key="cat.key">
    {{ cat.title }}
  </div>
</template>
```

#### 方法B: 使用API调用

```vue
<script setup>
import { ref, onMounted } from 'vue'

const categories = ref([])

onMounted(async () => {
  const response = await fetch('/api/categories')
  categories.value = await response.json()
})
</script>
```

---

### 步骤4: 修改 CategorySidebar.vue

将 `assets/vue/shop/components/CategorySidebar.vue` 中的硬编码数据改为:

```vue
<script setup>
import { ref, onMounted, markRaw } from 'vue'
import {
  Home, Sprout, Music, Mountain, Plug, Cog,
  HeartPulse, Briefcase, PawPrint, Puzzle,
  Car, ShoppingBag, Headphones, ChevronRight,
} from 'lucide-vue-next'

// 图标映射表
const iconMap = {
  'Home': markRaw(Home),
  'Sprout': markRaw(Sprout),
  'Music': markRaw(Music),
  'Mountain': markRaw(Mountain),
  'Plug': markRaw(Plug),
  'Cog': markRaw(Cog),
  'HeartPulse': markRaw(HeartPulse),
  'Briefcase': markRaw(Briefcase),
  'PawPrint': markRaw(PawPrint),
  'Puzzle': markRaw(Puzzle),
  'Car': markRaw(Car),
  'ShoppingBag': markRaw(ShoppingBag),
  'Headphones': markRaw(Headphones),
}

const categories = ref([])

onMounted(() => {
  // 从全局变量获取数据
  const rawData = window.__CATEGORIES__ || []
  
  // 将图标字符串转换为实际的图标组件
  categories.value = rawData.map(cat => ({
    ...cat,
    icon: iconMap[cat.icon] || markRaw(Home)
  }))
})
</script>

<template>
  <!-- 保持原有的模板结构不变 -->
  <div class="hidden md:block category-sidebar bg-white border-r border-b shadow-sm" style="height: 460px">
    <ul class="h-full flex flex-col divide-y divide-gray-200">
      <li v-for="cat in categories" :key="cat.key" class="relative group flex flex-col" style="height: 35px">
        <!-- 其余代码保持不变 -->
      </li>
    </ul>
  </div>
</template>
```

---

## ✅ 验证清单

完成以下检查确保一切正常:

- [ ] ✅ 访问 `/api/categories` 能看到JSON数据
- [ ] ✅ 测试页面所有测试通过
- [ ] ✅ 首页菜单正常显示
- [ ] ✅ 所有图标正确显示
- [ ] ✅ 悬停菜单面板正常弹出
- [ ] ✅ 浏览器控制台无错误

---

## 📚 更多文档

- **完整实现文档**: `/CATEGORIES_API_IMPLEMENTATION.md`
- **数据文件说明**: `src/Controller/Shop/data/README.md`
- **Vue组件指南**: `assets/vue/shop/components/CategorySidebar-API-Usage.md`

---

## 🆘 遇到问题?

### API返回404
```bash
# 清除Symfony缓存
php bin/console cache:clear
```

### 数据为空
```bash
# 验证JSON格式
php -r "json_decode(file_get_contents('src/Controller/Shop/data/categories.json')); echo json_last_error() === JSON_ERROR_NONE ? '正确' : '错误';"
```

### 图标不显示
检查 `iconMap` 是否包含所有图标名称。

---

## 🎉 完成!

现在你的分类菜单已经通过API加载,而不是硬编码在组件中!

**优点:**
- ✅ 数据与代码分离
- ✅ 易于维护和更新
- ✅ 支持缓存优化
- ✅ 为数据库迁移做好准备
