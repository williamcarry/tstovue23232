<script setup>
import { ref } from 'vue'

const guides = ref([
  {
    id: 1,
    title: '新手入门指南',
    description: '快速了解平台基本操作',
    steps: [
      { title: '第一步：注册账户', content: '点击右上角"注册"按钮，填写邮箱和密码即可完成注册。' },
      { title: '第二步：完善信息', content: '登录后进入个人中心，补充企业和联系信息。' },
      { title: '第三步：首次发布商品', content: '进入"我的商品"，点击"发布商品"，填写商品信息并上传图片。' },
    ],
  },
  {
    id: 2,
    title: '商品管理完全指南',
    description: '学习如何有效管理您的商品',
    steps: [
      { title: '上传商品', content: '详细的商品上传步骤和注意事项。' },
      { title: '编辑商品信息', content: '如何修改商品基本信息、价格、库存等。' },
      { title: '管理库存', content: '实时查看和更新库存数量。' },
    ],
  },
])

const selectedGuide = ref(null)

const handleSelectGuide = (id) => {
  selectedGuide.value = selectedGuide.value === id ? null : id
}
</script>

<template>
  <div class="min-h-screen bg-slate-50">
    <div class="mx-auto w-full max-w-[1500px] md:w-[80%] md:min-w-[1150px] px-4 md:px-0 py-6">
      <h1 class="text-3xl font-bold text-slate-900 mb-8">操作指南</h1>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- 指南列表 -->
        <div class="md:col-span-1">
          <div class="space-y-3">
            <button
              v-for="guide in guides"
              :key="guide.id"
              @click="handleSelectGuide(guide.id)"
              :class="[
                'w-full text-left p-4 rounded-lg border-2 transition',
                selectedGuide === guide.id
                  ? 'border-primary bg-primary/10'
                  : 'border-slate-200 bg-white hover:border-primary'
              ]"
            >
              <h3 class="font-semibold text-slate-900">{{ guide.title }}</h3>
              <p class="text-sm text-slate-600 mt-1">{{ guide.description }}</p>
            </button>
          </div>
        </div>

        <!-- 指南详情 -->
        <div class="md:col-span-2">
          <div v-if="selectedGuide" class="bg-white rounded-lg p-8">
            <div
              v-for="(guide, idx) in guides"
              v-show="guide.id === selectedGuide"
              :key="guide.id"
            >
              <h2 class="text-2xl font-bold text-slate-900 mb-6">{{ guide.title }}</h2>
              <div class="space-y-6">
                <div
                  v-for="(step, stepIdx) in guide.steps"
                  :key="stepIdx"
                  class="flex gap-4"
                >
                  <div class="flex-shrink-0 w-8 h-8 rounded-full bg-primary text-white flex items-center justify-center font-bold text-sm">
                    {{ stepIdx + 1 }}
                  </div>
                  <div class="flex-1">
                    <h3 class="font-semibold text-slate-900 mb-2">{{ step.title }}</h3>
                    <p class="text-slate-600">{{ step.content }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div v-else class="bg-white rounded-lg p-8 text-center text-slate-500">
            <p>请选择���个指南查看详情</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* 组件特定样式 */
</style>
