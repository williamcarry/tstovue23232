<script setup>
import { ref } from 'vue'

const props = defineProps({
  faq: {
    type: [Object, String],
    default: () => ({})
  },
  faqId: {
    type: Number,
    default: 0
  },
  relatedFaqs: {
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

const faq = ref(parseData(props.faq))
const relatedFaqs = ref(parseData(props.relatedFaqs))
const isHelpful = ref(null)

const handleHelpful = (helpful) => {
  isHelpful.value = helpful
  // 保存反馈到后端
}

const handleRelatedClick = (faqId) => {
  window.location.href = `/help/faq/${faqId}`
}

const handleBackClick = () => {
  window.location.href = '/help/'
}
</script>

<template>
  <div class="min-h-screen bg-slate-50">
    <div class="mx-auto w-full max-w-[1500px] md:w-[80%] md:min-w-[1150px] px-4 md:px-0 py-6">
      <button @click="handleBackClick" class="text-primary hover:text-primary-dark mb-4">
        ← 返回帮助中心
      </button>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- 主要内容 -->
        <div class="md:col-span-2">
          <div class="bg-white rounded-lg p-8">
            <!-- 标题 -->
            <h1 class="text-3xl font-bold text-slate-900 mb-4">{{ faq.title }}</h1>

            <!-- 元信息 -->
            <div class="flex items-center gap-4 text-sm text-slate-500 pb-4 border-b border-slate-200">
              <span>分类: {{ faq.category }}</span>
              <span>浏览: {{ faq.views }}</span>
              <span>发布: {{ faq.createdAt }}</span>
            </div>

            <!-- 内容 -->
            <div class="prose prose-sm max-w-none py-6">
              <p>{{ faq.content }}</p>
            </div>

            <!-- 有帮助吗？ -->
            <div class="border-t border-slate-200 pt-6">
              <p class="text-sm font-semibold text-slate-900 mb-3">此内容对您有帮助吗？</p>
              <div class="flex gap-2">
                <button
                  @click="handleHelpful(true)"
                  :class="[
                    'px-4 py-2 rounded text-sm font-medium transition',
                    isHelpful === true
                      ? 'bg-primary text-white'
                      : 'bg-slate-100 text-slate-600 hover:text-primary'
                  ]"
                >
                  👍 有帮助 ({{ faq.helpful }})
                </button>
                <button
                  @click="handleHelpful(false)"
                  :class="[
                    'px-4 py-2 rounded text-sm font-medium transition',
                    isHelpful === false
                      ? 'bg-primary text-white'
                      : 'bg-slate-100 text-slate-600 hover:text-primary'
                  ]"
                >
                  👎 无帮助
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- 侧边栏 -->
        <div class="md:col-span-1">
          <!-- 相关FAQ -->
          <div class="bg-white rounded-lg p-4">
            <h3 class="font-bold text-slate-900 mb-4">相关问题</h3>
            <div class="space-y-3">
              <button
                v-for="item in relatedFaqs"
                :key="item.id"
                @click="handleRelatedClick(item.id)"
                class="w-full text-left p-3 rounded border border-slate-200 hover:border-primary hover:bg-slate-50 transition text-sm"
              >
                <p class="font-medium text-slate-900 line-clamp-2">{{ item.title }}</p>
              </button>
            </div>
          </div>

          <!-- 需要帮助 -->
          <div class="bg-primary/10 rounded-lg p-4 mt-4">
            <h3 class="font-bold text-slate-900 mb-2">仍需帮助？</h3>
            <p class="text-sm text-slate-600 mb-4">联系我们的客服团队获取支持。</p>
            <a href="/help/contact" class="text-primary font-medium hover:text-primary-dark">
              → 联系我们
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* 组件特定样式 */
</style>
