<script setup>
import { ref } from 'vue'

const props = defineProps({
  categories: {
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

const categories = ref(parseData(props.categories))

const handleCategoryClick = (categoryId) => {
  window.location.href = `/shop/all-products?category1=${categoryId}`
}
</script>

<template>
  <div class="min-h-screen bg-slate-50">
    <div class="mx-auto w-full max-w-[1500px] md:w-[80%] md:min-w-[1150px] px-4 md:px-0 py-6">
      <h1 class="text-3xl font-bold text-slate-900 mb-8">全部分类</h1>
      
      <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        <button
          v-for="category in categories"
          :key="category.id"
          @click="handleCategoryClick(category.id)"
          class="bg-white rounded-lg p-6 shadow-sm hover:shadow-md hover:translate-y-[-2px] transition text-center"
        >
          <div class="text-4xl mb-2">{{ category.icon }}</div>
          <h3 class="font-semibold text-slate-900">{{ category.name }}</h3>
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* 组件特定样式 */
</style>
