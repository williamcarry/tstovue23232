<script setup>
const handleBannerClick = (url) => {
  window.location.href = url
}
</script>

<template>
  <div class="w-full bg-gradient-to-r from-primary to-primary-light text-white py-12">
    <div class="mx-auto w-full max-w-[1500px] md:w-[80%] md:min-w-[1150px] px-4 md:px-0">
      <div class="text-center">
        <h2 class="text-4xl font-bold mb-4">欢迎来到 Saleyee</h2>
        <p class="text-xl mb-6">全球领先的在线交易平台</p>
        <button @click="handleBannerClick('/shop/all-products')" class="btn btn-outline text-white border-white hover:bg-white hover:text-primary">
          开始浏览 →
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* 组件特定样式 */
</style>
