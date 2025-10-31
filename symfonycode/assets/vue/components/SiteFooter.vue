<script setup>
const handleContactClick = () => {
  window.location.href = '/help/contact'
}

const handleAboutClick = () => {
  window.location.href = '/help/about'
}
</script>

<template>
  <footer class="bg-slate-900 text-slate-300 mt-12">
    <!-- 主页脚内容 -->
    <div class="mx-auto w-full max-w-[1500px] md:w-[80%] md:min-w-[1150px] px-4 md:px-0 py-12">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
        <!-- 公司信息 -->
        <div>
          <h3 class="text-white font-bold mb-4">关于Saleyee</h3>
          <ul class="space-y-2 text-sm">
            <li><a href="#" @click.prevent="handleAboutClick" class="hover:text-white transition">关于我们</a></li>
            <li><a href="#" class="hover:text-white transition">公司新闻</a></li>
            <li><a href="#" class="hover:text-white transition">招聘信息</a></li>
            <li><a href="#" class="hover:text-white transition">联系我们</a></li>
          </ul>
        </div>

        <!-- 帮助中心 -->
        <div>
          <h3 class="text-white font-bold mb-4">帮助中心</h3>
          <ul class="space-y-2 text-sm">
            <li><a href="/help/" class="hover:text-white transition">常见问题</a></li>
            <li><a href="/help/guide" class="hover:text-white transition">操作指南</a></li>
            <li><a href="#" class="hover:text-white transition">平台规则</a></li>
            <li><a href="#" class="hover:text-white transition">交易保障</a></li>
          </ul>
        </div>

        <!-- 商务服务 -->
        <div>
          <h3 class="text-white font-bold mb-4">商务服务</h3>
          <ul class="space-y-2 text-sm">
            <li><a href="#" class="hover:text-white transition">品牌入驻</a></li>
            <li><a href="#" class="hover:text-white transition">供应商服务</a></li>
            <li><a href="#" class="hover:text-white transition">代理商申请</a></li>
            <li><a href="#" class="hover:text-white transition">广告合���</a></li>
          </ul>
        </div>

        <!-- 联系我们 -->
        <div>
          <h3 class="text-white font-bold mb-4">联系我们</h3>
          <ul class="space-y-2 text-sm">
            <li><span class="hover:text-white">📞 400-123-4567</span></li>
            <li><span class="hover:text-white">✉️ support@saleyee.com</span></li>
            <li><span class="hover:text-white">📱 微信: saleyee_service</span></li>
            <li><button @click="handleContactClick" class="hover:text-white transition">更多联系方式 →</button></li>
          </ul>
        </div>
      </div>

      <!-- 分割线 -->
      <div class="border-t border-slate-700 my-8"></div>

      <!-- 底部信息 -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-sm">
        <!-- 政策链接 -->
        <div class="flex gap-4 text-xs text-slate-400">
          <a href="#" class="hover:text-slate-300 transition">隐私政策</a>
          <a href="#" class="hover:text-slate-300 transition">服务条款</a>
          <a href="#" class="hover:text-slate-300 transition">Cookie设置</a>
          <a href="#" class="hover:text-slate-300 transition">企业社会责任</a>
        </div>

        <!-- 版权信息 -->
        <div class="text-center text-xs text-slate-400">
          <p>&copy; 2024 Saleyee. 保留所有权利。</p>
        </div>

        <!-- 国际化 -->
        <div class="flex justify-end gap-4 text-xs">
          <a href="#" class="text-slate-400 hover:text-slate-300 transition">简体中文</a>
          <a href="#" class="text-slate-400 hover:text-slate-300 transition">English</a>
          <a href="#" class="text-slate-400 hover:text-slate-300 transition">Español</a>
        </div>
      </div>
    </div>
  </footer>
</template>

<style scoped>
/* 页脚特定样式 */
</style>
