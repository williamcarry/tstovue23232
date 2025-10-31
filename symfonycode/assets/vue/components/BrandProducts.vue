<script setup>
import { ref } from 'vue'

const products = ref([
  { id: 1, name: '品牌商品 1', image: 'https://via.placeholder.com/200x200?text=Brand+1', price: 299.99, brand: 'Brand A' },
  { id: 2, name: '品牌商品 2', image: 'https://via.placeholder.com/200x200?text=Brand+2', price: 349.99, brand: 'Brand B' },
  { id: 3, name: '品牌商品 3', image: 'https://via.placeholder.com/200x200?text=Brand+3', price: 399.99, brand: 'Brand C' },
  { id: 4, name: '品牌商品 4', image: 'https://via.placeholder.com/200x200?text=Brand+4', price: 449.99, brand: 'Brand D' },
])

const handleProductClick = (productId) => {
  window.location.href = `/shop/product/${productId}`
}
</script>

<template>
  <div class="bg-white rounded-lg p-6 my-8">
    <h2 class="text-2xl font-bold text-slate-900 mb-6">品牌精品</h2>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <div
        v-for="product in products"
        :key="product.id"
        @click="handleProductClick(product.id)"
        class="product-card cursor-pointer"
      >
        <img :src="product.image" :alt="product.name" class="product-image" />
        <div class="p-3">
          <p class="text-xs text-primary font-semibold mb-1">{{ product.brand }}</p>
          <h3 class="product-title">{{ product.name }}</h3>
          <p class="product-price mt-2">¥{{ product.price.toFixed(2) }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* 组件特定样式 */
</style>
