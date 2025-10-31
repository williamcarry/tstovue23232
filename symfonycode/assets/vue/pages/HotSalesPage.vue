<script setup>
import { ref } from 'vue'

const props = defineProps({
  products: {
    type: [Array, String],
    default: () => []
  }
})

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

const handleProductClick = (productId) => {
  window.location.href = `/shop/product/${productId}`
}
</script>

<template>
  <div class="min-h-screen bg-slate-50">
    <div class="mx-auto w-full max-w-[1500px] md:w-[80%] md:min-w-[1150px] px-4 md:px-0 py-6">
      <h1 class="text-3xl font-bold text-slate-900 mb-8">热销商品</h1>
      
      <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4">
        <div
          v-for="product in products"
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
              销量: {{ product.sales }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* 组件特定样式 */
</style>
