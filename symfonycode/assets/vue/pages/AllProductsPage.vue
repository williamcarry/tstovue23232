<script setup>
import { ref, computed, onMounted, watch } from 'vue'

const props = defineProps({
  products: {
    type: [Array, String],
    default: () => []
  },
  filters: {
    type: [Object, String],
    default: () => ({})
  },
  filterOptions: {
    type: [Object, String],
    default: () => ({})
  }
})

// 解析 props
const parseData = (data) => {
  if (typeof data === 'string') {
    try {
      return JSON.parse(data)
    } catch {
      return data
    }
  }
  return data || {}
}

const products = ref(parseData(props.products))
const filters = ref(parseData(props.filters))
const filterOptions = ref(parseData(props.filterOptions))

const selectedWarehouses = ref(filters.value.warehouse || null)
const selectedNewTime = ref(filters.value.newTime || '')
const selectedShipment = ref(filters.value.shipment || '')
const selectedSaleMode = ref(filters.value.saleMode || '')
const selectedCategory1 = ref(filters.value.category1 || '')
const sortBy = ref(filters.value.sortBy || 'default')
const currentPage = ref(filters.value.page || 1)
const pageSize = ref(filters.value.pageSize || 20)

// 计算属性：过滤后的商品
const filteredProducts = computed(() => {
  let result = [...products.value]

  if (selectedWarehouses.value) {
    result = result.filter(p => p.warehouse === selectedWarehouses.value)
  }
  if (selectedNewTime.value) {
    result = result.filter(p => p.newTime === selectedNewTime.value)
  }
  if (selectedShipment.value) {
    result = result.filter(p => p.shipment === selectedShipment.value)
  }
  if (selectedSaleMode.value) {
    result = result.filter(p => p.saleMode === selectedSaleMode.value)
  }
  if (selectedCategory1.value) {
    result = result.filter(p => p.category1 === selectedCategory1.value)
  }

  // 排序
  if (sortBy.value === 'newest') {
    result.sort((a, b) => new Date(b.createdAt) - new Date(a.createdAt))
  } else if (sortBy.value === 'bestSelling') {
    result.sort((a, b) => b.sales - a.sales)
  } else if (sortBy.value === 'priceAsc') {
    result.sort((a, b) => a.price - b.price)
  } else if (sortBy.value === 'priceDesc') {
    result.sort((a, b) => b.price - a.price)
  }

  return result
})

// 分页
const paginatedProducts = computed(() => {
  const start = (currentPage.value - 1) * pageSize.value
  return filteredProducts.value.slice(start, start + pageSize.value)
})

const totalPages = computed(() => {
  return Math.ceil(filteredProducts.value.length / pageSize.value)
})

// 方法
const resetFilters = () => {
  selectedWarehouses.value = null
  selectedNewTime.value = ''
  selectedShipment.value = ''
  selectedSaleMode.value = ''
  selectedCategory1.value = ''
  currentPage.value = 1
}

const handleProductClick = (productId) => {
  window.location.href = `/shop/product/${productId}`
}

onMounted(() => {
  // 初始化逻辑
})
</script>

<template>
  <div class="min-h-screen bg-slate-50">
    <div class="mx-auto w-full max-w-[1500px] md:w-[80%] md:min-w-[1150px] px-4 md:px-0 py-6">
      <!-- 筛选栏 -->
      <div class="bg-white shadow-sm rounded-lg mb-6 p-5">
        <!-- 区域筛选 -->
        <div class="flex flex-wrap gap-3 mb-4">
          <span class="font-bold text-sm text-primary px-2 py-1">仓库：</span>
          <button
            @click="selectedWarehouses = null"
            :class="[
              'px-3 py-1 rounded text-xs transition',
              !selectedWarehouses ? 'bg-primary text-white' : 'bg-slate-100 text-slate-700 hover:text-primary'
            ]"
          >
            全部
          </button>
          <button
            v-for="warehouse in filterOptions.warehouses || []"
            :key="warehouse.id"
            @click="selectedWarehouses = selectedWarehouses === warehouse.id ? null : warehouse.id"
            :class="[
              'px-3 py-1 rounded text-xs transition',
              selectedWarehouses === warehouse.id ? 'bg-primary text-white' : 'bg-slate-100 text-slate-700 hover:text-primary'
            ]"
          >
            {{ warehouse.name }}
          </button>
        </div>

        <!-- 排序 -->
        <div class="flex flex-wrap gap-3 items-center">
          <span class="font-bold text-sm text-primary px-2 py-1">排序：</span>
          <select v-model="sortBy" class="px-3 py-1 rounded text-xs border border-slate-300">
            <option value="default">默认</option>
            <option value="newest">最新</option>
            <option value="bestSelling">畅销</option>
            <option value="priceAsc">价格升序</option>
            <option value="priceDesc">价格降序</option>
          </select>

          <button
            @click="resetFilters"
            class="ml-auto px-3 py-1 rounded text-xs bg-slate-100 text-slate-700 hover:text-primary"
          >
            重置
          </button>
        </div>
      </div>

      <!-- 商品列表 -->
      <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
        <div
          v-for="product in paginatedProducts"
          :key="product.id"
          @click="handleProductClick(product.id)"
          class="product-card cursor-pointer"
        >
          <img :src="product.image" :alt="product.name" class="product-image" />
          <div class="p-3">
            <h3 class="product-title">{{ product.name }}</h3>
            <div class="flex items-center mt-2">
              <span class="product-price">¥{{ product.price.toFixed(2) }}</span>
              <span class="product-original-price">¥{{ product.originalPrice.toFixed(2) }}</span>
            </div>
            <div class="text-xs text-slate-500 mt-1">
              销量: {{ product.sales }} | 评分: {{ product.rating }}
            </div>
          </div>
        </div>
      </div>

      <!-- 空状态 -->
      <div v-if="paginatedProducts.length === 0" class="text-center py-12">
        <p class="text-slate-500 text-lg">暂无商品</p>
      </div>

      <!-- 分页 -->
      <div v-if="totalPages > 1" class="flex justify-center items-center mt-8 gap-2">
        <button
          @click="currentPage = Math.max(1, currentPage - 1)"
          :disabled="currentPage === 1"
          class="px-3 py-1 rounded text-sm border border-slate-300 hover:text-primary disabled:opacity-50"
        >
          上一页
        </button>
        <span class="text-sm text-slate-600">
          {{ currentPage }} / {{ totalPages }}
        </span>
        <button
          @click="currentPage = Math.min(totalPages, currentPage + 1)"
          :disabled="currentPage === totalPages"
          class="px-3 py-1 rounded text-sm border border-slate-300 hover:text-primary disabled:opacity-50"
        >
          下一页
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* 组件特定样式 */
</style>
