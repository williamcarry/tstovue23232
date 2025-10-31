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

    <div class="w-full" style="background: linear-gradient(90deg, #000000 0%, #3C0A09 100%);">

    <!-- Banner (converted to Tailwind structure matching provided markup) -->
    <div class="w-full">
      <div class="mx-auto w-full max-w-[1500px] md:w-[80%] md:min-w-[1200px] px-4 md:px-0 py-0">
        <div class="relative">
          <div class="bg-white rounded-lg overflow-hidden">
            <div class="flex w-full">
              <div class="w-full flex items-center justify-center">
                <a
                  target="_blank"
                  href="https://www.saleyee.cn/Management/SaleSubject/EditSubjectModule/2649"
                  class="block w-full"
                >
                  <img
                    loading="lazy"
                    :src="bannerImage"
                    alt="平台直发 banner"
                    class="w-full h-64 md:h-40 lg:h-48 object-cover"
                  />
                </a>
              </div>
            </div>
          </div>

          <!-- Pagination dot -->
          <div class="absolute bottom-2 left-0 w-full flex justify-center z-10">
            <button
              tabindex="0"
              role="button"
              aria-label="Go to slide 1"
              class="w-2 h-2 bg-yellow-400 rounded-full mx-1 focus:outline-none"
            ></button>
          </div>

          <!-- Accessible live region -->
          <span aria-live="assertive" aria-atomic="true" class="sr-only"></span>
        </div>
      </div>
    </div>

    <!-- 热卖推荐 (Hot Recommend) -->
    <section class="mx-auto w-full max-w-[1500px] md:w-[80%] md:min-w-[1200px] px-4 md:px-0 py-6">
      <div class="bg-transparent mx-auto w-full max-w-[1500px]">
        <!-- Title -->
        <h3
          class="mx-auto text-center text-[36px] leading-[114px] mb-6"
          :style="{ backgroundImage: `url(https://www.saleyee.com/static/imgs/6610b7ae24959b0bfcbd47615dadf60c.png)`, backgroundRepeat: 'no-repeat', backgroundPosition: 'center', width: '586px', height: '114px', paddingLeft: '90px', paddingRight: '90px', color: '#f2c475' }"
        >
          热卖推荐
        </h3>

        <!-- First row: two featured items -->
        <div class="flex flex-wrap -mx-2 mb-4">
          <div v-for="(p, idx) in hotRecommend.slice(0,2)" :key="p.spu" class="w-full md:w-1/2 px-2 mb-4 md:mb-0">
            <div class="bg-white p-5 flex gap-4 items-start">
              <a :href="`/item/${p.spu}.html`" target="_blank" class="relative block flex-shrink-0 w-1/3">
                <img src="https://www.saleyee.com/ContentNew/Images/2023/202311/right_top_hot.png" alt="badge" class="absolute right-0 top-0 w-14 h-14 object-contain" />
                <img :src="p.mainImage" :alt="p.title" class="w-full h-28 object-cover rounded" />
              </a>
              <div class="flex-1">
                <a :href="`/item/${p.spu}.html`" target="_blank" class="text-sm font-medium text-slate-800 hover:text-primary block line-clamp-2">{{ p.title }}</a>
                <p class="text-xs text-slate-500 mt-1">SKU:{{ p.spu }}</p>
                <div class="mt-2">
                  <button type="button" class="px-2 py-1 border border-red-600 text-red-600 text-xs rounded">{{ p._warehouses?.[0] || 'US' }}</button>
                </div>
                <p class="text-xs text-red-600 mt-2">登录后可见</p>
                <div class="mt-2 flex items-center gap-3">
                  <b class="text-base font-bold text-red-600">{{ formatPrice(p._price) }}</b>
                  <em v-if="p._originalPrice" class="text-sm text-slate-400 line-through">{{ formatPrice(p._originalPrice) }}</em>
                  <span class="ml-2 bg-orange-400 text-white text-xs px-2 py-0.5 rounded">热卖</span>
                </div>
                <div class="mt-3 text-right">
                  <input type="checkbox" :value="p.spu" class="hidden" />
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Second row: three items -->
        <div class="flex flex-wrap -mx-2">
          <div v-for="(p, idx) in hotRecommend.slice(2,5)" :key="p.spu" class="w-full md:w-1/3 px-2 mb-4">
            <div class="bg-white p-4">
              <a :href="`/item/${p.spu}.html`" target="_blank" class="block">
                <div class="aspect-square bg-gray-100 overflow-hidden rounded mb-3">
                  <img :src="p.mainImage" :alt="p.title" class="w-full h-full object-cover" />
                </div>
              </a>
              <a :href="`/item/${p.spu}.html`" target="_blank" class="text-sm font-medium text-slate-800 hover:text-primary line-clamp-2">{{ p.title }}</a>
              <p class="text-xs text-slate-500 mt-1">SKU:{{ p.spu }}</p>
              <div class="mt-2 flex items-center justify-between">
                <p class="text-sm text-red-600 font-semibold">登录后可见</p>
                <button class="px-2 py-1 border border-red-600 text-red-600 text-xs rounded">{{ p._warehouses?.[0] || 'US' }}</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- 平台直发优选 -->
    <section class="mx-auto w-full max-w-[1500px] md:w-[80%] md:min-w-[1200px] px-4 md:px-0 py-6">
      <!-- Title -->
      <h3 class="mx-auto text-center text-[36px] leading-[114px] mb-6" :style="{ backgroundImage: `url(https://www.saleyee.com/static/imgs/6610b7ae24959b0bfcbd47615dadf60c.png)`, backgroundRepeat: 'no-repeat', backgroundPosition: 'center', width: '586px', height: '114px', paddingLeft: '90px', paddingRight: '90px', color: '#f2c475' }">平台直发优选</h3>

      <!-- Category tiles -->
      <div class="bg-white rounded-lg p-4 mb-6">
        <div class="flex flex-wrap gap-3 justify-center">
          <button
            v-for="(cat, i) in categories"
            :key="i"
            type="button"
            @click="selectedCategory = cat"
            :aria-pressed="selectedCategory === cat"
            :class="[
              'px-4 py-3 border rounded shadow-sm text-sm transition',
              selectedCategory === cat ? 'bg-red-50 text-red-600 border-red-600 font-semibold' : 'bg-white text-slate-700 hover:bg-slate-50'
            ]"
          >
            {{ cat }}
          </button>
        </div>
      </div>

      <!-- Product tiles grid (5 columns) -->
      <div class="flex flex-wrap -mx-2">
        <div v-for="p in directSelection" :key="p.spu" class="w-1/2 sm:w-1/3 md:w-1/4 lg:w-1/5 px-2 mb-4">
          <div class="bg-white p-4 relative">
            <a :href="`/item/${p.spu}.html`" target="_blank" class="block mb-3">
              <div class="aspect-[1/1] bg-gray-100 overflow-hidden rounded">
                <img :src="p.mainImage" :alt="p.title" class="w-full h-full object-cover" />
                <span class="absolute top-2 left-2 bg-orange-500 text-white text-xs px-2 py-0.5 rounded">热卖</span>
              </div>
            </a>
            <a :href="`/item/${p.spu}.html`" target="_blank" class="text-sm font-medium text-slate-800 hover:text-primary line-clamp-2">{{ p.title }}</a>
            <h5 class="text-xs text-slate-500 mt-1">SPU:{{ p.spu }}</h5>
            <div class="mt-2 flex items-center justify-between">
              <a href="/login" class="text-xs text-red-600 font-semibold">登录后可见</a>
              <button class="px-2 py-1 border border-red-600 text-red-600 text-xs rounded">{{ p._warehouses?.[0] || 'US' }}</button>
            </div>
            <div class="mt-2 flex items-center justify-between">
              <div>
                <b class="text-sm text-red-600 font-bold">{{ formatPrice(p._price) }}</b>
                <span v-if="p._originalPrice" class="text-xs text-slate-400 line-through ml-2">{{ formatPrice(p._originalPrice) }}</span>
              </div>
              <div>
                <input type="checkbox" :value="p.spu" class="hidden" />
              </div>
            </div>
            <div class="absolute top-2 right-2">
              <button class="text-xs bg-white/80 px-2 py-1 rounded">一键刊登</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Placeholder additional modules (kept hidden) -->
      <div class="hidden">
        <!-- Additional modules can be lazy-loaded here -->
      </div>
    </section>

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
import { products as prodList } from '@/data/products'

