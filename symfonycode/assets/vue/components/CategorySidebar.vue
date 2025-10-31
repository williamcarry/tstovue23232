<script setup>
import { ref } from 'vue'

const categories = ref([
  { id: 'electronics', name: '电子产品', count: 1250 },
  { id: 'home', name: '家居用品', count: 856 },
  { id: 'apparel', name: '服装配饰', count: 3421 },
  { id: 'sports', name: '运动户外', count: 234 },
  { id: 'beauty', name: '美妆护肤', count: 567 },
])

const handleCategoryClick = (categoryId) => {
  window.location.href = `/shop/all-products?category1=${categoryId}`
}
</script>

<template>
  <div class="bg-white rounded-lg p-4">
    <h3 class="font-bold text-slate-900 mb-4">商品分类</h3>
    <div class="space-y-2">
      <button
        v-for="category in categories"
        :key="category.id"
        @click="handleCategoryClick(category.id)"
        class="w-full text-left px-3 py-2 rounded hover:bg-slate-50 hover:text-primary transition flex items-center justify-between"
      >
        <span>{{ category.name }}</span>
        <span class="text-xs text-slate-500">({{ category.count }})</span>
      </button>
    </div>
  </div>
</template>

<style scoped>
/* 组件特定样式 */
</style>
