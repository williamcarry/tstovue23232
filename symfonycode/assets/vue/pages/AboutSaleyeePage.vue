<script setup>
const aboutInfo = ref({
  companyName: 'Saleyee',
  description: '我们是一个领先的在线交易平台，致力于为全球商人提供高效、安全、便捷的交易服务。',
  founded: '2015年',
  team: '500+专业团队',
  users: '100万+活跃用户',
  mission: '让交易更简单，让生意更好做',
  values: ['诚实', '专业', '创新', '服务'],
})

const { ref } = await import('vue')
const aboutInfo = ref({
  companyName: 'Saleyee',
  description: '我们是一个领先的在线交易平台，致力于为全球商人提供高效、安全、便捷的交易服务。',
  founded: '2015年',
  team: '500+专业团队',
  users: '100万+活跃用户',
  mission: '让交易更简单，让生意更好做',
  values: ['诚实', '专业', '创新', '服务'],
})
</script>

<template>
  <div class="min-h-screen bg-slate-50">
    <!-- 介绍区 -->
    <div class="bg-gradient-to-r from-primary to-primary-light text-white py-12">
      <div class="mx-auto w-full max-w-[1500px] md:w-[80%] md:min-w-[1150px] px-4 md:px-0">
        <h1 class="text-4xl font-bold mb-4">{{ aboutInfo.companyName }}</h1>
        <p class="text-lg max-w-2xl">{{ aboutInfo.description }}</p>
      </div>
    </div>

    <!-- 主要内容 -->
    <div class="mx-auto w-full max-w-[1500px] md:w-[80%] md:min-w-[1150px] px-4 md:px-0 py-12">
      <!-- 数据统计 -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-12">
        <div class="bg-white rounded-lg p-6 text-center">
          <p class="text-3xl font-bold text-primary mb-2">{{ aboutInfo.founded }}</p>
          <p class="text-slate-600">成立时间</p>
        </div>
        <div class="bg-white rounded-lg p-6 text-center">
          <p class="text-3xl font-bold text-primary mb-2">{{ aboutInfo.team }}</p>
          <p class="text-slate-600">团队规模</p>
        </div>
        <div class="bg-white rounded-lg p-6 text-center">
          <p class="text-3xl font-bold text-primary mb-2">{{ aboutInfo.users }}</p>
          <p class="text-slate-600">活跃用户</p>
        </div>
        <div class="bg-white rounded-lg p-6 text-center">
          <p class="text-3xl font-bold text-primary mb-2">24/7</p>
          <p class="text-slate-600">客服支持</p>
        </div>
      </div>

      <!-- 使命和价值观 -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12">
        <div class="bg-white rounded-lg p-8">
          <h2 class="text-2xl font-bold text-slate-900 mb-4">我们的使命</h2>
          <p class="text-slate-600 leading-relaxed">{{ aboutInfo.mission }}</p>
        </div>
        <div class="bg-white rounded-lg p-8">
          <h2 class="text-2xl font-bold text-slate-900 mb-4">核心价值观</h2>
          <div class="grid grid-cols-2 gap-4">
            <div
              v-for="value in aboutInfo.values"
              :key="value"
              class="p-3 bg-primary/10 rounded text-center text-primary font-semibold"
            >
              {{ value }}
            </div>
          </div>
        </div>
      </div>

      <!-- 服务优势 -->
      <div class="bg-white rounded-lg p-8 mb-12">
        <h2 class="text-2xl font-bold text-slate-900 mb-6">我们的优势</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div>
            <h3 class="text-lg font-semibold text-slate-900 mb-2">🌍 全球覆盖</h3>
            <p class="text-slate-600">我们的服务覆盖全球 150+ 个国家和地区。</p>
          </div>
          <div>
            <h3 class="text-lg font-semibold text-slate-900 mb-2">🔒 安全可靠</h3>
            <p class="text-slate-600">采用业界领先的安全技术保护您的交易和数据。</p>
          </div>
          <div>
            <h3 class="text-lg font-semibold text-slate-900 mb-2">⚡ 高效便捷</h3>
            <p class="text-slate-600">一键发布、自动匹配、快速成交，让交易更高效。</p>
          </div>
          <div>
            <h3 class="text-lg font-semibold text-slate-900 mb-2">👥 专业团队</h3>
            <p class="text-slate-600">拥有500+专业的运营和服务团队。</p>
          </div>
          <div>
            <h3 class="text-lg font-semibold text-slate-900 mb-2">📞 7*24支持</h3>
            <p class="text-slate-600">全天候客户服务，随时解决您的问题。</p>
          </div>
          <div>
            <h3 class="text-lg font-semibold text-slate-900 mb-2">💼 商务赋能</h3>
            <p class="text-slate-600">提供营销、物流、金融等一站式解决方案。</p>
          </div>
        </div>
      </div>

      <!-- 合作伙伴 -->
      <div class="text-center">
        <h2 class="text-2xl font-bold text-slate-900 mb-8">我们的合作伙伴</h2>
        <div class="grid grid-cols-2 md:grid-cols-6 gap-6">
          <div v-for="i in 6" :key="i" class="bg-white rounded-lg p-6 flex items-center justify-center h-24">
            <p class="text-slate-400">合作伙伴 {{ i }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const aboutInfo = ref({
  companyName: 'Saleyee',
  description: '我们是一个领先的在线交易平台，致力于为全球商人提供高效、安全、便捷的交易服务。',
  founded: '2015年',
  team: '500+专业团队',
  users: '100万+活跃用户',
  mission: '让交易更简单，让生意更好做',
  values: ['诚实', '专业', '创新', '服务'],
})
</script>

<style scoped>
/* 组件特定样式 */
</style>
