<!--
CSS 引用说明：
1. 全局样式：在 src/main.ts 中自动加载
   - src/assets/main.css (导入 src/assets/base.css)
     - @tailwind base, components, utilities (Tailwind CSS)
     - 全局 CSS 变量 (--color-*, --section-gap, --category-width 等)
   - Element Plus 样式 (element-plus/dist/index.css)
2. 页面局部样式：该文件底部的 <style scoped> 块
3. 导入的子组件样式：由各子组件的 <style scoped> 块提供
-->
<template>
  <div class="space-y-6">
    <!-- 更改密码���片 -->
    <div class="bg-white rounded-lg border border-slate-200 p-6">
      <div class="flex items-center justify-between mb-6">
        <h3 class="text-lg font-semibold text-slate-900">更改密码</h3>
        <span class="text-sm text-slate-600">定期更改密码可保护账户安全</span>
      </div>

      <el-form :model="passwordForm" label-width="140px" class="max-w-lg">
        <el-form-item label="新密码：">
          <el-input
            v-model="passwordForm.newPassword"
            type="password"
            placeholder="至少8个字符，包含大小写字母、数字"
            show-password
          />
          <div class="text-xs text-slate-600 mt-2">
            <div :class="passwordStrength.length ? 'text-green-600' : ''">✓ 至少8个字符</div>
            <div :class="passwordStrength.uppercase ? 'text-green-600' : ''">✓ 包含大写字母 (A-Z)</div>
            <div :class="passwordStrength.lowercase ? 'text-green-600' : ''">✓ 包含小写字母 (a-z)</div>
            <div :class="passwordStrength.number ? 'text-green-600' : ''">✓ 包含数字 (0-9)</div>
          </div>
        </el-form-item>
        <el-form-item label="确认密码：">
          <el-input
            v-model="passwordForm.confirmPassword"
            type="password"
            placeholder="请再次输入新密码"
            show-password
          />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="handleChangePassword">更改密码</el-button>
        </el-form-item>
      </el-form>
    </div>

    <!-- 邮箱验证卡片 -->
    <div class="bg-white rounded-lg border border-slate-200 p-6">
      <div class="flex items-center justify-between mb-6">
        <div>
          <h3 class="text-lg font-semibold text-slate-900">验证邮箱</h3>
          <p class="text-sm text-slate-600 mt-1">
            <span v-if="emailVerified" class="text-green-600">✓ 已验证</span>
            <span v-else class="text-orange-600">未验证</span>
          </p>
        </div>
        <div class="text-right">
          <p class="text-sm font-medium text-slate-900">{{ email || '未设置' }}</p>
          <p class="text-xs text-slate-600 mt-1">当前邮箱</p>
        </div>
      </div>

      <el-form v-if="!emailVerifying" label-width="140px" class="max-w-lg">
        <el-form-item label="新邮箱地址：">
          <el-input v-model="emailForm.newEmail" placeholder="请输入新的邮箱地址" type="email" />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="handleSendEmailCode">发送验证码</el-button>
          <span class="text-sm text-slate-600 ml-4">邮箱验证码会发送到您的邮箱</span>
        </el-form-item>
      </el-form>

      <el-form v-else :model="emailForm" label-width="140px" class="max-w-lg">
        <el-alert
          title="验证码已发送"
          :description="`验证码已发送到 ${emailForm.newEmail}，请在10分钟内输入`"
          type="info"
          closable
          @close="emailVerifying = false"
          class="mb-6"
        />
        <el-form-item label="验证码：">
          <el-input
            v-model="emailForm.emailCode"
            placeholder="请输入6位验证码"
            maxlength="6"
            show-word-limit
          />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="handleVerifyEmail">验证邮箱</el-button>
          <el-button @click="emailVerifying = false">取消</el-button>
        </el-form-item>
      </el-form>
    </div>

    <!-- 手机号验证卡片 -->
    <div class="bg-white rounded-lg border border-slate-200 p-6">
      <div class="flex items-center justify-between mb-6">
        <div>
          <h3 class="text-lg font-semibold text-slate-900">验证手机号</h3>
          <p class="text-sm text-slate-600 mt-1">
            <span v-if="phoneVerified" class="text-green-600">✓ 已验证</span>
            <span v-else class="text-orange-600">未验证</span>
          </p>
        </div>
        <div class="text-right">
          <p class="text-sm font-medium text-slate-900">{{ phone || '未设置' }}</p>
          <p class="text-xs text-slate-600 mt-1">当前手机号</p>
        </div>
      </div>

      <el-form v-if="!phoneVerifying" :model="phoneForm" label-width="140px" class="max-w-lg">
        <el-form-item label="国家/地区：">
          <el-select v-model="phoneForm.country" placeholder="请选择国家/地区" class="w-full">
            <el-option label="中国 (+86)" value="CN" />
            <el-option label="美国 (+1)" value="US" />
            <el-option label="日本 (+81)" value="JP" />
            <el-option label="英国 (+44)" value="GB" />
            <el-option label="加拿大 (+1)" value="CA" />
            <el-option label="澳大利亚 (+61)" value="AU" />
          </el-select>
        </el-form-item>
        <el-form-item label="手机号：">
          <el-input
            v-model="phoneForm.newPhone"
            placeholder="请输入手机号码"
            type="tel"
          />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="handleSendPhoneCode">发送验证码</el-button>
          <span class="text-sm text-slate-600 ml-4">验证码将通过短信发送</span>
        </el-form-item>
      </el-form>

      <el-form v-else :model="phoneForm" label-width="140px" class="max-w-lg">
        <el-alert
          title="验证码已发送"
          :description="`验证码已发送到 ${phoneForm.newPhone}，请在10分钟内输入`"
          type="info"
          closable
          @close="phoneVerifying = false"
          class="mb-6"
        />
        <el-form-item label="验证码：">
          <el-input
            v-model="phoneForm.phoneCode"
            placeholder="请输入6位验证码"
            maxlength="6"
            show-word-limit
          />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="handleVerifyPhone">验证手机号</el-button>
          <el-button @click="phoneVerifying = false">取消</el-button>
        </el-form-item>
      </el-form>
    </div>

    <!-- 安全提示 -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
      <h4 class="font-semibold text-blue-900 mb-3">安全建议</h4>
      <ul class="text-sm text-blue-800 space-y-2">
        <li>• 定期��改密码，不要使用过于简单的密码</li>
        <li>• 不要在公共计算机上登录您的账户</li>
        <li>• 不要与他人共享您的登录凭据</li>
        <li>• 定期检查您的账户活动，发现异常立即更改密码</li>
        <li>• 启用两步验证以增强账户安全性</li>
      </ul>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { ElMessage } from 'element-plus'

