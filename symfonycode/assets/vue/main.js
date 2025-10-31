import './styles/base.css'

import { createApp } from 'vue'
import App from './App.vue'

import ElementPlus from 'element-plus'
import 'element-plus/dist/index.css'
import zhCn from 'element-plus/es/locale/lang/zh-cn'

// ==================== 组件导入 ====================

// 布局组件
import SiteHeader from './components/SiteHeader.vue'
import CategoryNavBar from './components/CategoryNavBar.vue'
import SiteFooter from './components/SiteFooter.vue'
import RightFloatNav from './components/RightFloatNav.vue'

// 页面组件
import AllProductsPage from './pages/AllProductsPage.vue'
import ItemDetailPage from './pages/ItemDetailPage.vue'
import HelpCenterPage from './pages/HelpCenterPage.vue'
import FAQDetailPage from './pages/FAQDetailPage.vue'
import ContactUsPage from './pages/ContactUsPage.vue'
import AboutSaleyeePage from './pages/AboutSaleyeePage.vue'
import OperationGuidePage from './pages/OperationGuidePage.vue'
import HotSalesPage from './pages/HotSalesPage.vue'
import DiscountSalePage from './pages/DiscountSalePage.vue'
import NewPage from './pages/NewPage.vue'
import DirectDeliveryPage from './pages/DirectDeliveryPage.vue'
import AllCategoriesPage from './pages/AllCategoriesPage.vue'
import MallAnnouncementPage from './pages/MallAnnouncementPage.vue'
import MallAnnouncementDetailPage from './pages/MallAnnouncementDetailPage.vue'

// 功能组件
import HeroBanner from './components/HeroBanner.vue'
import CategorySidebar from './components/CategorySidebar.vue'
import PlatformBoutique from './components/PlatformBoutique.vue'
import BestsellerProducts from './components/BestsellerProducts.vue'
import BrandProducts from './components/BrandProducts.vue'
import RelatedProducts from './components/RelatedProducts.vue'
import ProductDetailTabs from './components/ProductDetailTabs.vue'

// ==================== 创建 Vue 应用 ====================

const app = createApp(App)

// ==================== 全局注册组件 ====================

// 布局组件
app.component('SiteHeader', SiteHeader)
app.component('CategoryNavBar', CategoryNavBar)
app.component('SiteFooter', SiteFooter)
app.component('RightFloatNav', RightFloatNav)

// 页面组件
app.component('AllProductsPage', AllProductsPage)
app.component('ItemDetailPage', ItemDetailPage)
app.component('HelpCenterPage', HelpCenterPage)
app.component('FAQDetailPage', FAQDetailPage)
app.component('ContactUsPage', ContactUsPage)
app.component('AboutSaleyeePage', AboutSaleyeePage)
app.component('OperationGuidePage', OperationGuidePage)
app.component('HotSalesPage', HotSalesPage)
app.component('DiscountSalePage', DiscountSalePage)
app.component('NewPage', NewPage)
app.component('DirectDeliveryPage', DirectDeliveryPage)
app.component('AllCategoriesPage', AllCategoriesPage)
app.component('MallAnnouncementPage', MallAnnouncementPage)
app.component('MallAnnouncementDetailPage', MallAnnouncementDetailPage)

// 功能组件
app.component('HeroBanner', HeroBanner)
app.component('CategorySidebar', CategorySidebar)
app.component('PlatformBoutique', PlatformBoutique)
app.component('BestsellerProducts', BestsellerProducts)
app.component('BrandProducts', BrandProducts)
app.component('RelatedProducts', RelatedProducts)
app.component('ProductDetailTabs', ProductDetailTabs)

// ==================== 配置 Element Plus ====================

app.use(ElementPlus, { locale: zhCn })

// ==================== 全局属性 ====================

app.config.globalProperties.$apiBaseUrl = window.__INITIAL_STATE__?.apiBaseUrl || '/api'
app.config.globalProperties.$t = (key, defaultValue = '') => {
  return window.__TRANSLATIONS__?.[key] || defaultValue
}

// ==================== 全局错误处理 ====================

app.config.errorHandler = (err, instance, info) => {
  console.error('Vue 错误:', err)
  console.error('错误信息:', info)
}

// ==================== 挂载应用 ====================

// 等待 DOM 准备好后挂载
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => {
    const appElement = document.getElementById('app')
    if (appElement) {
      app.mount('#app')
    }
  })
} else {
  const appElement = document.getElementById('app')
  if (appElement) {
    app.mount('#app')
  }
}

export default app
