<script setup>
import { ref } from 'vue'

const products = ref([
  { id: 1, name: '畅销商品 1', image: 'https://via.placeholder.com/200x200?text=Best+1', price: 99.99, sales: 5000 },
  { id: 2, name: '畅销商品 2', image: 'https://via.placeholder.com/200x200?text=Best+2', price: 149.99, sales: 4500 },
  { id: 3, name: '畅销商品 3', image: 'https://via.placeholder.com/200x200?text=Best+3', price: 199.99, sales: 4000 },
  { id: 4, name: '畅销商品 4', image: 'https://via.placeholder.com/200x200?text=Best+4', price: 249.99, sales: 3500 },
])

const handleProductClick = (productId) => {
  window.location.href = `/shop/product/${productId}`
}
</script>

<template>
  <div class="bg-white rounded-lg p-6 my-8">
    <h2 class="text-2xl font-bold text-slate-900 mb-6">畅销商品</h2>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <div
        v-for="product in products"
        :key="product.id"
        @click="handleProductClick(product.id)"
        class="product-card cursor-pointer"
      >
        <img :src="product.image" :alt="product.name" class="product-image" />
        <div class="p-3">
          <h3 class="product-title">{{ product.name }}</h3>
          <p class="product-price mt-2">¥{{ product.price.toFixed(2) }}</p>
          <p class="text-xs text-slate-500 mt-1">销量: {{ product.sales }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* 组件特定样式 */
</style>
