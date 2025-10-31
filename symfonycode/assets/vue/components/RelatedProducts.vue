<script setup>
import { defineProps } from 'vue'

const props = defineProps({
  products: {
    type: Array,
    default: () => []
  }
})

const handleProductClick = (productId) => {
  window.location.href = `/shop/product/${productId}`
}
</script>

<template>
  <div class="bg-white rounded-lg p-6">
    <h3 class="text-lg font-bold text-slate-900 mb-4">相关商品</h3>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <div
        v-for="product in products"
        :key="product.id"
        @click="handleProductClick(product.id)"
        class="product-card cursor-pointer"
      >
        <img :src="product.image" :alt="product.name" class="product-image" />
        <div class="p-3">
          <h4 class="product-title">{{ product.name }}</h4>
          <p class="product-price mt-2">¥{{ product.price.toFixed(2) }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* 组件特定样式 */
</style>
