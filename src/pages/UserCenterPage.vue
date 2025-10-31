<!--
CSS 引用说明：
1. 全局样式：在 src/main.ts 中自动加载
   - src/assets/main.css (导入 src/assets/base.css)
     - @tailwind base, components, utilities (Tailwind CSS)
     - 全局 CSS 变量 (--color-*, --section-gap, --category-width 等)
   - Element Plus 样式 (element-plus/dist/index.css)
2. 页面局部样式：该文件底部的 <style scoped> 块
3. 导入的子组件样式：由各子组件的 <style scoped> 块提供
-->
<template>
  <div class="min-h-screen flex flex-col">
    <SiteHeader />
    <div class="flex-1 bg-white">
      <div class="mx-auto w-full max-w-[1500px] md:min-w-[1150px] px-4 md:px-0">
        <div class="flex min-h-screen">
          <!-- 左侧导航菜单 -->
          <div class="hidden md:block shrink-0 border-r border-slate-200" :style="{ width: 'var(--category-width)' }">
            <!-- 个人中心组 -->
            <div>
              <button
                @click="expandedGroups.personal = !expandedGroups.personal"
                class="w-full text-slate-900 px-4 py-3 font-medium flex items-center gap-2 border-b border-slate-200 hover:bg-slate-50 transition cursor-pointer"
              >
                <User class="h-5 w-5" />
                <span>个人中心</span>
                <ChevronDown
                  class="h-4 w-4 ml-auto transition-transform duration-300"
                  :style="{ transform: expandedGroups.personal ? 'rotate(0deg)' : 'rotate(-90deg)' }"
                />
              </button>
              <div v-if="expandedGroups.personal" class="overflow-hidden">
                <SidebarItem
                  label="个人中心"
                  :active="activeMenu === 'info'"
                  @click="activeMenu = 'info'"
                />
                <SidebarItem
                  label="基本信息"
                  :active="activeMenu === 'baseinfo'"
                  @click="activeMenu = 'baseinfo'"
                />
                <SidebarItem
                  label="地址簿"
                  :active="activeMenu === 'address'"
                  @click="activeMenu = 'address'"
                />
                <SidebarItem
                  label="VAT税号管理"
                  :active="activeMenu === 'vat'"
                  @click="activeMenu = 'vat'"
                  :with-icon="true"
                />
                <SidebarItem
                  label="账户安全"
                  :active="activeMenu === 'security'"
                  @click="activeMenu = 'security'"
                />
              </div>
            </div>

            <!-- 消息中心组 -->
            <div>
              <button
                @click="expandedGroups.messages = !expandedGroups.messages"
                class="w-full text-slate-900 px-4 py-3 font-medium flex items-center gap-2 border-b border-slate-200 hover:bg-slate-50 transition cursor-pointer"
              >
                <MessageSquare class="h-5 w-5" />
                <span>消息中心</span>
                <ChevronDown
                  class="h-4 w-4 ml-auto transition-transform duration-300"
                  :style="{ transform: expandedGroups.messages ? 'rotate(0deg)' : 'rotate(-90deg)' }"
                />
              </button>
              <div v-if="expandedGroups.messages" class="overflow-hidden">
                <SidebarItem
                  label="商城公告"
                  :active="activeMenu === 'mall-announcement'"
                  @click="activeMenu = 'mall-announcement'"
                />
                <SidebarItem
                  label="营销活动"
                  :active="activeMenu === 'marketing-activity'"
                  @click="activeMenu = 'marketing-activity'"
                />
                <SidebarItem
                  label="订单通知"
                  :active="activeMenu === 'order-notification'"
                  @click="activeMenu = 'order-notification'"
                />
                <SidebarItem
                  label="售后通知"
                  :active="activeMenu === 'after-sales-notification'"
                  @click="activeMenu = 'after-sales-notification'"
                />
                <SidebarItem
                  label="平台消息"
                  :active="activeMenu === 'platform-message'"
                  @click="activeMenu = 'platform-message'"
                />
              </div>
            </div>

            <!-- 商品管理组 -->
            <div>
              <button
                @click="expandedGroups.products = !expandedGroups.products"
                class="w-full text-slate-900 px-4 py-3 font-medium flex items-center gap-2 border-b border-slate-200 hover:bg-slate-50 transition cursor-pointer"
              >
                <Package class="h-5 w-5" />
                <span>商品管理</span>
                <ChevronDown
                  class="h-4 w-4 ml-auto transition-transform duration-300"
                  :style="{ transform: expandedGroups.products ? 'rotate(0deg)' : 'rotate(-90deg)' }"
                />
              </button>
              <div v-if="expandedGroups.products" class="overflow-hidden">
                <SidebarItem
                  label="购物车"
                  :active="activeMenu === 'shopping-cart'"
                  @click="navigateToCart"
                />
                <SidebarItem
                  label="商品管理"
                  :active="activeMenu === 'products'"
                  @click="activeMenu = 'products'"
                  :badge="1"
                />
                <SidebarItem
                  label="刊登推送列表"
                  :active="activeMenu === 'listing-push'"
                  @click="activeMenu = 'listing-push'"
                />
                <SidebarItem
                  label="刊登商品列表"
                  :active="activeMenu === 'listing-products'"
                  @click="activeMenu = 'listing-products'"
                />
                <SidebarItem
                  label="品牌授权"
                  :active="activeMenu === 'brand-auth'"
                  @click="activeMenu = 'brand-auth'"
                />
                <SidebarItem
                  label="商品通知"
                  :active="activeMenu === 'product-notification'"
                  @click="activeMenu = 'product-notification'"
                />
              </div>
            </div>

            <!-- 营销活动组 -->
            <div>
              <button
                @click="expandedGroups.marketing = !expandedGroups.marketing"
                class="w-full text-slate-900 px-4 py-3 font-medium flex items-center gap-2 border-b border-slate-200 hover:bg-slate-50 transition cursor-pointer"
              >
                <Megaphone class="h-5 w-5" />
                <span>营销活动</span>
                <ChevronDown
                  class="h-4 w-4 ml-auto transition-transform duration-300"
                  :style="{ transform: expandedGroups.marketing ? 'rotate(0deg)' : 'rotate(-90deg)' }"
                />
              </button>
              <div v-if="expandedGroups.marketing" class="overflow-hidden">
                <SidebarItem
                  label="返现活动"
                  :active="activeMenu === 'sales'"
                  @click="activeMenu = 'sales'"
                />
              </div>
            </div>

            <!-- 订单管理组 -->
            <div>
              <button
                @click="expandedGroups.orders = !expandedGroups.orders"
                class="w-full text-slate-900 px-4 py-3 font-medium flex items-center gap-2 border-b border-slate-200 hover:bg-slate-50 transition cursor-pointer"
              >
                <ShoppingCart class="h-5 w-5" />
                <span>订单管理</span>
                <ChevronDown
                  class="h-4 w-4 ml-auto transition-transform duration-300"
                  :style="{ transform: expandedGroups.orders ? 'rotate(0deg)' : 'rotate(-90deg)' }"
                />
              </button>
              <div v-if="expandedGroups.orders" class="overflow-hidden">
                <SidebarItem
                  label="咨价单"
                  :active="activeMenu === 'inquiry-order'"
                  @click="activeMenu = 'inquiry-order'"
                />
                <SidebarItem
                  label="我的订单"
                  :active="activeMenu === 'orders'"
                  @click="activeMenu = 'orders'"
                />
                <SidebarItem
                  label="平台载单"
                  :active="activeMenu === 'platform-orders'"
                  @click="activeMenu = 'platform-orders'"
                />
                <SidebarItem
                  label="批量下单"
                  :active="activeMenu === 'batch-orders'"
                  @click="activeMenu = 'batch-orders'"
                />
                <SidebarItem
                  label="异常订单"
                  :active="activeMenu === 'exception-orders'"
                  @click="activeMenu = 'exception-orders'"
                />
              </div>
            </div>

            <!-- 客户服务组 -->
            <div>
              <button
                @click="expandedGroups.customers = !expandedGroups.customers"
                class="w-full text-slate-900 px-4 py-3 font-medium flex items-center gap-2 border-b border-slate-200 hover:bg-slate-50 transition cursor-pointer"
              >
                <Users class="h-5 w-5" />
                <span>客户服务</span>
                <ChevronDown
                  class="h-4 w-4 ml-auto transition-transform duration-300"
                  :style="{ transform: expandedGroups.customers ? 'rotate(0deg)' : 'rotate(-90deg)' }"
                />
              </button>
              <div v-if="expandedGroups.customers" class="overflow-hidden">
                <SidebarItem
                  label="售后管理"
                  :active="activeMenu === 'after-sales-management'"
                  @click="activeMenu = 'after-sales-management'"
                />
                <SidebarItem
                  label="下载中心"
                  :active="activeMenu === 'download-center'"
                  @click="activeMenu = 'download-center'"
                />
              </div>
            </div>

            <!-- 第三方开店组 -->
            <div>
              <button
                @click="expandedGroups.platforms = !expandedGroups.platforms"
                class="w-full text-slate-900 px-4 py-3 font-medium flex items-center gap-2 border-b border-slate-200 hover:bg-slate-50 transition cursor-pointer"
              >
                <Store class="h-5 w-5" />
                <span>第三方开店</span>
                <ChevronDown
                  class="h-4 w-4 ml-auto transition-transform duration-300"
                  :style="{ transform: expandedGroups.platforms ? 'rotate(0deg)' : 'rotate(-90deg)' }"
                />
              </button>
              <div v-if="expandedGroups.platforms" class="overflow-hidden">
                <SidebarItem
                  label="平台授权"
                  :active="activeMenu === 'platform-auth'"
                  @click="activeMenu = 'platform-auth'"
                />
                <SidebarItem
                  label="载单设置"
                  :active="activeMenu === 'order-settings'"
                  @click="activeMenu = 'order-settings'"
                />
                <SidebarItem
                  label="SKU映射"
                  :active="activeMenu === 'sku-mapping'"
                  @click="activeMenu = 'sku-mapping'"
                />
                <SidebarItem
                  label="物流映射"
                  :active="activeMenu === 'logistics-mapping'"
                  @click="activeMenu = 'logistics-mapping'"
                />
                <SidebarItem
                  label="库存更新"
                  :active="activeMenu === 'inventory-update'"
                  @click="activeMenu = 'inventory-update'"
                />
                <SidebarItem
                  label="更新记录"
                  :active="activeMenu === 'update-log'"
                  @click="activeMenu = 'update-log'"
                />
              </div>
            </div>

            <!-- 回复管理组 -->
            <div>
              <button
                @click="expandedGroups.feedback = !expandedGroups.feedback"
                class="w-full text-slate-900 px-4 py-3 font-medium flex items-center gap-2 border-b border-slate-200 hover:bg-slate-50 transition cursor-pointer"
              >
                <Wallet class="h-5 w-5" />
                <span>资产管理</span>
                <ChevronDown
                  class="h-4 w-4 ml-auto transition-transform duration-300"
                  :style="{ transform: expandedGroups.feedback ? 'rotate(0deg)' : 'rotate(-90deg)' }"
                />
              </button>
              <div v-if="expandedGroups.feedback" class="overflow-hidden">
                <SidebarItem
                  label="我的余额"
                  :active="activeMenu === 'my-balance'"
                  @click="activeMenu = 'my-balance'"
                />
                <SidebarItem
                  label="我的账单"
                  :active="activeMenu === 'my-invoices'"
                  @click="activeMenu = 'my-invoices'"
                />
                <SidebarItem
                  label="我的采购券"
                  :active="activeMenu === 'my-vouchers'"
                  @click="activeMenu = 'my-vouchers'"
                />
                <SidebarItem
                  label="支付账号管理"
                  :active="activeMenu === 'payment-account'"
                  @click="activeMenu = 'payment-account'"
                />
              </div>
            </div>

          </div>

          <!-- 右侧主内容 -->
          <div class="flex-1 min-w-0 p-6">
            <!-- 顶部标题栏 -->
            <div class="flex items-center justify-between mb-6">
              <h2 class="text-2xl font-semibold text-slate-900">{{ activeMenuLabel }}</h2>
              <div class="hidden md:flex items-center gap-4 text-sm text-slate-600">
                <span>业务经理</span>
                <span>个人资料</span>
              </div>
            </div>

            <!-- 个人中心面板 -->
            <div v-if="activeMenu === 'info'" class="bg-white rounded-lg border border-slate-200">
              <!-- 用户信息卡片 -->
              <div class="p-6 border-b border-slate-200">
                <div class="flex items-start justify-between">
                  <div>
                    <div class="text-lg font-semibold text-slate-900">金红元</div>
                    <div class="text-sm text-slate-600 mt-2">会员等级：普通会员</div>
                    <div class="flex items-center gap-4 mt-3 text-sm">
                      <span>
                        <span class="text-slate-600">客户ID：</span>
                        <span class="text-slate-900">CNSY51436528</span>
                      </span>
                    </div>
                  </div>
                  <a href="#" class="text-primary hover:text-primary text-sm">7天享受多倍店铺优惠>>></a>
                </div>
              </div>

              <!-- VIP等级 -->
              <div class="p-6 border-b border-slate-200">
                <h3 class="font-semibold text-slate-900 mb-4">金红元 VIP★★★★</h3>
                <div class="grid grid-cols-5 gap-4">
                  <div class="text-center">
                    <div class="inline-block bg-primary text-white px-3 py-1 rounded text-sm mb-2">
                      普通会员
                    </div>
                    <div class="text-slate-600 text-sm">0.00 $</div>
                  </div>
                  <div class="text-center">
                    <div class="text-slate-900 font-medium text-sm mb-2">VIP1</div>
                    <div class="text-slate-600 text-sm">2000.00 $</div>
                  </div>
                  <div class="text-center">
                    <div class="text-slate-900 font-medium text-sm mb-2">VIP2</div>
                    <div class="text-slate-600 text-sm">10000.00 $</div>
                  </div>
                  <div class="text-center">
                    <div class="text-slate-900 font-medium text-sm mb-2">VIP3</div>
                    <div class="text-slate-600 text-sm">50000.00 $</div>
                  </div>
                  <div class="text-center">
                    <div class="text-slate-900 font-medium text-sm mb-2">VIP4</div>
                    <div class="text-slate-600 text-sm">200000.00 $</div>
                  </div>
                </div>
              </div>

              <!-- 专属客户经理 -->
              <div class="p-6 border-b border-slate-200">
                <h3 class="font-semibold text-slate-900 mb-4">专属客户经理</h3>
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <div class="text-sm text-slate-600 mb-2">姓名：详见客户</div>
                    <div class="text-sm text-slate-600 mb-2">手机电话：15710839739</div>
                    <div class="text-sm text-slate-600">联系邮箱：Yunne@saleyee.cn</div>
                  </div>
                  <div class="text-right">
                    <el-button type="primary" size="small">在线沟通</el-button>
                  </div>
                </div>
              </div>

              <!-- 待办事项 -->
              <div class="p-6 border-b border-slate-200">
                <h3 class="font-semibold text-slate-900 mb-4">待办事项</h3>
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                  <div class="text-center">
                    <div class="text-slate-600 text-sm">持��库存</div>
                    <div class="text-primary font-semibold text-lg">0</div>
                  </div>
                  <div class="text-center">
                    <div class="text-slate-600 text-sm">持仓未确认</div>
                    <div class="text-primary font-semibold text-lg">0</div>
                  </div>
                  <div class="text-center">
                    <div class="text-slate-600 text-sm">待件待采购</div>
                    <div class="text-primary font-semibold text-lg">0</div>
                  </div>
                  <div class="text-center">
                    <div class="text-slate-600 text-sm">待上传设��图</div>
                    <div class="text-primary font-semibold text-lg">0</div>
                  </div>
                  <div class="text-center">
                    <div class="text-slate-600 text-sm">申请未通过提醒</div>
                    <div class="text-primary font-semibold text-lg">0</div>
                  </div>
                </div>
              </div>

              <!-- 商品销售排行 -->
              <div class="p-6 border-b border-slate-200">
                <div class="flex items-center justify-between mb-4">
                  <h3 class="font-semibold text-slate-900">商品销售排行</h3>
                  <div class="text-sm text-slate-500">2025-09</div>
                </div>
                <el-table :data="topProducts" style="width: 100%" size="small">
                  <el-table-column prop="rank" label="排序" width="60" />
                  <el-table-column prop="sku" label="SKU" width="100" />
                  <el-table-column prop="name" label="名称" min-width="200" />
                  <el-table-column prop="sales" label="总销售" />
                  <el-table-column prop="ratio" label="占比" />
                </el-table>
                <div class="mt-4 flex gap-2">
                  <el-button type="primary" size="small">详查</el-button>
                  <el-button size="small">下载</el-button>
                </div>
              </div>

              <!-- 月订单趋势 -->
              <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                  <h3 class="font-semibold text-slate-900">月订单趋势</h3>
                  <div class="text-sm text-slate-500">2025-10</div>
                </div>
                <div class="h-64 bg-slate-50 rounded flex items-center justify-center text-slate-500 text-sm">
                  <div class="flex items-center gap-4">
                    <span class="inline-block w-3 h-3 bg-blue-500 rounded-full"></span>合计金额
                    <span class="inline-block w-3 h-3 bg-green-500 rounded-full"></span>USD
                    <span class="inline-block w-3 h-3 bg-yellow-500 rounded-full"></span>GBP
                    <span class="inline-block w-3 h-3 bg-red-500 rounded-full"></span>EUR
                    <span class="inline-block w-3 h-3 bg-cyan-500 rounded-full"></span>CAD
                  </div>
                </div>
                <div class="mt-4">
                  <el-button type="primary" size="small">详查</el-button>
                </div>
              </div>
            </div>

            <!-- 基本信息面板 -->
            <BasicInfoPage v-else-if="activeMenu === 'baseinfo'" />

            <!-- 地址簿面板 -->
            <AddressBookPage v-else-if="activeMenu === 'address'" />

            <!-- 账户安全面板 -->
            <SecurityPage v-else-if="activeMenu === 'security'" />

            <!-- 商城公告面板 -->
            <MallAnnouncementPage v-else-if="activeMenu === 'mall-announcement'" />

            <!-- 营销活动面板 -->
            <MarketingActivityPage v-else-if="activeMenu === 'marketing-activity'" />

            <!-- 订单通知面板 -->
            <OrderNotificationPage v-else-if="activeMenu === 'order-notification'" />

            <!-- 售后通知面板 -->
            <AfterSalesNotificationPage v-else-if="activeMenu === 'after-sales-notification'" />

            <!-- 平台消息面板 -->
            <PlatformMessagePage v-else-if="activeMenu === 'platform-message'" />

            <!-- 商品管理面板 -->
            <ProductManagementPage v-else-if="activeMenu === 'products'" />

            <!-- 刊登推���列表面板 -->
            <ListingPushPage v-else-if="activeMenu === 'listing-push'" />

            <!-- 刊登商品列表面板 -->
            <ListingProductsPage v-else-if="activeMenu === 'listing-products'" />

            <!-- 品牌授权面板 -->
            <BrandAuthPage v-else-if="activeMenu === 'brand-auth'" />

            <!-- 商品通知面板 -->
            <ProductNotificationPage v-else-if="activeMenu === 'product-notification'" />

            <!-- 返现活动面板 -->
            <CashbackActivityPage v-else-if="activeMenu === 'sales'" />

            <!-- 我的余额面板 -->
            <MyBalancePage v-else-if="activeMenu === 'my-balance'" />

            <!-- 询价单面板 -->
            <InquiryOrderPage v-else-if="activeMenu === 'inquiry-order'" />

            <!-- 我的订单面板 -->
            <MyOrderPage v-else-if="activeMenu === 'orders'" />

            <!-- 我的账单面板 -->
            <MyInvoicesPage v-else-if="activeMenu === 'my-invoices'" />

            <!-- 我的采购券面板 -->
            <MyVouchersPage v-else-if="activeMenu === 'my-vouchers'" />

            <!-- 平台载单面板 -->
            <PlatformOrderPage v-else-if="activeMenu === 'platform-orders'" />

            <!-- 平台授权面�� -->
            <PlatformAuthPage v-else-if="activeMenu === 'platform-auth'" />

            <!-- 载单设置面板 -->
            <OrderSettingsPage v-else-if="activeMenu === 'order-settings'" />

            <!-- SKU映射面板 -->
            <SkuMappingPage v-else-if="activeMenu === 'sku-mapping'" />

            <!-- 物流映射面板 -->
            <LogisticsMappingPage v-else-if="activeMenu === 'logistics-mapping'" />

            <!-- 库存更新面板 -->
            <InventoryUpdatePage v-else-if="activeMenu === 'inventory-update'" />

            <!-- 更新记录面板 -->
            <UpdateLogPage v-else-if="activeMenu === 'update-log'" />

            <!-- 批量下单面板 -->
            <BatchOrderPage v-else-if="activeMenu === 'batch-orders'" />

            <!-- 下载中心面板 -->
            <DownloadCenterPage v-else-if="activeMenu === 'download-center'" />

            <!-- 支付账号管理面板 -->
            <PaymentAccountPage v-else-if="activeMenu === 'payment-account'" />

            <!-- 异常订单面板 -->
            <ExceptionOrderPage v-else-if="activeMenu === 'exception-orders'" />

            <!-- 售后管理面板 -->
            <AfterSalesManagementPage v-else-if="activeMenu === 'after-sales-management'" />

            <!-- 其他面板 -->
            <div v-else class="bg-white rounded-lg border border-slate-200 p-6">
              <el-empty description="此功能开发中..." />
            </div>
          </div>
        </div>
      </div>
    </div>
    <SiteFooter />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import {
  User,
  ChevronDown,
  MessageSquare,
  Package,
  Megaphone,
  ShoppingCart,
  Users,
  Store,
  Wallet,
  BarChart3,
  FileText,
} from 'lucide-vue-next'
import SiteHeader from '@/components/SiteHeader.vue'
import SiteFooter from '@/components/SiteFooter.vue'
import SidebarItem from '@/components/SidebarItem.vue'
import BasicInfoPage from '@/pages/BasicInfoPage.vue'
import AddressBookPage from '@/pages/AddressBookPage.vue'
import SecurityPage from '@/pages/SecurityPage.vue'
import MallAnnouncementPage from '@/pages/MallAnnouncementPage.vue'
import MarketingActivityPage from '@/pages/MarketingActivityPage.vue'
import OrderNotificationPage from '@/pages/OrderNotificationPage.vue'
import AfterSalesNotificationPage from '@/pages/AfterSalesNotificationPage.vue'
import PlatformMessagePage from '@/pages/PlatformMessagePage.vue'
import ProductManagementPage from '@/pages/ProductManagementPage.vue'
import ListingPushPage from '@/pages/ListingPushPage.vue'
import ListingProductsPage from '@/pages/ListingProductsPage.vue'
import BrandAuthPage from '@/pages/BrandAuthPage.vue'
import ProductNotificationPage from '@/pages/ProductNotificationPage.vue'
import CashbackActivityPage from '@/pages/CashbackActivityPage.vue'
import InquiryOrderPage from '@/pages/InquiryOrderPage.vue'
import MyOrderPage from '@/pages/MyOrderPage.vue'
import MyInvoicesPage from '@/pages/MyInvoicesPage.vue'
import PlatformOrderPage from '@/pages/PlatformOrderPage.vue'
import BatchOrderPage from '@/pages/BatchOrderPage.vue'
import ExceptionOrderPage from '@/pages/ExceptionOrderPage.vue'
import AfterSalesManagementPage from '@/pages/AfterSalesManagementPage.vue'
import DownloadCenterPage from '@/pages/DownloadCenterPage.vue'
import PlatformAuthPage from '@/pages/PlatformAuthPage.vue'
import OrderSettingsPage from '@/pages/OrderSettingsPage.vue'
import SkuMappingPage from '@/pages/SkuMappingPage.vue'
import LogisticsMappingPage from '@/pages/LogisticsMappingPage.vue'
import InventoryUpdatePage from '@/pages/InventoryUpdatePage.vue'
import UpdateLogPage from '@/pages/UpdateLogPage.vue'
import MyBalancePage from '@/pages/MyBalancePage.vue'
import MyVouchersPage from '@/pages/MyVouchersPage.vue'
import PaymentAccountPage from '@/pages/PaymentAccountPage.vue'

