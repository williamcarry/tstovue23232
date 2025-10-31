<script setup>
import { ref } from 'vue'
import { ChevronDown } from 'lucide-vue-next'

const langLabel = ref('中文')

const handleLanguageChange = (lang) => {
  langLabel.value = lang === 'en' ? 'English' : '中文'
  // 可以在这里添加语言切换逻辑
}

const handleLogin = () => {
  window.location.href = '/login'
}

const handleRegister = () => {
  window.location.href = '/register'
}
</script>

<template>
  <header class="w-full border-b border-slate-200">
    <!-- 顶部工具栏 -->
    <div class="hidden md:block bg-slate-50 text-slate-600 text-sm">
      <div class="mx-auto w-full max-w-[1500px] md:w-[80%] md:min-w-[1150px] px-4 md:px-0 py-2 flex justify-between items-center" style="height: 36px; line-height: 36px">
        <div class="left-section"></div>
        <div class="flex items-center gap-4">
          <div class="relative group inline-flex items-center">
            <a href="#" class="inline-flex items-center gap-1 hover:text-primary text-sm" @click.prevent>
              <span>国际</span>
              <ChevronDown class="h-2.5 w-2.5 opacity-70" />
            </a>
          </div>
          <div class="relative group inline-flex items-center">
            <a href="#" class="inline-flex items-center gap-1 hover:text-primary text-sm" @click.prevent>
              <span class="text-primary">{{ langLabel }}</span>
              <ChevronDown class="h-2.5 w-2.5 opacity-70" />
            </a>
            <div class="hidden group-hover:flex absolute right-0 top-full mt-0 w-28 bg-white border border-slate-300 rounded shadow-md z-50 flex-col">
              <a href="#" class="block px-3 py-2 hover:bg-slate-50 text-slate-700 text-sm" @click.prevent="handleLanguageChange('en')">English</a>
              <a href="#" class="block px-3 py-2 hover:bg-slate-50 text-slate-700 text-sm border-t border-slate-200" @click.prevent="handleLanguageChange('zh')">简体中文</a>
            </div>
          </div>
          <button @click="handleLogin" class="text-slate-600 hover:text-primary">登录</button>
          <button @click="handleRegister" class="text-slate-600 hover:text-primary">注册</button>
        </div>
      </div>
    </div>

    <!-- 主导航栏 -->
    <div class="bg-white">
      <div class="mx-auto w-full max-w-[1500px] md:w-[80%] md:min-w-[1150px] px-4 md:px-0 py-4 flex items-center justify-between">
        <!-- Logo -->
        <div class="flex items-center gap-2">
          <a href="/" class="text-2xl font-bold text-primary">Saleyee</a>
        </div>

        <!-- 导航菜单 -->
        <nav class="hidden md:flex gap-6">
          <a href="/" class="text-slate-700 hover:text-primary font-medium">首页</a>
          <a href="/shop/all-products" class="text-slate-700 hover:text-primary font-medium">全部商品</a>
          <a href="/help/" class="text-slate-700 hover:text-primary font-medium">帮助中心</a>
          <a href="/help/about" class="text-slate-700 hover:text-primary font-medium">关于我们</a>
        </nav>

        <!-- 右侧功能 -->
        <div class="flex items-center gap-4">
          <a href="/cart" class="text-slate-700 hover:text-primary">
            🛒 购物车
          </a>
          <a href="/user-center" class="text-slate-700 hover:text-primary">
            👤 个人中心
          </a>
          <button class="md:hidden text-slate-700">
            ☰ 菜单
          </button>
        </div>
      </div>
    </div>
  </header>
</template>

<style scoped>
/* 组件特定样式 */
</style>
