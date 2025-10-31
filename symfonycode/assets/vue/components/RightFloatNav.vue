<script setup>
import { ref } from 'vue'

const isExpanded = ref(false)

const handleChat = () => {
  alert('聊天功能')
}

const handleService = () => {
  window.location.href = '/help/contact'
}

const handleTop = () => {
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

const toggleMenu = () => {
  isExpanded.value = !isExpanded.value
}
</script>

<template>
  <div class="fixed right-4 bottom-4 z-40 flex flex-col items-end gap-3">
    <!-- 浮动菜单项 -->
    <div v-if="isExpanded" class="flex flex-col gap-2 animate-fadeIn">
      <button
        @click="handleChat"
        class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-dark transition shadow-lg text-sm font-medium whitespace-nowrap"
      >
        💬 在线客服
      </button>
      <button
        @click="handleService"
        class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-dark transition shadow-lg text-sm font-medium whitespace-nowrap"
      >
        📞 联系我们
      </button>
      <button
        @click="handleTop"
        class="bg-slate-700 text-white px-4 py-2 rounded-lg hover:bg-slate-800 transition shadow-lg text-sm font-medium whitespace-nowrap"
      >
        ⬆️ 返回顶部
      </button>
    </div>

    <!-- 主菜单按钮 -->
    <button
      @click="toggleMenu"
      :class="[
        'w-12 h-12 rounded-full bg-primary text-white hover:bg-primary-dark transition shadow-lg flex items-center justify-center font-bold text-lg',
        isExpanded ? 'bg-primary-dark' : ''
      ]"
    >
      {{ isExpanded ? '✕' : '≡' }}
    </button>
  </div>
</template>

<style scoped>
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fadeIn {
  animation: fadeIn 0.3s ease-out;
}
</style>
