<script setup>
import { ref } from 'vue'

const categories = ref([
  { id: 'electronics', name: '电子产品', icon: '📱' },
  { id: 'home', name: '家居用品', icon: '🏠' },
  { id: 'apparel', name: '服装配饰', icon: '👕' },
  { id: 'sports', name: '运动户外', icon: '⚽' },
  { id: 'beauty', name: '美妆护肤', icon: '💄' },
  { id: 'toys', name: '玩具游戏', icon: '🎮' },
  { id: 'books', name: '图书音像', icon: '📚' },
  { id: 'food', name: '食品饮料', icon: '🍕' },
])

const handleCategoryClick = (categoryId) => {
  window.location.href = `/shop/all-products?category1=${categoryId}`
}

const handleViewAll = () => {
  window.location.href = '/shop/categories'
}
</script>

<template>
  <div class="bg-white border-b border-slate-200">
    <div class="mx-auto w-full max-w-[1500px] md:w-[80%] md:min-w-[1150px] px-4 md:px-0 py-3">
      <div class="flex items-center gap-4 overflow-x-auto scrollbar-hide">
        <button
          v-for="category in categories"
          :key="category.id"
          @click="handleCategoryClick(category.id)"
          class="flex items-center gap-2 px-3 py-2 rounded-lg text-slate-700 hover:text-primary hover:bg-slate-50 transition whitespace-nowrap text-sm font-medium"
        >
          <span>{{ category.icon }}</span>
          <span>{{ category.name }}</span>
        </button>
        <button
          @click="handleViewAll"
          class="ml-auto px-3 py-2 text-slate-600 hover:text-primary text-sm whitespace-nowrap"
        >
          查看全部 →
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* 隐藏滚动条 */
::-webkit-scrollbar {
  height: 4px;
}

::-webkit-scrollbar-thumb {
  background-color: #cbd5e1;
  border-radius: 2px;
}

.scrollbar-hide {
  -ms-overflow-style: none;
  scrollbar-width: none;
}

.scrollbar-hide::-webkit-scrollbar {
  display: none;
}
</style>