const activeMenu = ref('info')
const expandedGroups = ref({
  personal: true,
  messages: false,
  products: false,
  marketing: false,
  orders: false,
  customers: false,
  platforms: false,
  feedback: false,
})

const menuLabels = {
  info: '个人中心',
  baseinfo: '基本信息',
  address: '地址簿',
  vat: 'VAT税号管理',
  security: '账户安全',
  messages: '消息中心',
  'mall-announcement': '商城公告',
  'marketing-activity': '营销活动',
  'order-notification': '订单通知',
  'after-sales-notification': '售后通知',
  'platform-message': '��台消息',
  'shopping-cart': '购物车',
  products: '商品管理',
  'listing-push': '刊登推送列表',
  'listing-products': '刊登商品列表',
  'brand-auth': '品牌授权',
  'product-notification': '商品通知',
  sales: '返现活动',
  'inquiry-order': '咨价单',
  orders: '我的订单',
  'platform-orders': '平台载单',
  'batch-orders': '批量下单',
  'exception-orders': '异常订单',
  'after-sales-management': '售后管理',
  'download-center': '下载中心',
  customers: '客户服务',
  'my-balance': '我的余额',
  'my-invoices': '我的账单',
  'my-vouchers': '我的采购券',
  'payment-account': '支付账号管理',
  'platform-auth': '平台授权',
  'order-settings': '载单设置',
  'sku-mapping': 'SKU映射',
  'logistics-mapping': '物流映射',
  'inventory-update': '库存更新',
  'update-log': '更新记录',
  'third-platform': '第三方开店',
  certification: '资产管理',
}

const activeMenuLabel = computed(() => menuLabels[activeMenu.value] || '个人中心')

const topProducts = ref([
  { rank: 1, sku: '', name: '', sales: '', ratio: '' },
  { rank: 2, sku: '', name: '', sales: '', ratio: '' },
  { rank: 3, sku: '', name: '', sales: '', ratio: '' },
])

const navigateToCart = () => {
  window.location.href = '/cart'
}
</script>

<style scoped>
:deep(.el-table) {
  font-size: 12px;
}

:deep(.el-table th) {
  background-color: #f8f9fa;
}

:deep(.el-dropdown-link) {
  color: white;
}
</style>