const bannerImage = 'https://resource.saleyee.com/UploadFiles/Images/2025/202506/02d92e04-70a0-430c-a0ca-0f86e56abfea.jpg'

// categories used for the platform direct-delivery filter
const categories = ['厨房用品','家居收纳用品&其它','庭院和园艺','电器类&消费电子类','汽摩配件','户外娱乐&运动','健康美容','宠物用品&婴童用品','家居装饰']
const selectedCategory = ref(categories[0])

// demo product list — attach a category to each demo item so the UI can filter
const demo = Array.from({ length: 80 }).map((_, i) => {
  const base = prodList[i % prodList.length]
  return {
    ...base,
    title: base.title,
    mainImage: base.mainImage || 'https://resource.saleyee.com/UploadFiles/Images/placeholder.png',
    _price: Number((18 + (i % 20) * 1.3).toFixed(2)),
    _warehouses: i % 2 === 0 ? ['US'] : ['US','UK'],
    spu: base.spu || `SPU-${i}`,
    category: categories[i % categories.length]
  }
})
const sortBy = ref('newest')
const currentPage = ref(1)
const itemsPerPage = 24

const sorted = computed(() => {
  if (sortBy.value === 'price_asc') return [...demo].sort((a,b)=>a._price-b._price)
  if (sortBy.value === 'price_desc') return [...demo].sort((a,b)=>b._price-a._price)
  return demo
})

const displayedProducts = computed(() => {
  const start = (currentPage.value-1)*itemsPerPage
  return sorted.value.slice(start, start + itemsPerPage)
})

// 热卖推荐数据（取前5条用于布局）
const hotRecommend = computed(() => demo.slice(0, 5))

// 平台直发优选数据（根据所选分类过滤）
const directSelection = computed(() => demo.filter(p => (selectedCategory.value ? p.category === selectedCategory.value : true)))

function formatPrice(n) { return `$${n.toFixed(2)}` }
function prevPage(){ if (currentPage.value>1) currentPage.value-- }
function nextPage(){ if ((currentPage.value*itemsPerPage) < demo.length) currentPage.value++ }
</script>

<style scoped>
:root{ --primary: #CB261C }
.bg-primary{ background-color: var(--primary) }
.text-primary{ color: var(--primary) }
</style>
