<script setup>
import { ref } from 'vue'

const props = defineProps({
  announcements: {
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

const announcements = ref(parseData(props.announcements))

const handleAnnouncementClick = (announcementId) => {
  window.location.href = `/help/announcement/${announcementId}`
}
</script>

<template>
  <div class="min-h-screen bg-slate-50">
    <div class="mx-auto w-full max-w-[1500px] md:w-[80%] md:min-w-[1150px] px-4 md:px-0 py-6">
      <h1 class="text-3xl font-bold text-slate-900 mb-8">平台公告</h1>
      
      <div class="space-y-3">
        <div
          v-for="announcement in announcements"
          :key="announcement.id"
          @click="handleAnnouncementClick(announcement.id)"
          class="bg-white rounded-lg p-4 cursor-pointer transition hover:shadow-md hover:translate-y-[-2px]"
        >
          <div class="flex items-start justify-between">
            <div class="flex-1">
              <h3 class="font-semibold text-slate-900 mb-1">{{ announcement.title }}</h3>
              <p class="text-sm text-slate-600 line-clamp-2">{{ announcement.content }}</p>
              <div class="flex items-center gap-4 mt-2 text-xs text-slate-500">
                <span>{{ announcement.createdAt }}</span>
                <span :class="['px-2 py-1 rounded', announcement.importance === 'high' ? 'bg-red-100 text-red-700' : 'bg-slate-100 text-slate-700']">
                  {{ announcement.type }}
                </span>
              </div>
            </div>
            <svg class="w-5 h-5 text-slate-400 ml-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
            </svg>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* 组件特定样式 */
</style>
