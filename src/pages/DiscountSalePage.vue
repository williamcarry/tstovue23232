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
  <div class="min-h-screen" style="background-color: #f2f3f7">
    <SiteHeader />

    <!-- Hero -->
    <div
      class="w-full py-8"
      style="
        background-image: linear-gradient(135deg, #ff8a5b 0%, #ff6e6e 100%);
      "
    >
      <div class="mx-auto w-full max-w-[1500px] md:w-[80%] md:min-w-[1200px] px-4 md:px-0">
        <div class="text-center">
          <h1 class="text-white text-2xl md:text-3xl font-semibold">折扣专区</h1>
          <p class="text-orange-100 mt-2">限时优惠 — 抢购进行中</p>
        </div>
      </div>
    </div>

    <!-- Top embedded white recommendations (countdown + list) -->
    <div class="mx-auto w-full max-w-[1500px] md:w-[80%] md:min-w-[1200px] px-4 md:px-0 -mt-4 mb-6">
      <div class="bg-white rounded-lg shadow-lg p-4">
        <div class="flex gap-6 items-start">
          <!-- Product list grid (image left, details right) -->
          <div class="flex-1">
            <div class="flex items-center justify-between mb-3">
              <h2 class="text-lg font-semibold">今日折扣精选</h2>
              <div class="text-sm text-slate-500">共 {{ recommendedTop.length }} 款</div>
            </div>

            <ul class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <li v-for="p in recommendedTop" :key="p.spu" class="flex bg-[#f6f6f6] rounded-[3px] overflow-hidden p-[10px]">
                <a :href="`/item/${p.spu}.html`" target="_blank" class="w-40 flex-shrink-0">
                  <div class="w-full h-full aspect-square overflow-hidden">
                    <img :src="p.image" :alt="p.title" class="w-full h-full object-cover" />
                  </div>
                </a>
                <div class="flex-1 p-3">
                  <a :href="`/item/${p.spu}.html`" target="_blank" class="block text-sm font-medium text-slate-800 line-clamp-2">{{ p.title }}</a>
                  <div class="mt-2 flex items-center gap-3">
                    <div class="text-red-600 font-semibold">{{ formatPrice(p.price) }}</div>
                    <div class="text-xs text-slate-400 line-through">{{ formatPrice(p.originalPrice) }}</div>
                    <div class="ml-auto text-xs text-slate-500">{{ p.warehouses.join(' / ') }}</div>
                  </div>
                  <p class="text-red-600 text-xs mt-2">登录后可见</p>
                </div>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <!-- Tabbed highlighted section (限量供应 / 新品特惠) -->
    <div class="mx-auto w-full max-w-[1500px] md:w-[80%] md:min-w-[1200px] px-4 md:px-0 mb-6">
      <div class="bg-white rounded-lg shadow-md p-0 overflow-hidden">
        <!-- Tabs -->
        <div class="flex">
          <button @click="activeTab = 'limited'" :class="activeTab==='limited'? 'flex-1 bg-[#fff3f1] text-[#ff654d] font-bold' : 'flex-1 bg-white text-[#ff654d]'" class="px-4 py-6 text-center text-xl">限量供应</button>
          <button @click="activeTab = 'new'" :class="activeTab==='new'? 'flex-1 bg-[#fff3f1] text-[#ff654d] font-bold' : 'flex-1 bg-white text-[#ff654d]'" class="px-4 py-6 text-center text-xl">新品特惠</button>
        </div>

        <!-- Grid below tabs -->
        <div class="p-4">
          <div class="div-3 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
            <div v-for="p in (activeTab==='limited'? limitedSupplyGrid : newDealsGrid)" :key="p.spu" class="bg-[#f6f6f6] rounded-[3px] overflow-hidden relative group">
              <a :href="`/item/${p.spu}.html`" target="_blank" class="block">
                <div class="w-full aspect-square bg-slate-100 overflow-hidden">
                  <img :src="p.image" :alt="p.title" class="w-full h-full object-cover" />
                </div>
              </a>
              <div class="p-3">
                <a :href="`/item/${p.spu}.html`" target="_blank" class="block mb-1 text-sm text-slate-800 line-clamp-2">{{ p.title }}</a>
                <div class="flex items-center justify-between">
                  <div class="text-xs text-red-600 font-semibold">{{ formatPrice(p.price) }}</div>
                  <div class="text-xs text-slate-500">{{ p.warehouses.join(' / ') }}</div>
                </div>
                <p class="text-red-600 text-xs mt-2">登录后可见</p>
              </div>

              <!-- Hover toolbar (hidden until hover) -->
              <div class="absolute left-0 right-0 bottom-0 bg-white/95 p-2 flex items-center justify-between gap-2 opacity-0 group-hover:opacity-100 transition">
                <div class="flex items-center gap-2">
                  <button class="px-2 py-1 bg-red-600 text-white text-xs rounded flex items-center gap-1"><img src="https://www.saleyee.com/ContentNew/Images/2023/202309/collect.png" class="w-4 h-4"/> 收藏</button>
                </div>
                <div class="flex items-center gap-1">
                  <a href="javascript:;" class="text-xs"><img src="https://www.saleyee.com/Content/Images/down2.png" class="w-4 h-4"/></a>
                  <a href="javascript:;" title="一键刊登" class="text-xs"><img src="https://www.saleyee.com/ContentNew/Images/2023/202304/flasting_logo.png" class="w-5 h-5"/></a>
                </div>
              </div>

            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main content with sidebar filters and product grid -->
    <div class="mx-auto w-full max-w-[1500px] md:w-[80%] md:min-w-[1200px] px-4 md:px-0 py-4">
      <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
        <!-- Sidebar -->
        <aside class="lg:col-span-1">
          <div class="bg-white rounded-lg shadow-md p-4 sticky top-20 max-h-[70vh] overflow-y-auto">
            <h4 class="font-semibold mb-3">筛选</h4>

            <div class="mb-4">
              <div class="text-sm font-medium mb-2">仓库</div>
              <div class="flex flex-col gap-2 text-xs">
                <label v-for="w in ['US','UK','DE','FR']" :key="w" class="inline-flex items-center gap-2">
                  <input type="checkbox" :value="w" v-model="selectedWarehouses" />
                  <span>{{ w }}</span>
                </label>
              </div>
            </div>

            <div class="mb-4">
              <div class="text-sm font-medium mb-2">价格区间</div>
              <div class="flex items-center gap-2">
                <input v-model="minPrice" type="number" placeholder="最小" class="w-1/2 px-2 py-1 border rounded text-sm" />
                <input v-model="maxPrice" type="number" placeholder="最大" class="w-1/2 px-2 py-1 border rounded text-sm" />
              </div>
              <button @click="applyPrice" class="mt-3 w-full bg-primary text-white py-2 rounded text-sm">应用</button>
            </div>

            <div class="mb-4">
              <div class="text-sm font-medium mb-2">折扣</div>
              <div class="flex flex-col gap-2 text-xs">
                <label class="inline-flex items-center gap-2"><input type="radio" v-model="discountRange" value="all" /> 全部</label>
                <label class="inline-flex items-center gap-2"><input type="radio" v-model="discountRange" value=">=50" /> 50% 及以上</label>
                <label class="inline-flex items-center gap-2"><input type="radio" v-model="discountRange" value=">=30" /> 30% 及以上</label>
                <label class="inline-flex items-center gap-2"><input type="radio" v-model="discountRange" value=">=10" /> 10% 及以上</label>
              </div>
            </div>

            <div class="text-xs text-slate-500">提示：这是演示筛选，不会调用后端。</div>
          </div>
        </aside>

        <!-- Product grid -->
        <main class="lg:col-span-4">
          <div class="bg-white rounded-lg shadow-md p-4">
            <div class="flex items-center justify-between mb-3">
              <div class="text-sm text-slate-600">显示 {{ totalFiltered }} 条结果</div>
              <div class="flex items-center gap-3">
                <label class="text-sm text-slate-600">���序：</label>
                <select v-model="sortBy" class="text-sm border rounded px-2 py-1">
                  <option value="default">默认</option>
                  <option value="price-asc">价格 ����</option>
                  <option value="price-desc">价格 ↓</option>
                  <option value="discount-desc">折扣 ↓</option>
                </select>
              </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
              <div v-for="p in pagedProducts" :key="p.spu" class="bg-[#f6f6f6] rounded-[3px] overflow-hidden hover:shadow transition">
                <a :href="`/item/${p.spu}.html`" class="block relative">
                  <div class="relative w-full aspect-square bg-slate-100 overflow-hidden">
                    <img :src="p.image" :alt="p.title" class="w-full h-full object-cover" />
                    <div class="absolute top-2 left-2 bg-red-600 text-white text-xs px-2 py-0.5 rounded">{{ p.discount }}% OFF</div>
                  </div>
                </a>

                <div class="p-3">
                  <a :href="`/item/${p.spu}.html`" class="block mb-2">
                    <h3 class="text-sm text-slate-800 font-medium line-clamp-2">{{ p.title }}</h3>
                  </a>

                  <div class="flex items-center justify-between">
                    <div>
                      <div class="text-red-600 font-semibold">{{ formatPrice(p.price) }}</div>
                      <div class="text-xs text-slate-400 line-through">{{ formatPrice(p.originalPrice) }}</div>
                    </div>
                    <div class="text-xs text-slate-500">{{ p.warehouses.join(' / ') }}</div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Pagination -->
            <div class="mt-4 flex items-center justify-center">
              <button class="px-3 py-1 border rounded-l" :disabled="page === 1" @click="page--">上一页</button>
              <button v-for="n in pages" :key="n" @click="page = n" :class="['px-3 py-1 border-t border-b', page===n? 'bg-primary text-white': 'bg-white']">{{ n }}</button>
              <button class="px-3 py-1 border rounded-r" :disabled="page === totalPages" @click="page++">下一页</button>
            </div>

          </div>
        </main>
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

// generate demo products
const baseImages = [
  'https://img-accelerate.saleyee.cn/Resources/GoodsImages/2024/202404/5b1487bd-c161-4a8b-9a8b-5b07c7199fd2.Jpeg',
  'https://img-accelerate.saleyee.cn/Resources/GoodsImages/2021/202102/43f9078f-e24d-47c7-8f53-a743e042bc5f.Jpeg',
  'https://img-accelerate.saleyee.cn/Resources/GoodsImages/2022/202209/5bc737f9-f9fa-43eb-af41-2e2f8ad91065.Jpeg',
  'https://img-accelerate.saleyee.cn/Resources/GoodsImages/2024/202405/c103f36e-c3d1-4716-bd72-28327d48a024.Jpeg',
  'https://img-accelerate.saleyee.cn/Resources/GoodsImages/2024/202401/bca2859e-1cf0-4ce1-9879-5b9aaf68109b.Jpeg',
]

const makeProduct = (i) => {
  const img = baseImages[i % baseImages.length]
  const original = Math.round((20 + (i % 15)) * 10) / 10
  const discount = [10, 20, 30, 40, 50][i % 5]
  const price = Math.round((original * (1 - discount / 100)) * 100) / 100
  return {
    spu: `disc-${1000 + i}`,
    title: `折扣商品��例 ${i + 1}`,
    image: img,
    price,
    originalPrice: original,
    discount,
    warehouses: i % 3 === 0 ? ['US','UK'] : ['US'],
  }
}

const totalDemo = 40
const allProducts = Array.from({ length: totalDemo }).map((_, i) => makeProduct(i))

// featured sections
const recommendedTop = allProducts.slice(0, 12)
const limitedSupply = allProducts.slice(12, 18)
const newDeals = allProducts.slice(18, 24)

// expand grid lists for the tabbed block (duplicate to fill grid)
const limitedSupplyGrid = Array.from({length: 12}).map((_, i) => limitedSupply[i % limitedSupply.length])
const newDealsGrid = Array.from({length: 12}).map((_, i) => newDeals[i % newDeals.length])

const activeTab = ref('limited')

// filters & pagination
const selectedWarehouses = ref([])
const minPrice = ref(null)
const maxPrice = ref(null)
const discountRange = ref('all')
const sortBy = ref('default')
const page = ref(1)
const pageSize = ref(12)

function applyPrice() {
  page.value = 1
}

const filtered = computed(() => {
  let out = allProducts.slice()
  if (selectedWarehouses.value.length) {
    out = out.filter(p => p.warehouses.some(w => selectedWarehouses.value.includes(w)))
  }
  if (minPrice.value != null) out = out.filter(p => p.price >= minPrice.value)
  if (maxPrice.value != null) out = out.filter(p => p.price <= maxPrice.value)
  if (discountRange.value !== 'all') {
    const num = parseInt(discountRange.value.replace('>=',''))
    if (!isNaN(num)) out = out.filter(p => p.discount >= num)
  }

  if (sortBy.value === 'price-asc') out.sort((a,b)=>a.price-b.price)
  if (sortBy.value === 'price-desc') out.sort((a,b)=>b.price-a.price)
  if (sortBy.value === 'discount-desc') out.sort((a,b)=>b.discount-b.discount)

  return out
})

const totalFiltered = computed(() => filtered.value.length)
const totalPages = computed(() => Math.max(1, Math.ceil(totalFiltered.value / pageSize.value)))

const pagedProducts = computed(() => {
  const p = Math.min(Math.max(1, page.value), totalPages.value)
  const start = (p - 1) * pageSize.value
  return filtered.value.slice(start, start + pageSize.value)
})

const pages = computed(() => {
  const arr = []
  for (let i = 1; i <= totalPages.value; i++) arr.push(i)
  return arr
})

function formatPrice(n) {
  return `$${n.toFixed(2)}`
}

</script>

<style scoped>
/* small helper to override primary color used above */
:root {
  --primary: #CB261C;
}
.bg-primary { background-color: var(--primary); }
.text-primary { color: var(--primary); }
</style>
