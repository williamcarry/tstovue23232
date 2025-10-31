# Symfony UX Vue 集成文档

本文档详细说明如何将Vue应用源码集成到Symfony项目中，使用Symfony的路由系统而不是Vue Router。

---

## 📋 目录

1. [项目结构分析](#项目结构分析)
2. [前置准备](#前置准备)
3. [集成步骤](#集成步骤)
4. [样式系统集成](#样式系统集���)
5. [路由映射策略](#路由映射策略)
6. [组件注册](#组件注册)
7. [数据传递](#数据传递)
8. [构建配置](#构建配置)
9. [常见问题](#常见问题)

---

## 项目结构分析

### 源项目（Fusion Vue）的结构
```
src/
├── assets/
│   ├── base.css          # 全局基础样式
│   └── main.css          # 主样式入口（导入base.css）
├── components/           # Vue组件（31个）
│   ├── SiteHeader.vue    # 顶部导航
│   ├── CategoryNavBar.vue # 分类导航
│   ├── SiteFooter.vue    # 页脚
│   ├── RightFloatNav.vue # 右侧浮动导航
│   └── ...其他布局和功能组件
├── pages/                # 页面组件（60+个）
│   ├── AllProductsPage.vue
│   ├── ItemDetailPage.vue
│   ├── HelpCenterPage.vue
│   └── ...
├── data/                 # 数据文件
│   ├── faqData.js
│   ├── helpCenterNav.js
│   └── ...
├── router/
│   └── index.js          # Vue Router配置（将被移除）
├── i18n.js               # 国际化配置
├── main.js               # 应用入口（将被修改）
└── App.vue               # 根组件（将被修改）
```

### 依赖项分析
- **Vue**: 3.5.17
- **TailwindCSS**: 3.4.11（必须保留）
- **Element Plus**: 2.11.4（中文UI组件库）
- **Lucide Vue**: 0.545.0（图标库）

---

## 前置准备

### 1. Symfony项目要求
```bash
# 确保已安装UX Vue bundle
composer require symfony/ux-vue

# 如果使用AssetMapper（推荐）
composer require symfony/asset-mapper
```

### 2. 文件夹结构规划
```
symfony-project/
├── assets/
│   ├── vue/              # 新建：Vue源码目录
│   │   ├── components/   # 复制所有Vue组件
│   │   ├── pages/        # 复制所有页面组件
│   │   ├── data/         # 复制数据文件
│   │   ├── styles/       # 复制样式文件
│   │   └── i18n.js       # 复制国际化配置
│   └── css/
│       └── app.css       # 主CSS入口
├── src/
│   ├── Controller/       # Symfony控制器（处理路由）
│   └── ...
├── templates/
│   ├── base.html.twig    # 基础模板
│   └── vue/              # Vue模板目录
└── tailwind.config.js    # TailwindCSS配置
```

---

## 集成步骤

### 步骤1：复制Vue源码

1. **复制components目录**
   ```bash
   cp -r ./src/components ./symfony-project/assets/vue/components
   ```

2. **复制pages目录**
   ```bash
   cp -r ./src/pages ./symfony-project/assets/vue/pages
   ```

3. **复制data目录**
   ```bash
   cp -r ./src/data ./symfony-project/assets/vue/data
   ```

4. **复制样式和国际化**
   ```bash
   cp ./src/assets/base.css ./symfony-project/assets/vue/styles/base.css
   cp ./src/i18n.js ./symfony-project/assets/vue/i18n.js
   ```

### 步骤2：配置TailwindCSS

1. **复制tailwind.config.js到项目根目录**
   ```bash
   cp ./tailwind.config.js ./symfony-project/tailwind.config.js
   ```

2. **更新content路径（适配Symfony结构）**
   ```javascript
   // symfony-project/tailwind.config.js
   export default {
     content: [
       './templates/**/*.html.twig',
       './assets/vue/**/*.vue',
       './assets/vue/**/*.js',
     ],
     theme: {
       container: { 
         center: true, 
         padding: '1rem', 
         screens: { xl: '1200px', '2xl': '1500px' } 
       },
       extend: {
         colors: {
           primary: '#CB261C',
           slate: { 950: '#0b1220' },
         },
         fontFamily: {
           sans: [
             'Inter', 
             'PingFang SC', 
             'Microsoft YaHei', 
             'Helvetica Neue', 
             'Arial', 
             'sans-serif'
           ],
         },
       },
     },
     plugins: [],
   }
   ```

3. **配置PostCSS（如果没有）**
   创建或更新 `postcss.config.js`：
   ```javascript
   export default {
     plugins: {
       tailwindcss: {},
       autoprefixer: {},
     },
   }
   ```

### 步骤3：更新App.vue（移除路由逻辑）

**原始App.vue的问题**：
- 包含内置的页面切换逻辑
- 依赖Vue Router
- 不适合Symfony模板系统

**修改方案**：
创建 `assets/vue/App.vue`（新的精简版本）：

```vue
<script setup>
import SiteHeader from './components/SiteHeader.vue'
import CategoryNavBar from './components/CategoryNavBar.vue'
import SiteFooter from './components/SiteFooter.vue'
import RightFloatNav from './components/RightFloatNav.vue'

// 接收来自Symfony的插槽内容
defineProps({
  pageContent: {
    type: Object,
    default: null
  }
})
</script>

<template>
  <div class="min-h-screen flex flex-col">
    <!-- 顶部导航 -->
    <SiteHeader />
    
    <!-- 分类导航 -->
    <CategoryNavBar />
    
    <!-- 主内容区 -->
    <main class="flex-grow">
      <slot>
        <!-- Symfony模板会在这里注入页面内容 -->
      </slot>
    </main>
    
    <!-- 页脚 -->
    <SiteFooter />
    
    <!-- 右侧浮动导航 -->
    <RightFloatNav />
  </div>
</template>

<style scoped>
/* 样式已通过TailwindCSS全局应用 */
</style>
```

### 步骤4：创建新的main.js（无路由版本）

创建 `assets/vue/main.js`：

```javascript
import './styles/base.css'

import { createApp } from 'vue'
import App from './App.vue'

import ElementPlus from 'element-plus'
import 'element-plus/dist/index.css'
import zhCn from 'element-plus/es/locale/lang/zh-cn'

// 注意：不再导入和使用Vue Router

const app = createApp(App)
app.use(ElementPlus, { locale: zhCn })
// app.mount('#app') — 不在这里挂载，由Symfony管理

export default app
```

### 步骤5：导入ElementPlus中文语言包

复制或配置ElementPlus的中文语言包：
```javascript
// 在main.js中已配置
import zhCn from 'element-plus/es/locale/lang/zh-cn'
app.use(ElementPlus, { locale: zhCn })
```

---

## 样式系统集成

### 核心原则
**目标**：保持样式100%一致，不丢失任何CSS

### 实现方案

#### 1. 创建统一的CSS入口

创建 `assets/css/app.css`：

```css
/* 导入Vue应用的基础样式 */
@import '../vue/styles/base.css';

/* TailwindCSS指令（必须） */
@tailwind base;
@tailwind components;
@tailwind utilities;

/* 任何Symfony特有的全局样式（可选） */
/* ... */
```

#### 2. Element Plus样式处理

Element Plus样式通过以下方式导入：
- **在Vue组件中**：`import 'element-plus/dist/index.css'`
- **或在main.js中**（推荐）：见上述步骤4

#### 3. Symfony模板CSS引入

在 `templates/base.html.twig`：

```twig
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{% block title %}Saleye{% endblock %}</title>
    
    {# 重要：引入编译后的CSS #}
    {% block stylesheets %}
        {{ import_asset('assets/css/app.css') }}
    {% endblock %}
</head>
<body>
    <div id="app"></div>
    
    {% block javascripts %}
        {{ import_asset('assets/vue/main.js') }}
    {% endblock %}
</body>
</html>
```

---

## 路由映射策略

### 核心概念
- **Vue Router被移除**，改用Symfony路由系统
- 每条路由对应一个Symfony Controller方法
- Vue组件作为"视图"被Symfony渲染

### 路由映射表

原始Vue Router路由 → Symfony Controller映射：

| Vue路由 | 组件 | Symfony路由 | Controller方法 |
|---------|------|----------|-----------------|
| `/` | AllProductsPage | `/` | ProductController::index() |
| `/all-products` | AllProductsPage | `/products` | ProductController::list() |
| `/help-center` | HelpCenterPage | `/help` | HelpController::index() |
| `/operation-guide` | OperationGuidePage | `/guide` | GuideController::index() |
| `/faq/:id` | FAQDetailPage | `/faq/{id}` | FaqController::detail() |
| `/item/:id` | ItemDetailPage | `/product/{id}` | ProductController::show() |

### Symfony Controller实现

创建 `src/Controller/ProductController.php`：

```php
<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProductController extends AbstractController
{
    #[Route('/', name: 'app_home', methods: ['GET'])]
    #[Route('/products', name: 'app_products', methods: ['GET'])]
    public function index(): Response
    {
        // 这里可以传递数据给Vue组件
        $products = []; // 从数据库获取
        
        return $this->render('vue/products/list.html.twig', [
            'products' => json_encode($products),
        ]);
    }
    
    #[Route('/product/{id}', name: 'app_product_show', methods: ['GET'])]
    public function show(int $id): Response
    {
        // 获取产品详情
        $product = []; // 从数据库获取
        
        return $this->render('vue/products/detail.html.twig', [
            'product' => json_encode($product),
            'productId' => $id,
        ]);
    }
}
```

创建 `src/Controller/HelpController.php`：

```php
<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HelpController extends AbstractController
{
    #[Route('/help', name: 'app_help_center', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('vue/help/center.html.twig');
    }
    
    #[Route('/faq/{id}', name: 'app_faq_detail', methods: ['GET'])]
    public function detail(int $id): Response
    {
        return $this->render('vue/help/faq-detail.html.twig', [
            'faqId' => $id,
        ]);
    }
}
```

---

## 组件注册

### 方案1：使用Symfony UX Vue的自动注册

在 `assets/vue/main.js` 中注册所有可复用的组件：

```javascript
import { createApp } from 'vue'
import App from './App.vue'
import ElementPlus from 'element-plus'
import 'element-plus/dist/index.css'
import zhCn from 'element-plus/es/locale/lang/zh-cn'

// 导入所有可复用的组件
import SiteHeader from './components/SiteHeader.vue'
import CategoryNavBar from './components/CategoryNavBar.vue'
import SiteFooter from './components/SiteFooter.vue'
import HeroBanner from './components/HeroBanner.vue'
import CategorySidebar from './components/CategorySidebar.vue'
import PlatformBoutique from './components/PlatformBoutique.vue'
// ... 导入其他组件

const app = createApp(App)

// 全局注册组件
app.component('SiteHeader', SiteHeader)
app.component('CategoryNavBar', CategoryNavBar)
app.component('SiteFooter', SiteFooter)
app.component('HeroBanner', HeroBanner)
app.component('CategorySidebar', CategorySidebar)
app.component('PlatformBoutique', PlatformBoutique)
// ... 注册其他组件

app.use(ElementPlus, { locale: zhCn })

export default app
```

### 方案2：按需导入（推荐大型应用）

在需要的地方动态导入：

```vue
<script setup>
import { defineAsyncComponent } from 'vue'

// 动态导入页面特定的组件
const ProductDetailTabs = defineAsyncComponent(() =>
  import('./components/ProductDetailTabs.vue')
)
</script>
```

---

## 数据传递

### 策略1：通过Twig模板变量传递初始数据

在 `templates/vue/products/detail.html.twig`：

```twig
{% extends 'base.html.twig' %}

{% block content %}
<div id="app" data-product="{{ product | json_encode }}">
    {# Vue应用挂载点 #}
</div>

<script>
    // 暴露数据给Vue应用
    window.__INITIAL_STATE__ = {
        product: {{ product | raw }},
        productId: {{ productId }}
    };
</script>
{% endblock %}
```

在Vue组件中访问：

```javascript
// 在组件中
const initialState = window.__INITIAL_STATE__
const product = ref(initialState?.product || {})
const productId = ref(initialState?.productId || null)
```

### 策略2：使用Props从Symfony传递数据

在 `assets/vue/main.js` 中修改App组件：

```javascript
const app = createApp(App, {
    // 从Symfony传递的初始Props
    initialData: window.__INITIAL_DATA__ || {}
})
```

### 策略3：API调用（推荐）

在Vue组件中直接调用Symfony API端点：

```javascript
// pages/ItemDetailPage.vue
import { onMounted, ref } from 'vue'

export default {
    props: {
        productId: {
            type: Number,
            required: true
        }
    },
    setup(props) {
        const product = ref(null)
        
        onMounted(async () => {
            const response = await fetch(`/api/product/${props.productId}`)
            product.value = await response.json()
        })
        
        return { product }
    }
}
```

创建对应的Symfony API Controller：

```php
#[Route('/api/product/{id}', name: 'api_product', methods: ['GET'])]
public function getProduct(int $id): JsonResponse
{
    // 获取产品数据
    $product = $this->productRepository->find($id);
    
    return $this->json($product);
}
```

---

## 构建配置

### 1. 更新Webpack/Vite配置（如使用AssetMapper）

在 `assets/controllers/hello_controller.js` 或 `webpack.config.js` 中：

```javascript
// 如果使用Symfony AssetMapper，无需特殊配置
// 如果使用Webpack Encore:

const Encore = require('@symfony/webpack-encore');

Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')
    
    // Vue应用入口
    .addEntry('app', './assets/vue/main.js')
    
    // CSS处理
    .addStyleEntry('styles', './assets/css/app.css')
    
    // 分离Node modules
    .splitEntryChunks()
    .enableSingleRuntimeChunk()
    
    // TailwindCSS + PostCSS
    .enablePostCssLoader()
    
    // Vue支持
    .enableVueLoader()
    
    // 其他配置...
    .cleanupOutputBeforeBuild()
    .enableVersioning()
;

module.exports = Encore.getWebpackConfig();
```

### 2. 安装缺失的依赖

```bash
npm install --save-dev \
    tailwindcss \
    postcss \
    autoprefixer \
    element-plus \
    lucide-vue-next
```

### 3. 编译构��

```bash
# 开发模式
npm run dev

# 生产构建
npm run build
```

---

## 国际化（i18n）

### 原项目国际化设置

原 `src/i18n.js`：

```javascript
export function t(key, defaultValue = '') {
  // 简单的i18n实现
  const translations = {
    'nav.home': '首页',
    'nav.products': '全部商品',
    // ... 更多翻译
  }
  return translations[key] || defaultValue
}
```

### Symfony UX集成

迁移到Symfony的翻译系统：

1. **创建翻译文件** `translations/messages.zh_CN.yaml`：

```yaml
nav:
  home: 首页
  products: 全部商品
  help: 帮助中心
```

2. **在Vue组件中使用翻译**：

```vue
<script setup>
// 从window对象获取翻译（由Symfony注入）
const t = (key) => window.__TRANSLATIONS__?.[key] || key
</script>

<template>
  <div>{{ t('nav.home') }}</div>
</template>
```

3. **在Symfony模板中注入翻译**：

```twig
<script>
    window.__TRANSLATIONS__ = {
        'nav.home': '{{ 'nav.home' | trans }}',
        'nav.products': '{{ 'nav.products' | trans }}',
        // ... 其他翻译
    };
</script>
```

---

## 常见问题

### Q1：如何保证样式100%一致？

**A**：
1. 复制 `src/assets/base.css` 到Symfony项目
2. 使用相同的 `tailwind.config.js`
3. 在Symfony��� `templates/base.html.twig` 中导入所有样式
4. 所有Vue组件保持原样（不修改style部分）

### Q2：旧的Vue Router路由如何处理？

**A**：
1. 在Symfony中创建对应的Controller和Route
2. 移除 `src/router/index.js` 的导入
3. 修改组件中的 `router.push()` 调用为：
   ```javascript
   // 替换为
   window.location.href = '/new-route'
   // 或使用 Symfony路由生成
   window.location.href = '{{ path('route_name') }}'
   ```

### Q3：如何处理组件中的动态路由参数？

**A**：通过Props传递：

```javascript
// Symfony Controller
return $this->render('vue/product/detail.html.twig', [
    'productId' => $id,
]);
```

```twig
{# Symfony模板 #}
<div id="app">
    <product-detail :product-id="{{ productId }}"></product-detail>
</div>
```

### Q4：如何调试Vue组件？

**A**：
1. 使用Vue DevTools浏览器扩展
2. 组件开发工具仍然可用
3. 浏览器DevTools正常工作

### Q5：ElementPlus组件库是否完全兼容？

**A**：是的，完全兼容。ElementPlus是独立的Vue 3 UI库，不依赖于路由系统。

### Q6：如何处理全局状态？

**A**：使用Vue的 `provide/inject` 或集成状态管理库：

```javascript
// main.js
import { createGlobalProperties } from 'vue'

const globalProperties = {
    apiBaseUrl: window.__API_BASE_URL__ || 'http://localhost:8000',
    currentUser: window.__CURRENT_USER__ || null,
}

app.config.globalProperties = {
    ...app.config.globalProperties,
    ...globalProperties
}
```

---

## 检查清单

集成完成后，逐项验证：

- [ ] 所有Vue组件已复制到 `assets/vue/components/`
- [ ] 所有页面组件已复制到 `assets/vue/pages/`
- [ ] 样式文件已复制，TailwindCSS配置正确
- [ ] 修改后的 `App.vue` 不包含路由逻辑
- [ ] `main.js` 已移除Vue Router导入
- [ ] 所有Symfony Controllers已创建
- [ ] 所有路由已在Symfony中定义
- [ ] CSS/样式在Symfony模板中正确引入
- [ ] ElementPlus中文化已配置
- [ ] 构建过程成功（`npm run build`）
- [ ] 所有页面在浏览器中正确显示
- [ ] 样式保持100%一致
- [ ] 国际化正确处理

---

## 最佳实践

1. **保留原始Vue代码**：不要修改组件逻辑，只修改入口和路由
2. **使用Symfony的路由生成**：在模板中使用 `path()` 和 `url()` 函数
3. **分离关注点**：数据获取由Symfony Controller负责，UI由Vue组件负责
4. **样式优先级**：TailwindCSS > ElementPlus > 自定义样式
5. **性能优化**：使用动态导入(import())来按需加载页面组件
6. **测试**：为Vue组件编写测试，为Symfony Controllers编写功能测试

---

## 额外资源

- [Symfony UX Vue文档](https://symfony.com/doc/current/frontend/ux.html)
- [Vue 3官方文档](https://vuejs.org/)
- [TailwindCSS文档](https://tailwindcss.com/docs)
- [Element Plus文档](https://element-plus.org/)

