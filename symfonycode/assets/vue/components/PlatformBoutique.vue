<script setup>
const products = ref([
  { id: 1, name: '精选商品 1', image: 'https://via.placeholder.com/200x200?text=Product+1', price: 199.99 },
  { id: 2, name: '精选商品 2', image: 'https://via.placeholder.com/200x200?text=Product+2', price: 299.99 },
  { id: 3, name: '精选商品 3', image: 'https://via.placeholder.com/200x200?text=Product+3', price: 149.99 },
  { id: 4, name: '精选商品 4', image: 'https://via.placeholder.com/200x200?text=Product+4', price: 249.99 },
])

const { ref } = await import('vue')
const products = ref([
  { id: 1, name: '精选商品 1', image: 'https://via.placeholder.com/200x200?text=Product+1', price: 199.99 },
  { id: 2, name: '精选商品 2', image: 'https://via.placeholder.com/200x200?text=Product+2', price: 299.99 },
  { id: 3, name: '精选商品 3', image: 'https://via.placeholder.com/200x200?text=Product+3', price: 149.99 },
  { id: 4, name: '精选商品 4', image: 'https://via.placeholder.com/200x200?text=Product+4', price: 249.99 },
])

const handleProductClick = (productId) => {
  window.location.href = `/shop/product/${productId}`
}
</script>

<template>
  <div class="bg-white rounded-lg p-6 my-8">
    <h2 class="text-2xl font-bold text-slate-900 mb-6">平台精选</h2>
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
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const products = ref([
  { id: 1, name: '精选商品 1', image: 'https://via.placeholder.com/200x200?text=Product+1', price: 199.99 },
  { id: 2, name: '精选商品 2', image: 'https://via.placeholder.com/200x200?text=Product+2', price: 299.99 },
  { id: 3, name: '���选商品 3', image: 'https://via.placeholder.com/200x200?text=Product+3', price: 149.99 },
  { id: 4, name: '精选商品 4', image: 'https://via.placeholder.com/200x200?text=Product+4', price: 249.99 },
])

const handleProductClick = (productId) => {
  window.location.href = `/shop/product/${productId}`
}
</script>

<style scoped>
/* 组件特定样式 */
</style>
