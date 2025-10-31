<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  categories: {
    type: [Array, String],
    default: () => []
  },
  faqs: {
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
const faqs = ref(parseData(props.faqs))
const selectedCategory = ref(null)
const searchKeyword = ref('')

const filteredFAQs = computed(() => {
  let result = faqs.value || []

  if (selectedCategory.value) {
    result = result.filter(faq => faq.category === selectedCategory.value)
  }

  if (searchKeyword.value) {
    const keyword = searchKeyword.value.toLowerCase()
    result = result.filter(faq =>
      faq.title.toLowerCase().includes(keyword) ||
      faq.content.toLowerCase().includes(keyword)
    )
  }

  return result
})

const handleFAQClick = (faqId) => {
  window.location.href = `/help/faq/${faqId}`
}

const handleGuideClick = () => {
  window.location.href = '/help/guide'
}

const handleContactClick = () => {
  window.location.href = '/help/contact'
}
</script>

<template>
  <div class="min-h-screen bg-slate-50">
    <div class="mx-auto w-full max-w-[1500px] md:w-[80%] md:min-w-[1150px] px-4 md:px-0 py-6">
      <!-- 搜索栏 -->
      <div class="bg-white rounded-lg p-6 mb-6">
        <h1 class="text-2xl font-bold text-slate-900 mb-4">帮助中心</h1>
        <div class="flex gap-2">
          <input
            v-model="searchKeyword"
            type="text"
            placeholder="搜索问题..."
            class="flex-1 px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:border-primary"
          />
          <button class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark">
            搜索
          </button>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <!-- 左侧：分类 -->
        <div class="md:col-span-1">
          <div class="bg-white rounded-lg p-4">
            <h2 class="font-bold text-slate-900 mb-4">帮助分类</h2>
            <button
              @click="selectedCategory = null"
              :class="[
                'w-full text-left px-3 py-2 rounded mb-2 transition',
                !selectedCategory ? 'bg-primary text-white' : 'hover:bg-slate-50'
              ]"
            >
              全部分类
            </button>
            <button
              v-for="cat in categories"
              :key="cat.id"
              @click="selectedCategory = cat.name"
              :class="[
                'w-full text-left px-3 py-2 rounded mb-2 transition flex items-center justify-between',
                selectedCategory === cat.name ? 'bg-primary text-white' : 'hover:bg-slate-50'
              ]"
            >
              <span>{{ cat.name }}</span>
              <span class="text-xs">{{ cat.count }}</span>
            </button>
          </div>

          <!-- 快速链接 -->
          <div class="bg-white rounded-lg p-4 mt-4">
            <h3 class="font-bold text-slate-900 mb-3">快速链接</h3>
            <button
              @click="handleGuideClick"
              class="w-full text-left px-3 py-2 text-slate-600 hover:text-primary mb-2"
            >
              → 操作指南
            </button>
            <button
              @click="handleContactClick"
              class="w-full text-left px-3 py-2 text-slate-600 hover:text-primary"
            >
              → 联系我们
            </button>
          </div>
        </div>

        <!-- 右侧：FAQ列表 -->
        <div class="md:col-span-3">
          <div class="space-y-3">
            <div
              v-for="faq in filteredFAQs"
              :key="faq.id"
              @click="handleFAQClick(faq.id)"
              class="bg-white rounded-lg p-4 cursor-pointer transition hover:shadow-md hover:translate-y-[-2px]"
            >
              <div class="flex items-start justify-between">
                <div class="flex-1">
                  <h3 class="font-semibold text-slate-900 mb-1">{{ faq.title }}</h3>
                  <p class="text-sm text-slate-600 line-clamp-2">{{ faq.content }}</p>
                  <div class="flex items-center gap-4 mt-2 text-xs text-slate-500">
                    <span>浏览: {{ faq.views }}</span>
                    <span>有帮助: {{ faq.helpful }}</span>
                    <span>{{ faq.createdAt }}</span>
                  </div>
                </div>
                <svg class="w-5 h-5 text-slate-400 ml-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                </svg>
              </div>
            </div>
          </div>

          <!-- 空状态 -->
          <div v-if="filteredFAQs.length === 0" class="text-center py-12 bg-white rounded-lg">
            <p class="text-slate-500 text-lg">暂无相关问题</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* 组件特定样式 */
</style>
