<!--
CSS 引用说明：
1. 全局样式：在 src/main.ts 中自动加��
   - src/assets/main.css (导入 src/assets/base.css)
     - @tailwind base, components, utilities (Tailwind CSS)
     - 全局 CSS 变量 (--color-*, --section-gap, --category-width 等)
   - Element Plus 样式 (element-plus/dist/index.css)
2. 页面局部样式：该文件底部的 <style scoped> 块
3. 导入的子组件样式：由各子组件的 <style scoped> 块提供
-->
<template>
  <div class="min-h-screen" style="background-color: #f2f3f7">
    <SiteHeader />

    <!-- Hero Banner with Carousel -->
    <div class="w-full">
      <div class="mx-auto w-full max-w-[1500px] md:w-[80%] md:min-w-[1200px] px-4 md:px-0 py-6">
        <NewProductBanner :height="400" />
      </div>
    </div>

    <!-- Main Content: Two Column Layout -->
    <div class="mx-auto w-full max-w-[1500px] md:w-[80%] md:min-w-[1200px] px-4 md:px-0 py-6">
      <div class="bg-white rounded-lg shadow-md p-6 flex gap-6">
        <!-- Left Column: Weekly Featured (本周强推) -->
        <div class="w-[35%]">
          <h2 class="text-2xl font-bold text-slate-900 mb-4">本周强���</h2>
          <div class="relative h-96 overflow-hidden rounded-lg">
            <transition name="fade" mode="out-in">
              <a
                v-if="weeklyFeatured.length > 0"
                :key="currentWeeklyIdx"
                :href="`/item/${weeklyFeatured[currentWeeklyIdx].spu}.html`"
                class="block absolute inset-0"
              >
                <img
                  :src="weeklyFeatured[currentWeeklyIdx].mainImage"
                  :alt="weeklyFeatured[currentWeeklyIdx].title"
                  class="w-full h-full object-cover"
                />
              </a>
            </transition>
            <!-- Navigation Arrows -->
            <button
              v-if="weeklyFeatured.length > 1"
              @click="prevWeekly"
              class="absolute left-2 top-1/2 -translate-y-1/2 w-10 h-10 flex items-center justify-center rounded-full bg-black/35 text-white hover:bg-black/50 z-10"
            >
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
              </svg>
            </button>
            <button
              v-if="weeklyFeatured.length > 1"
              @click="nextWeekly"
              class="absolute right-2 top-1/2 -translate-y-1/2 w-10 h-10 flex items-center justify-center rounded-full bg-black/35 text-white hover:bg-black/50 z-10"
            >
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </button>
          </div>

          <!-- Product Details -->
          <div v-if="weeklyFeatured.length > 0" class="mt-4">
            <a :href="`/item/${weeklyFeatured[currentWeeklyIdx].spu}.html`" class="block text-sm text-slate-800 hover:text-primary line-clamp-2 mb-2 font-medium">
              {{ weeklyFeatured[currentWeeklyIdx].title }}
            </a>
            <p class="text-primary font-bold text-sm mb-1">{{ formatPrice(weeklyFeatured[currentWeeklyIdx]._price) }}</p>
            <p v-if="weeklyFeatured[currentWeeklyIdx]._originalPrice" class="text-slate-400 line-through text-xs mb-2">{{ formatPrice(weeklyFeatured[currentWeeklyIdx]._originalPrice) }}</p>
            <p class="text-red-600 text-xs">登录后可见</p>
            <div class="mt-2">
              <button class="border border-primary text-primary px-2 py-1 text-xs rounded hover:bg-primary hover:text-white transition">
                {{ weeklyFeatured[currentWeeklyIdx]._warehouses[0] }}
              </button>
            </div>
          </div>
        </div>

        <!-- Right Column: Hot Sales (热销新品) -->
        <div class="w-[65%]">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl font-bold text-slate-900">热销新品</h2>
            <ul class="flex gap-3">
              <li
                v-for="(cat, idx) in categories"
                :key="idx"
                @click="selectedCategory = idx"
                :style="selectedCategory === idx ? { backgroundColor: '#CB261C', color: '#ffffff' } : {}"
                :class="[
                  'px-4 py-2 rounded text-sm cursor-pointer transition whitespace-nowrap',
                  selectedCategory === idx
                    ? ''
                    : 'bg-gray-100 text-slate-700 hover:bg-gray-200'
                ]"
              >
                {{ cat }}
              </li>
            </ul>
          </div>

          <!-- Product Grid -->
          <div class="grid grid-cols-4 gap-4">
            <div v-for="p in hotSalesTop8" :key="p.spu" class="bg-[#f6f6f6] rounded-lg overflow-hidden hover:shadow transition">
              <a :href="`/item/${p.spu}.html`" class="block relative">
                <div class="relative w-full aspect-square bg-slate-100 overflow-hidden">
                  <img :src="p.mainImage" :alt="p.title" class="w-full h-full object-cover" />
                  <div class="absolute top-2 right-2 bg-yellow-500 text-white text-xs px-1.5 py-0.5 rounded-t-lg rounded-br-lg">NEW</div>
                </div>
              </a>

              <div class="p-3">
                <a :href="`/item/${p.spu}.html`" class="block mb-1">
                  <h3 class="text-xs text-slate-800 font-medium line-clamp-1 hover:text-primary transition">{{ p.title }}</h3>
                </a>
                <p class="text-red-600 text-xs font-semibold mb-1">登录后可见</p>
                <div class="flex items-center justify-between">
                  <p class="text-slate-800 font-bold text-xs">{{ formatPrice(p._price) }}</p>
                  <span class="text-xs text-slate-500">{{ p._warehouses[0] }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Bottom Section: Filter and Product List -->
    <div class="mx-auto w-full max-w-[1500px] md:w-[80%] md:min-w-[1200px] px-4 md:px-0 py-6">
      <div class="flex gap-4">
        <!-- Left Sidebar: Filters -->
        <div class="w-1/6 bg-white rounded-lg p-4" style="height: max-content">
          <!-- 区域 Filter -->
          <div class="mb-6">
            <p class="font-bold text-slate-900 mb-4 text-base">区域</p>
            <ul class="flex flex-wrap gap-1">
              <li v-for="(region, idx) in regions" :key="idx" class="w-1/2">
                <label class="flex items-center cursor-pointer">
                  <input
                    type="checkbox"
                    :value="region"
                    class="mr-2 rounded"
                    @change="toggleRegion(region)"
                  />
                  <span class="text-sm text-slate-700">{{ region }}</span>
                </label>
              </li>
            </ul>
          </div>

          <!-- 库存 Filter -->
          <div class="mb-6">
            <p class="font-bold text-slate-900 mb-4 text-base">库存</p>
            <div class="flex items-center gap-2">
              <input
                v-model.number="inventoryStart"
                type="text"
                placeholder="最低"
                class="flex-1 px-2 py-2 border border-gray-300 rounded text-sm"
              />
              <span>-</span>
              <input
                v-model.number="inventoryEnd"
                type="text"
                placeholder="最高"
                class="flex-1 px-2 py-2 border border-gray-300 rounded text-sm"
              />
              <button
                @click="applyInventoryFilter"
                class="px-3 py-2 bg-red-600 text-white text-sm rounded hover:bg-red-700"
              >
                确认
              </button>
            </div>
          </div>

          <!-- 价格 Filter -->
          <div class="mb-6">
            <p class="font-bold text-slate-900 mb-4 text-base">价格</p>
            <div class="flex items-center gap-2">
              <input
                v-model.number="priceStart"
                type="text"
                placeholder="最低"
                class="flex-1 px-2 py-2 border border-gray-300 rounded text-sm"
              />
              <span>-</span>
              <input
                v-model.number="priceEnd"
                type="text"
                placeholder="最高"
                class="flex-1 px-2 py-2 border border-gray-300 rounded text-sm"
              />
              <button
                @click="applyPriceFilter"
                class="px-3 py-2 bg-red-600 text-white text-sm rounded hover:bg-red-700"
              >
                确认
              </button>
            </div>
          </div>

          <!-- 上新时间 Filter -->
          <div class="mb-6">
            <p class="font-bold text-slate-900 mb-4 text-base">上新时���</p>
            <ul class="space-y-2">
              <li v-for="(time, idx) in newTimes" :key="idx">
                <label class="flex items-center cursor-pointer">
                  <input
                    type="radio"
                    :value="time.value"
                    v-model="selectedNewTime"
                    class="mr-2"
                  />
                  <span class="text-sm text-slate-700">{{ time.label }}</span>
                </label>
              </li>
            </ul>
          </div>

          <!-- 发货类型 Filter -->
          <div class="mb-6">
            <p class="font-bold text-slate-900 mb-4 text-base">发货类型</p>
            <ul class="space-y-2">
              <li v-for="(type, idx) in shipmentTypes" :key="idx">
                <label class="flex items-center cursor-pointer">
                  <input
                    type="radio"
                    :value="type.value"
                    v-model="selectedShipmentType"
                    class="mr-2"
                  />
                  <span class="text-sm text-slate-700">{{ type.label }}</span>
                </label>
              </li>
            </ul>
          </div>

          <!-- 交易模式 Filter -->
          <div>
            <p class="font-bold text-slate-900 mb-4 text-base">交易模式</p>
            <ul class="space-y-2">
              <li v-for="(mode, idx) in saleModes" :key="idx">
                <label class="flex items-center cursor-pointer">
                  <input
                    type="radio"
                    :value="mode.value"
                    v-model="selectedSaleMode"
                    class="mr-2"
                  />
                  <span class="text-sm text-slate-700">{{ mode.label }}</span>
                </label>
              </li>
            </ul>
          </div>
        </div>

        <!-- Right Content Area -->
        <div class="w-5/6">
          <!-- Category Carousel -->
          <div class="bg-white rounded-lg p-4 mb-4">
            <div class="relative">
              <div class="flex overflow-x-auto gap-4 pb-4">
                <div
                  v-for="(cat, idx) in categoryCarousel"
                  :key="idx"
                  class="flex-shrink-0 text-center cursor-pointer group"
                >
                  <div class="w-32 h-32 bg-gray-200 rounded-lg overflow-hidden mb-2 group-hover:shadow-lg transition">
                    <img
                      :src="cat.image"
                      :alt="cat.name"
                      class="w-full h-full object-cover"
                    />
                  </div>
                  <p class="text-sm text-slate-700 text-center">{{ cat.name }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Toolbar: Select All, Sorting, Actions -->
          <div class="bg-white rounded-lg p-4 mb-4 flex items-center justify-between">
            <ul class="flex items-center gap-4">
              <li class="flex items-center">
                <input
                  type="checkbox"
                  v-model="selectAll"
                  class="mr-2"
                />
                <span class="text-sm">全选</span>
              </li>
              <li class="text-sm text-slate-600">已选 <em>{{ selectedItems }}</em> 项</li>
              <li>
                <a href="javascript:;" class="text-sm text-slate-700 hover:text-red-600">一键刊登</a>
              </li>
              <li>
                <a href="javascript:;" class="text-sm text-slate-700 hover:text-red-600">下载数据包</a>
              </li>
              <li>
                <a href="javascript:;" class="text-sm text-slate-700 hover:text-red-600">加入收藏夹</a>
              </li>
            </ul>

            <div class="flex items-center gap-2">
              <span class="text-sm text-slate-600">排序:</span>
              <select
                v-model="sortBy"
                class="px-3 py-2 border border-gray-300 rounded text-sm"
              >
                <option value="newest">最新</option>
                <option value="inventory">库存由大到小</option>
                <option value="price_asc">价格由小到大</option>
                <option value="price_desc">价格由大到小</option>
              </select>
            </div>
          </div>

          <!-- Product Grid -->
          <div class="grid grid-cols-5 gap-4">
            <div
              v-for="p in filteredProducts"
              :key="p.spu"
              class="bg-white rounded-lg overflow-hidden hover:shadow-lg transition"
            >
              <div class="relative aspect-square bg-gray-100 overflow-hidden">
                <img :src="p.mainImage" :alt="p.title" class="w-full h-full object-cover" />
                <div class="absolute top-2 right-2 bg-gray-500 text-white text-xs px-2 py-1 rounded">NEW</div>
                <div class="absolute top-2 left-2 flex gap-1">
                  <input type="checkbox" class="mr-2" />
                </div>
              </div>
              <div class="p-3">
                <h3 class="text-xs text-slate-800 line-clamp-2 mb-1 hover:text-red-600">
                  <a :href="`/item/${p.spu}.html`">{{ p.title }}</a>
                </h3>
                <p class="text-xs text-slate-600 mb-2">SPU：{{ p.spu }}</p>
                <div class="mb-2">
                  <a href="javascript:;" class="text-xs text-red-600 hover:underline">登录后可见</a>
                </div>
                <button class="w-full px-2 py-1 border border-red-600 text-red-600 text-xs rounded hover:bg-red-600 hover:text-white transition">
                  {{ p._warehouses?.[0] || 'US' }}
                </button>
              </div>
            </div>
          </div>

          <!-- Pagination -->
          <div class="bg-white rounded-lg p-4 mt-4 flex items-center justify-between">
            <div class="text-sm text-slate-600">
              共266页，前往第
              <input v-model.number="pageNum" type="text" class="w-12 px-2 py-1 border border-gray-300 rounded" />
              页
              <button
                @click="goToPage"
                class="ml-2 px-3 py-1 border border-gray-300 rounded text-sm hover:bg-gray-100"
              >
                GO
              </button>
            </div>
            <ul class="flex gap-1">
              <li v-for="i in 11" :key="i">
                <a
                  href="javascript:;"
                  :class="[
                    'px-3 py-2 border rounded text-sm',
                    currentPage === i
                      ? 'bg-red-600 text-white border-red-600'
                      : 'border-gray-300 text-slate-700 hover:bg-gray-100'
                  ]"
                >
                  {{ i }}
                </a>
              </li>
              <li>
                <a href="javascript:;" class="px-3 py-2 border border-gray-300 rounded text-sm hover:bg-gray-100">下一页</a>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <SiteFooter />
    <RightFloatNav />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import SiteHeader from '@/components/SiteHeader.vue'
import SiteFooter from '@/components/SiteFooter.vue'
import RightFloatNav from '@/components/RightFloatNav.vue'
import NewProductBanner from '@/components/NewProductBanner.vue'
import { products as prodList } from '@/data/products'

// Images and titles scraped from saleyee.com/new.html (referenced directly)
const scraped = [
  { title: "花纹玻璃面 浅黄色藤条 44*5*44.5*57.5cm 编藤边桌", image: "https://img-accelerate.saleyee.cn/Resources/GoodsImages/2025/202509/f4846586-7ddf-42a8-9356-0cc2587e3823.Jpeg" },
  { title: "30.7inch 1pc桌子+2pcs凳子 红蓝绿拼色 儿童庭院桌椅套装", image: "https://img-accelerate.saleyee.cn/Resources/GoodsImages/2025/202509/bec1aa42-2fe8-46f9-bc06-6426b3f5a37e.Jpeg" },
  { title: "DIY 火焰 36in 双-双 拾音器 桐木琴体 电吉他 白色", image: "https://img-accelerate.saleyee.cn/upload/product/202509/d8ecf43d-9f7f-4b6a-8b5a-b21c3795da8f.png" },
  { title: "DIY ST 39in 单-单-单拾音器 桐木琴体 电吉他 白色", image: "https://img-accelerate.saleyee.cn/upload/product/202509/6ecaa79e-95d1-451f-ad53-96f4621d592e.png" },
  { title: "40*30*52.5cm 矩形 双层桌板 圆棒腿  柚木色 庭院木边桌 杉木", image: "https://img-accelerate.saleyee.cn/Resources/GoodsImages/2025/202508/7628df6c-4a10-4aef-a066-a7f82dca173c.Jpeg" },
  { title: "14inx10in 3鼓 杨木 ��蓝 架子鼓 童", image: "https://img-accelerate.saleyee.cn/Resources/GoodsImages/2025/202509/10952bdd-7a8d-4199-804e-5125ce51b937.Jpeg" },
  { title: "41*41*53.5cm  方形 黑色 燃气收纳 庭院铁边桌", image: "https://img-accelerate.saleyee.cn/Resources/GoodsImages/2025/202508/3951a094-aa81-4068-8af0-0fac44785e1f.Jpeg" },
  { title: "荷叶边格纹浴帘带椰子纽扣", image: "https://img-accelerate.saleyee.cn/Resources/GoodsImages/2025/202508/edcf7cd4-b2af-4978-88b8-06f569d1d8da.Jpeg" },
  { title: "橡胶 黑色 60*90*1cm 橡胶地垫【同款编码：31236019】", image: "https://img-accelerate.saleyee.cn/Resources/GoodsImages/2025/202508/357d9294-892b-4f51-bc94-5cb9db4f880c.Jpeg" },
  { title: "2ft 40cm宽 黑色 户外折叠��� 铝合金 长方形 50kg 两折桌面 带黑色网兜 3个高度调节", image: "https://img-accelerate.saleyee.cn/Resources/GoodsImages/2025/202508/027c8a4b-f6f6-4f6b-8368-d7deee320b69.Jpeg" },
  { title: "3ft 40cm宽 白鹅卵石色 户外折叠桌 铝合金 长方形 50kg 三折桌面 带黑色网兜 3个高度调节", image: "https://img-accelerate.saleyee.cn/Resources/GoodsImages/2025/202508/91617457-b6d7-4e55-bbe3-e47c514bd7aa.Jpeg" },
  { title: "5层双排   可自由调节层间距 展示架 铁 147*35*180cm 喷粉黑 N001", image: "https://img-accelerate.saleyee.cn/Resources/GoodsImages/2025/202507/fc85338e-4aec-4577-8bf7-908eeebfd780.Jpeg" },
]

const demo = Array.from({ length: 100 }).map((_, i) => {
  const src = scraped[i % scraped.length]
  const base = prodList[i % prodList.length]
  return {
    ...base,
    title: src.title || base.title,
    mainImage: src.image,
    _price: Number((20 + (i % 15) * 1.5).toFixed(2)),
    _originalPrice: Number((25 + (i % 15) * 1.8).toFixed(2)),
    _warehouses: i % 3 === 0 ? ['US','UK'] : ['US'],
    category: i % 5,
  }
})

const allNew = demo

// Categories
const categories = ['家居和家具', '庭院和园艺', '户外、娱乐和运动', '消费电子/器材', '宠物用品']
const selectedCategory = ref(0)

// Weekly featured carousel state
const currentWeeklyIdx = ref(0)
const weeklyFeatured = computed(() => allNew.slice(0, 7))

function nextWeekly() {
  if (weeklyFeatured.value.length > 0) {
    currentWeeklyIdx.value = (currentWeeklyIdx.value + 1) % weeklyFeatured.value.length
  }
}

function prevWeekly() {
  if (weeklyFeatured.value.length > 0) {
    currentWeeklyIdx.value = (currentWeeklyIdx.value - 1 + weeklyFeatured.value.length) % weeklyFeatured.value.length
  }
}

// Filter by category
const filteredByCategory = computed(() => {
  return allNew.filter((p) => p.category === selectedCategory.value)
})

// Limit hot sales section to 8 items (2 rows @ 4 cols)
const hotSalesTop8 = computed(() => filteredByCategory.value.slice(0, 8))

function formatPrice(n) {
  return `$${n.toFixed(2)}`
}

// ============ Bottom Section: Filter & Product List ============

// Regions filter
const regions = ['US', 'UK', 'DE', 'FR', 'CZ', 'CA']
const selectedRegions = ref([])

function toggleRegion(region) {
  const idx = selectedRegions.value.indexOf(region)
  if (idx > -1) {
    selectedRegions.value.splice(idx, 1)
  } else {
    selectedRegions.value.push(region)
  }
}

// Inventory filter
const inventoryStart = ref(null)
const inventoryEnd = ref(null)

function applyInventoryFilter() {
  // Placeholder for inventory filter logic
  console.log('Inventory filter applied:', inventoryStart.value, inventoryEnd.value)
}

// Price filter
const priceStart = ref(null)
const priceEnd = ref(null)

function applyPriceFilter() {
  // Placeholder for price filter logic
  console.log('Price filter applied:', priceStart.value, priceEnd.value)
}

// New time filter
const newTimes = [
  { label: '全部', value: '' },
  { label: '近7天', value: '7' },
  { label: '近15天', value: '15' },
  { label: '近30天', value: '30' },
  { label: '近60天', value: '60' },
]
const selectedNewTime = ref('')

// Shipment type filter
const shipmentTypes = [
  { label: '全部', value: '' },
  { label: '平台发货', value: '0' },
  { label: '供应商自发货', value: '1' },
]
const selectedShipmentType = ref('')

// Sale mode filter
const saleModes = [
  { label: '全部', value: '' },
  { label: '一件代发', value: '0' },
  { label: '批发', value: '1' },
  { label: '圈货', value: '2' },
  { label: '自提', value: '3' },
]
const selectedSaleMode = ref('')

// Category carousel
const categoryCarousel = [
  { name: '家居和家具', image: 'https://resource.saleyee.cn/Content/Images/asset/%E5%95%86%E5%9F%8E%E9%A6%96%E9%A1%B5/%E5%AE%B6%E5%B1%85%E5%92%8C%E5%AE%B6%E5%85%B7.jpg?v=1524421279' },
  { name: '庭院��园艺', image: 'https://resource.saleyee.cn/Content/Images/asset/%E4%B8%80%E7%BA%A7%E5%88%86%E7%B1%BB%E5%9B%BE%E6%A0%872024/%E5%BA%AD%E9%99%A2%E5%92%8C%E5%9B%AD%E8%89%BA.jpg?v=232566885' },
  { name: '音乐艺术', image: 'https://resource.saleyee.cn/Content/Images/asset/%E4%B8%80%E7%BA%A7%E5%88%86%E7%B1%BB%E5%9B%BE%E6%A0%872024/%E9%9F%B3%E4%B9%90%E8%89%BA%E6%9C%AF.jpg?v=232566885' },
  { name: '户外、娱乐和运动', image: 'https://resource.saleyee.cn/Content/Images/asset/%E4%B8%80%E7%BA%A7%E5%88%86%E7%B1%BB%E5%9B%BE%E6%A0%872024/%E6%88%B7%E5%A4%96.%E5%A8%B1%E4%B9%90%E5%92%8C%E8%BF%90%E5%8A%A8.jpg?v=232566885' },
  { name: '电器类', image: 'https://resource.saleyee.cn/Content/Images/asset/%E4%B8%80%E7%BA%A7%E5%88%86%E7%B1%BB%E5%9B%BE%E6%A0%872024/%E7%94%B5%E5%99%A8%E7%B1%BB.jpg?v=232566885' },
  { name: '工商业设备', image: 'https://resource.saleyee.cn/Content/Images/asset/%E4%B8%80%E7%BA%A7%E5%88%86%E7%B1%BB%E5%9B%BE%E6%A0%872024/%E5%B7%A5%E5%95%86%E4%B8%9A%E8%AE%BE%E5%A4%87.jpg?v=232566885' },
  { name: '健康��美容', image: 'https://resource.saleyee.cn/Content/Images/asset/%E4%B8%80%E7%BA%A7%E5%88%86%E7%B1%BB%E5%9B%BE%E6%A0%872024/%E5%81%A5%E5%BA%B7%E5%92%8C%E7%BE%8E%E5%AE%B9.jpg?v=232566885' },
  { name: '办公、教育与安全', image: 'https://resource.saleyee.cn/Content/Images/asset/%E4%B8%80%E7%BA%A7%E5%88%86%E7%B1%BB%E5%9B%BE%E6%A0%872024/%E5%8A%9E%E5%85%AC%E6%95%99%E8%82%B2%E4%B8%8E%E5%AE%89%E5%85%A8.jpg?v=232566885' },
  { name: '宠物用品', image: 'https://resource.saleyee.cn/Content/Images/asset/%E4%B8%80%E7%BA%A7%E5%88%86%E7%B1%BB%E5%9B%BE%E6%A0%872024/%E5%AE%A0%E7%89%A9%E7%94%A8%E5%93%81.jpg?v=232566885' },
  { name: '玩具/婴童用品', image: 'https://resource.saleyee.cn/Content/Images/asset/%E4%B8%80%E7%BA%A7%E5%88%86%E7%B1%BB%E5%9B%BE%E6%A0%872024/%E7%8E%A9%E5%85%B7%E5%A9%B4%E7%AB%A5%E7%94%A8%E5%93%81.JPG?v=232566885' },
  { name: '汽摩配件', image: 'https://resource.saleyee.cn/Content/Images/asset/%E4%B8%80%E7%BA%A7%E5%88%86%E7%B1%BB%E5%9B%BE%E6%A0%872024/%E6%B1%BD%E6%91%A9%E9%85%8D%E4%BB%B6.jpg?v=232566885' },
  { name: '服饰箱包', image: 'https://resource.saleyee.cn/Content/Images/asset/%E4%B8%80%E7%BA%A7%E5%88%86%E7%B1%BB%E5%9B%BE%E6%A0%872024/%E6%9C%8D%E9%A5%B0%E7%AE%B1%E5%8C%85.jpg?v=232566885' },
  { name: '消费电子/器材', image: 'https://resource.saleyee.cn/Content/Images/asset/%E4%B8%80%E7%BA%A7%E5%88%86%E7%B1%BB%E5%9B%BE%E6%A0%872024/%E6%B6%88%E8%B4%B9%E7%94%B5%E5%AD%90%E5%99%A8%E6%9D%90.jpg?v=232566885' },
]

// Select all and sorting
const selectAll = ref(false)
const selectedItems = ref(0)
const sortBy = ref('newest')
const currentPage = ref(1)
const pageNum = ref(null)

function goToPage() {
  if (pageNum.value && pageNum.value > 0 && pageNum.value <= 266) {
    currentPage.value = pageNum.value
  }
}

// Filtered products based on all filters
const filteredProducts = computed(() => {
  let result = allNew

  // Apply price filter if specified
  if (priceStart.value !== null || priceEnd.value !== null) {
    result = result.filter((p) => {
      if (priceStart.value !== null && p._price < priceStart.value) return false
      if (priceEnd.value !== null && p._price > priceEnd.value) return false
      return true
    })
  }

  // Apply sorting
  if (sortBy.value === 'inventory') {
    // Placeholder: would sort by inventory
  } else if (sortBy.value === 'price_asc') {
    result = [...result].sort((a, b) => a._price - b._price)
  } else if (sortBy.value === 'price_desc') {
    result = [...result].sort((a, b) => b._price - a._price)
  } else if (sortBy.value === 'newest') {
    // Keep original order (newest first)
  }

  // Paginate: 20 items per page
  const itemsPerPage = 20
  const startIdx = (currentPage.value - 1) * itemsPerPage
  return result.slice(startIdx, startIdx + itemsPerPage)
})
</script>

<style scoped>
:root {
  --primary: #CB261C;
}
.bg-primary { background-color: var(--primary); }
.text-primary { color: var(--primary); }

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.5s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
