# Symfony UX Vue 项目快速开始指南

这个 `symfonycode` 文件夹包含完整的 Symfony + Vue 3 项目结构，可以直接复制到你的 Symfony 项目中使用。

## 📁 文件结构

```
symfonycode/
├── src/Controller/Shop/                # Symfony 控制器
│   ├── ShopController.php             # 商品管理控制器
│   └── HelpController.php             # 帮助中心控制器
├── templates/                          # Symfony Twig 模板
│   ├── base.html.twig                 # 基础模板
│   └── shop/                          # 商品页面模板
├── assets/vue/                        # Vue 应用源码
│   ├── App.vue                        # 根组件
│   ├── main.js                        # 应用入口
│   ├── i18n.js                        # 国际化配置
│   ├── pages/                         # 页面组件
│   ├── components/                    # 功能组件
│   └── styles/                        # 样式文件
├── tailwind.config.js                 # Tailwind CSS 配置
├── postcss.config.js                  # PostCSS 配置
├── vite.config.js                     # Vite 配置
└── package.json                       # Node.js 依赖配置
```

## 🚀 快速开始

### 步骤 1: 复制文件到 Symfony 项目

```bash
# 复制控制器到 src/Controller/Shop
cp -r symfonycode/src/Controller/Shop/* your-symfony-project/src/Controller/

# 复制模板到 templates/shop
cp -r symfonycode/templates/* your-symfony-project/templates/

# 复制 Vue 应用到 assets/vue
cp -r symfonycode/assets/vue your-symfony-project/assets/

# 复制配置文件到项目根目录
cp symphonycode/tailwind.config.js your-symfony-project/
cp symphonycode/postcss.config.js your-symfony-project/
cp symphonycode/vite.config.js your-symfony-project/
cp symphonycode/package.json your-symfony-project/
```

### 步骤 2: 安装依赖

```bash
cd your-symfony-project

# 安装 Composer 依赖
composer require symfony/ux-vue

# 安装 npm 依赖
npm install
```

### 步骤 3: 构建前端资源

```bash
# 开发模式（热更新）
npm run dev

# 生产构建
npm run build
```

### 步骤 4: 配置 Symfony 路由

如果你的 Symfony 项目还没有启用属性路由，请在 `config/routes.yaml` 中添加：

```yaml
# 路由配置
controllers:
    resource: ../src/Controller/
    type: attribute

# API 路由
api:
    resource: ../src/Controller/
    type: attribute
    prefix: /api
```

### 步骤 5: 更新基础模板（可选）

在 `templates/base.html.twig` 中引入 CSS 和 JS：

```twig
{% block stylesheets %}
    {{ import_asset('build/assets/app.css') }}
{% endblock %}

{% block javascripts %}
    {{ import_asset('build/assets/app.js') }}
{% endblock %}
```

## 📋 主要功能

### 商品模块 (ShopController)

- ✅ 商品列表（支持筛选、排序、分页）
- ✅ 商品详情
- ✅ 热销商品
- ✅ 折扣商品
- ✅ 新品上市
- ✅ 直发商品
- ✅ 分类浏览
- ✅ API 端点

### 帮助中心模块 (HelpController)

- ✅ 帮助中心首页
- ✅ 常见问题（FAQ）
- ✅ FAQ 详情
- ✅ 操作指南
- ✅ 联系我们
- ✅ 关于平台
- ✅ 平台公告
- ✅ API 端点

### 前端功能

- ✅ 响应式布局
- ✅ Tailwind CSS 样式
- ✅ ���英双语支持
- ✅ Element Plus UI 组件库
- ✅ Lucide 图标库
- ✅ 国际化 (i18n)

## 🔧 配置说明

### 环境变量

在 `.env` 中配置：

```env
# Vite 开发服务器
VITE_DEV_SERVER_URL=http://localhost:5173

# API 基础 URL
VITE_API_BASE_URL=/api
```

### 数据模拟

所有控制器使用模拟数据。在生产环境中，需要：

1. 创建数据库实体（Entity）
2. 创建数据库迁移（Migration）
3. 修改控制器从数据库查询数据

示例：

```php
#[Route('/shop/products', name: 'shop_products')]
public function getProducts(ProductRepository $repo): Response
{
    $products = $repo->findAll();
    
    return $this->render('shop/index.html.twig', [
        'products' => json_encode($products),
    ]);
}
```

## 🎨 自定义样式

### Tailwind CSS 配置

编辑 `tailwind.config.js` 修改主题色：

```javascript
theme: {
    extend: {
        colors: {
            primary: '#CB261C',  // 修改为你的品牌色
        },
    },
}
```

