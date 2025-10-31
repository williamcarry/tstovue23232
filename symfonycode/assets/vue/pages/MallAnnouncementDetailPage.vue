<script setup>
import { ref } from 'vue'

const props = defineProps({
  announcement: {
    type: [Object, String],
    default: () => ({})
  },
  announcementId: {
    type: Number,
    default: 0
  },
  relatedAnnouncements: {
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

const announcement = ref(parseData(props.announcement))
const relatedAnnouncements = ref(parseData(props.relatedAnnouncements))

const handleRelatedClick = (announcementId) => {
  window.location.href = `/help/announcement/${announcementId}`
}

const handleBackClick = () => {
  window.location.href = '/help/announcements'
}
</script>

<template>
  <div class="min-h-screen bg-slate-50">
    <div class="mx-auto w-full max-w-[1500px] md:w-[80%] md:min-w-[1150px] px-4 md:px-0 py-6">
      <button @click="handleBackClick" class="text-primary hover:text-primary-dark mb-4">
        ← 返回公告列表
      </button>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- 主要内容 -->
        <div class="md:col-span-2">
          <div class="bg-white rounded-lg p-8">
            <!-- 标题 -->
            <h1 class="text-3xl font-bold text-slate-900 mb-4">{{ announcement.title }}</h1>

            <!-- 元信息 -->
            <div class="flex items-center gap-4 text-sm text-slate-500 pb-4 border-b border-slate-200">
              <span>{{ announcement.createdAt }}</span>
              <span :class="['px-2 py-1 rounded', announcement.importance === 'high' ? 'bg-red-100 text-red-700' : 'bg-slate-100 text-slate-700']">
                {{ announcement.type }}
              </span>
            </div>

            <!-- 内容 -->
            <div class="prose prose-sm max-w-none py-6">
              <p>{{ announcement.content }}</p>
            </div>
          </div>
        </div>

        <!-- 侧边栏 -->
        <div class="md:col-span-1">
          <!-- 相关公告 -->
          <div class="bg-white rounded-lg p-4">
            <h3 class="font-bold text-slate-900 mb-4">相关公告</h3>
            <div class="space-y-3">
              <button
                v-for="item in relatedAnnouncements"
                :key="item.id"
                @click="handleRelatedClick(item.id)"
                class="w-full text-left p-3 rounded border border-slate-200 hover:border-primary hover:bg-slate-50 transition text-sm"
              >
                <p class="font-medium text-slate-900 line-clamp-2">{{ item.title }}</p>
              </button>
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
