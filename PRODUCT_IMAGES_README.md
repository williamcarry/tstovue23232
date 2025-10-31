# 商品详情页面图片下载说明

## 概述

已将商品详情页面 (`src/pages/ItemDetailPage.vue`) 按照附件 HTML 布局进行了重新设计，并将所有外链图片替换为本地引用。

## 布局改进

### 主要改动：
1. **优化了左侧图片库布局**
   - 主图容器：500x500px
   - 缩略图导航：88px 高度
   - 大图预览：1000x1000px（悬停时显示）

2. **重组了右侧商品信息区**
   - 商品标题与 SKU/SPU 信息
   - 商品标签（一件代发、工厂直采）
   - 价格与会员折扣区域
   - 服务说明（圈货、自提、包邮等）
   - 发货区域与物流选项
   - 操作按钮组

3. **优化了样式结构**
   - 使用 Flexbox 和 Grid 布局
   - 响应式设计支持
   - 更好的可读性和交互体验

## 图片管��

### 占位符图标（已创建）

已在 `public/images/icons/` 目录下创建 SVG 占位符图标：

- `prohibition.svg` - 禁止标志
- `true.svg` - 支持/检查标记
- `false.svg` - 不支持/叉号
- `insurance.svg` - 帮助/信息图标
- `collect.svg` - 收藏/心形图标
- `feedback.svg` - 反馈/星形图标
- `note.svg` - 注意/警告图标
- `closed.svg` - 关闭/叉号

### 下载真实图片（可选）

如果需要使用原始图片而不是 SVG 占位符，可以运行以下命令：

```bash
npm run download:product-images
```

这会自动从 Saleyee 服务器下载所有需要的图片到 `public/images/icons/` 目录。

**脚本文件：** `scripts/download-product-images.js`

## 使用指南

### 本地开发
```bash
npm run dev
```

### 构建生产版本
```bash
npm run build
```

### 预览生产构建
```bash
npm run preview
```

## 文件结构

```
public/images/
├── icons/
│   ├── prohibition.svg
│   ├── true.svg
│   ├── false.svg
│   ├── insurance.svg
│   ├── collect.svg
│   ├── feedback.svg
│   ├── note.svg
│   └── closed.svg
└── supplier/

src/pages/
└── ItemDetailPage.vue  (已更新)

scripts/
└── download-product-images.js  (新增)
```

## 代码示例

### 图片引用示例
```html
<!-- 使用本地 SVG 占位符 -->
<img loading="lazy" src="/images/icons/true.svg" class="service-icon" alt="支持" />

<!-- 使用下载的真实图片 -->
<img loading="lazy" src="/images/icons/true.png" class="service-icon" alt="支持" />
```

## 样式特点

- 响应式布局，适配不同屏幕宽度
- 流畅的过渡动画
- 悬停效果和交互反馈
- 清晰的信息层级
- 符合 Saleyee 平台设计规范

## 浏览器兼容性

- 现代浏览器（Chrome, Firefox, Safari, Edge）
- 支持 SVG 图片格式
- CSS Grid 和 Flexbox 支持

## 后续优化建议

1. **图片优化**
   - 考虑使用 WebP 格式压缩图片
   - 实现图片延迟加载（已使用 `loading="lazy"`）

2. **性能优化**
   - 缓存下载的图片
   - 使用 CDN 加速静态资源

3. **可访问性**
   - 为所有图片提供有意义的 alt 文本
   - 增强键盘导航支持

4. **国际化**
   - 支持多语言文本
   - 提供国际化的日期时间格式

## 问题排查

### 图片无法加载
1. 确认文件路径正确
2. 检查文件是否存在：`public/images/icons/`
3. 运行 `npm run download:product-images` 下载真实图片

### 样式显示异常
1. 清除浏览器缓存
2. ��新启动开发服务器：`npm run dev`
3. 检查浏览器控制台错误消息

## 联系方式

如有任何问题或建议，请提交 Issue 或联系开发团队。
