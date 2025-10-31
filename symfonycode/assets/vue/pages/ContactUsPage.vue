<script setup>
import { ref } from 'vue'

const contactInfo = ref({
  email: 'support@saleyee.com',
  phone: '+86-800-123-4567',
  wechat: 'saleyee_support',
  qq: '123456789',
  address: '北京市朝阳区某某街道123号',
  workingHours: '周一至周五 9:00-18:00',
})

const formData = ref({
  name: '',
  email: '',
  subject: '',
  message: '',
})

const handleSubmit = async () => {
  if (!formData.value.name || !formData.value.email || !formData.value.message) {
    alert('请填写所有必填字段')
    return
  }

  try {
    const response = await fetch('/help/contact', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: new URLSearchParams(formData.value),
    })
    const result = await response.json()
    if (result.success) {
      alert('感谢您的反馈，我们会尽快回复！')
      formData.value = { name: '', email: '', subject: '', message: '' }
    }
  } catch (error) {
    alert('提交失败，请稍后重试')
  }
}
</script>

<template>
  <div class="min-h-screen bg-slate-50">
    <div class="mx-auto w-full max-w-[1500px] md:w-[80%] md:min-w-[1150px] px-4 md:px-0 py-6">
      <h1 class="text-3xl font-bold text-slate-900 mb-8">联系我们</h1>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- 联系方式 -->
        <div class="space-y-6">
          <div class="bg-white rounded-lg p-6">
            <h2 class="text-lg font-bold text-slate-900 mb-4">联系方式</h2>

            <div class="space-y-4">
              <div>
                <p class="text-sm text-slate-600 mb-1">电话</p>
                <p class="text-lg font-semibold text-slate-900">{{ contactInfo.phone }}</p>
              </div>
              <div>
                <p class="text-sm text-slate-600 mb-1">邮箱</p>
                <p class="text-lg font-semibold text-slate-900">{{ contactInfo.email }}</p>
              </div>
              <div>
                <p class="text-sm text-slate-600 mb-1">微信</p>
                <p class="text-lg font-semibold text-slate-900">{{ contactInfo.wechat }}</p>
              </div>
              <div>
                <p class="text-sm text-slate-600 mb-1">QQ</p>
                <p class="text-lg font-semibold text-slate-900">{{ contactInfo.qq }}</p>
              </div>
              <div>
                <p class="text-sm text-slate-600 mb-1">地址</p>
                <p class="text-lg font-semibold text-slate-900">{{ contactInfo.address }}</p>
              </div>
              <div>
                <p class="text-sm text-slate-600 mb-1">工作时间</p>
                <p class="text-lg font-semibold text-slate-900">{{ contactInfo.workingHours }}</p>
              </div>
            </div>
          </div>

          <!-- 地图占位符 -->
          <div class="bg-slate-200 rounded-lg h-64 flex items-center justify-center">
            <p class="text-slate-600">地图展示位置</p>
          </div>
        </div>

        <!-- 联系表单 -->
        <div class="bg-white rounded-lg p-6">
          <h2 class="text-lg font-bold text-slate-900 mb-4">发送消息</h2>

          <form @submit.prevent="handleSubmit" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-slate-900 mb-1">姓名 *</label>
              <input
                v-model="formData.name"
                type="text"
                required
                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:border-primary"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-slate-900 mb-1">邮箱 *</label>
              <input
                v-model="formData.email"
                type="email"
                required
                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:border-primary"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-slate-900 mb-1">主题</label>
              <input
                v-model="formData.subject"
                type="text"
                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:border-primary"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-slate-900 mb-1">消息 *</label>
              <textarea
                v-model="formData.message"
                required
                rows="6"
                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:border-primary resize-none"
              ></textarea>
            </div>

            <button type="submit" class="w-full btn btn-primary">
              发送消息
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* 组件特定样式 */
</style>