// 密码表单
const passwordForm = reactive({
  newPassword: '',
  confirmPassword: '',
})

// 邮箱表单
const emailForm = reactive({
  newEmail: '',
  emailCode: '',
})

// 手机号表单
const phoneForm = reactive({
  country: 'CN',
  newPhone: '',
  phoneCode: '',
})

// 状态
const emailVerified = ref(false)
const phoneVerified = ref(false)
const emailVerifying = ref(false)
const phoneVerifying = ref(false)

// 模拟数据
const email = ref('user@example.com')
const phone = ref('+86 13800138000')

// 计算密码强度
const passwordStrength = computed(() => {
  const pwd = passwordForm.newPassword
  return {
    length: pwd.length >= 8,
    uppercase: /[A-Z]/.test(pwd),
    lowercase: /[a-z]/.test(pwd),
    number: /[0-9]/.test(pwd),
  }
})

// 更改密码
function handleChangePassword() {
  if (!passwordForm.newPassword) {
    ElMessage.warning('请输入新密码')
    return
  }
  if (passwordForm.newPassword.length < 8) {
    ElMessage.warning('新密码至少需要8个字符')
    return
  }
  if (passwordForm.newPassword !== passwordForm.confirmPassword) {
    ElMessage.warning('两次输入的密码不一致')
    return
  }
  if (
    !passwordStrength.value.uppercase ||
    !passwordStrength.value.lowercase ||
    !passwordStrength.value.number
  ) {
    ElMessage.warning('密码必须包含大小写字母和数字')
    return
  }

  ElMessage.success('密码已更改')
  passwordForm.newPassword = ''
  passwordForm.confirmPassword = ''
}

// 发送邮箱验证码
function handleSendEmailCode() {
  if (!emailForm.newEmail) {
    ElMessage.warning('请输入邮箱地址')
    return
  }
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailForm.newEmail)) {
    ElMessage.warning('请输入有效的邮箱地址')
    return
  }

  emailVerifying.value = true
  ElMessage.success('验证码已发送到邮箱')
}

// 验证邮箱
function handleVerifyEmail() {
  if (!emailForm.emailCode) {
    ElMessage.warning('请输入验证码')
    return
  }
  if (emailForm.emailCode.length !== 6) {
    ElMessage.warning('验证码长度不正确')
    return
  }

  email.value = emailForm.newEmail
  emailVerified.value = true
  emailVerifying.value = false
  emailForm.newEmail = ''
  emailForm.emailCode = ''
  ElMessage.success('邮箱验证成功')
}

// 发送手机号验证码
function handleSendPhoneCode() {
  if (!phoneForm.newPhone) {
    ElMessage.warning('请输入手机号')
    return
  }
  if (!/^\d{10,}$/.test(phoneForm.newPhone.replace(/\D/g, ''))) {
    ElMessage.warning('请输入有效的手机号')
    return
  }

  phoneVerifying.value = true
  ElMessage.success('验证码已发送到手机')
}

// 验证手机号
function handleVerifyPhone() {
  if (!phoneForm.phoneCode) {
    ElMessage.warning('请输入验证码')
    return
  }
  if (phoneForm.phoneCode.length !== 6) {
    ElMessage.warning('验证码长度不正确')
    return
  }

  const countryCode = {
    CN: '+86',
    US: '+1',
    JP: '+81',
    GB: '+44',
    CA: '+1',
    AU: '+61',
  }[phoneForm.country] || ''

  phone.value = `${countryCode} ${phoneForm.newPhone}`
  phoneVerified.value = true
  phoneVerifying.value = false
  phoneForm.newPhone = ''
  phoneForm.phoneCode = ''
  ElMessage.success('手机号验证成功')
}
</script>

<style scoped></style>
