<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import SiteHeader from './components/SiteHeader.vue'
import CategoryNavBar from './components/CategoryNavBar.vue'
import SiteFooter from './components/SiteFooter.vue'
import RightFloatNav from './components/RightFloatNav.vue'

// 接收来自 Symfony 的初始数据
const initialState = window.__INITIAL_STATE__ || {}

// 页面内容通过插槽注入
defineProps({
  pageContent: {
    type: Object,
    default: null
  }
})

const heroHeight = ref(undefined)

onMounted(() => {
  // 动态调整高度或其他初始化逻辑
  window.addEventListener('resize', handleResize)
})

onUnmounted(() => {
  window.removeEventListener('resize', handleResize)
})

const handleResize = () => {
  // 处理窗口大小变化
}
</script>

<template>
  <div class="min-h-screen flex flex-col bg-slate-50">
    <!-- 顶部导航 -->
    <SiteHeader />
    
    <!-- 分类导航 -->
    <CategoryNavBar />
    
    <!-- 主内容区 -->
    <main class="flex-grow">
      <!-- Symfony 模板会在这里注入页面内容 -->
      <slot>
        <div class="text-center py-12">
          <p class="text-slate-500">加载中...</p>
        </div>
      </slot>
    </main>
    
    <!-- 页脚 -->
    <SiteFooter />
    
    <!-- 右侧浮动导航 -->
    <RightFloatNav />
  </div>
</template>

<style scoped>
/* 样式已通过 TailwindCSS 全局应用 */
</style>