### 自定义 CSS

在 `assets/vue/styles/base.css` 中添加自定义样式。

## 🌍 国际化

### 添加新的语言

编辑 `assets/vue/i18n.js`：

```javascript
const messages = {
  'en-US': {
    'nav.home': 'Home',
    // ... 更多翻译
  },
  'zh-CN': {
    'nav.home': '首页',
    // ... 更多翻译
  },
  'es-ES': {
    'nav.home': 'Inicio',
    // ... 更多翻译
  }
}
```

## 📱 路由映射

| URL | 组件 | 控制器方法 | 说明 |
|-----|------|----------|------|
| `/` | AllProductsPage | ShopController::index | 首页/商品列表 |
| `/shop/product/{id}` | ItemDetailPage | ShopController::productDetail | 商品详情 |
| `/shop/hot-sales` | HotSalesPage | ShopController::hotSales | 热销商品 |
| `/shop/discount-sale` | DiscountSalePage | ShopController::discountSale | 折扣商品 |
| `/shop/new` | NewPage | ShopController::newProducts | 新品 |
| `/shop/direct-delivery` | DirectDeliveryPage | ShopController::directDelivery | 直发 |
| `/shop/categories` | AllCategoriesPage | ShopController::categories | 分类 |
| `/help/` | HelpCenterPage | HelpController::index | 帮助中心 |
| `/help/faq/{id}` | FAQDetailPage | HelpController::faqDetail | FAQ详情 |
| `/help/guide` | OperationGuidePage | HelpController::guide | 操作指南 |
| `/help/contact` | ContactUsPage | HelpController::contact | 联系我们 |
| `/help/about` | AboutSaleyeePage | HelpController::about | 关于平台 |
| `/help/announcements` | MallAnnouncementPage | HelpController::announcements | 公告列表 |
| `/help/announcement/{id}` | MallAnnouncementDetailPage | HelpController::announcementDetail | 公告详情 |

## 🔌 API 端点

### 商品 API

```
GET  /api/shop/products              获取商品列表
GET  /api/shop/product/{id}          获取商品详情
```

### 帮助 API

```
GET  /api/help/faqs                  获取 FAQ 列表
GET  /api/help/categories            获取帮助分类
```

## 🔐 安全建议

1. **验证用户输入** - 所有表单数据都应验证
2. **CSRF 保护** - 使用 Symfony 的 CSRF 令牌
3. **权限检查** - 使用 Symfony Security 检查用户权限
4. **SQL 注入防护** - 使用 Doctrine ORM 或参数化查询
5. **XSS 防护** - Vue 自动转义，Twig 使用 `|escape` 过滤器

## 📚 依赖说明

### Composer 依赖

- `symfony/ux-vue` - Symfony Vue 支持
- `symfony/asset-mapper` - 资源管理（可选）

### NPM 依赖

- `vue@^3.5.17` - Vue 3
- `element-plus@^2.11.4` - UI 组件库
- `lucide-vue-next@^0.545.0` - 图标库
- `tailwindcss@^3.4.11` - CSS 框架
- `vite@^7.0.0` - 构建工具

## 🐛 常见问题

### 1. Vue 组件未加载

**问题**: 页面显示空白或组件未渲染

**解决方案**:
- 检查浏览器控制台的错误信息
- 确保 `npm run build` 成功完成
- 清除浏览器缓存
- 检查 `main.js` 中的组件注册

### 2. 样式未应用

**问题**: TailwindCSS 样式不生效

**解决方案**:
- 检查 `tailwind.config.js` 的 `content` 配置
- 运行 `npm run dev` 重新编译
- 清除 `public/build` 目录

### 3. 路由 404

**问题**: 访问页面返回 404

**解决方案**:
- 检查控制器的 `#[Route]` 属性
- 确保路由定义正确
- 检查 `config/routes.yaml` 配置

### 4. 数据未显示

**问题**: 页面加载但没有数据

**解决方案**:
- 检查浏览器网络请求
- 查看控制器是否返回正确的 JSON
- 检查 Vue 组件的 `props` 定义

## 📖 进一步学习

- [Symfony 官方文档](https://symfony.com/doc/current/index.html)
- [Vue 3 官方文档](https://vuejs.org/)
- [TailwindCSS 文档](https://tailwindcss.com/)
- [Element Plus 文档](https://element-plus.org/)

## 📝 许可证

MIT

## 💬 支持

如有问题，请参考：
- Symfony 文档
- Vue 3 文档
- 项目中的代码注释

---

祝你使用愉快！🎉
