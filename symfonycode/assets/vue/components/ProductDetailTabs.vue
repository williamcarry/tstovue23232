<script setup>
import { ref } from 'vue'

const props = defineProps({
  product: {
    type: Object,
    default: () => ({})
  }
})

const activeTab = ref('detail')
</script>

<template>
  <div class="bg-white rounded-lg">
    <!-- 选项卡标题 -->
    <div class="flex border-b border-slate-200">
      <button
        @click="activeTab = 'detail'"
        :class="[
          'flex-1 py-3 text-center font-semibold border-b-2 transition',
          activeTab === 'detail'
            ? 'border-primary text-primary'
            : 'border-transparent text-slate-600 hover:text-primary'
        ]"
      >
        商品详情
      </button>
      <button
        @click="activeTab = 'specs'"
        :class="[
          'flex-1 py-3 text-center font-semibold border-b-2 transition',
          activeTab === 'specs'
            ? 'border-primary text-primary'
            : 'border-transparent text-slate-600 hover:text-primary'
        ]"
      >
        规格参数
      </button>
      <button
        @click="activeTab = 'reviews'"
        :class="[
          'flex-1 py-3 text-center font-semibold border-b-2 transition',
          activeTab === 'reviews'
            ? 'border-primary text-primary'
            : 'border-transparent text-slate-600 hover:text-primary'
        ]"
      >
        用户评价
      </button>
    </div>

    <!-- 选项卡内容 -->
    <div class="p-6">
      <div v-show="activeTab === 'detail'" class="prose prose-sm max-w-none">
        {{ product.description || '暂无描述' }}
      </div>

      <div v-show="activeTab === 'specs'" class="space-y-4">
        <div v-for="spec in (product.specs || [])" :key="spec.name" class="flex justify-between py-2 border-b border-slate-200">
          <span class="font-semibold text-slate-900">{{ spec.name }}</span>
          <span class="text-slate-600">{{ spec.value }}</span>
        </div>
      </div>

      <div v-show="activeTab === 'reviews'" class="text-center py-8 text-slate-500">
        暂无评价
      </div>
    </div>
  </div>
</template>

<style scoped>
/* 组件特定样式 */
</style>
