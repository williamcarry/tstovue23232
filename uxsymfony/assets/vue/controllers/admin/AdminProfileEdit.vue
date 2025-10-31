<template>
  <div class="profile-container">
    <!-- 页面头部 -->
    <div class="page-header">
      <h2 class="page-title">
        <el-icon class="title-icon"><User /></el-icon>
        {{ isCreateMode ? '添加管理员' : '个人信息' }}
      </h2>
    </div>
    
    <el-card class="profile-card" shadow="hover">
      <template #header>
        <div class="card-header">
          <div class="card-header-title">
            <el-icon class="card-icon"><Edit /></el-icon>
            {{ isCreateMode ? '添加管理员' : '编辑个人信息' }}
          </div>
        </div>
      </template>
      
      <!-- 成功消息 -->
      <el-alert
        v-if="successMessage"
        :title="successMessage"
        type="success"
        show-icon
        closable
        @close="successMessage = ''"
        class="message-alert"
        effect="dark"
      />
      
      <!-- 错误消息 -->
      <el-alert
        v-if="errorMessages.length > 0"
        type="error"
        show-icon
        closable
        class="message-alert"
        effect="dark"
      >
        <template #default>
          <div v-for="(error, index) in errorMessages" :key="index">
            {{ error }}
          </div>
        </template>
      </el-alert>
      
      <el-form
        ref="formRef"
        :model="form"
        :rules="rules"
        label-width="120px"
        class="profile-form"
        @submit.prevent="submitForm"
        label-position="left"
      >
        <el-row :gutter="30">
          <el-col :span="12">
            <el-form-item label="用户名" prop="username">
              <el-input 
                v-model="form.username" 
                :disabled="!isCreateMode"
                placeholder="请输入用户名"
                clearable
              />
            </el-form-item>
            
            <el-form-item label="邮箱" prop="email">
              <el-input 
                v-model="form.email" 
                type="email"
                placeholder="请输入邮箱地址"
                clearable
              />
            </el-form-item>
            
            <el-form-item label="标记" prop="mark">
              <el-input 
                v-model="form.mark" 
                placeholder="请输入标记/备注"
                clearable
              />
            </el-form-item>
          </el-col>
          
          <el-col :span="12">
            <el-form-item v-if="!isCreateMode" label="当前密码" prop="currentPassword">
              <el-input 
                v-model="form.currentPassword" 
                type="password" 
                show-password
                placeholder="请输入当前密码"
                clearable
              />
            </el-form-item>
            
            <el-form-item label="密码" prop="newPassword">
              <el-input 
                v-model="form.newPassword" 
                type="password" 
                show-password
                :placeholder="isCreateMode ? '请输入密码（至少8个字符）' : '新密码（不改密码请留空）'"
                clearable
              />
              <div class="password-hint">{{ isCreateMode ? '密码至少需要8个字符' : '新密码至少需要8个字符，不改密码留空' }}</div>
            </el-form-item>
            
            <el-form-item label="确认密码" prop="confirmPassword">
              <el-input 
                v-model="form.confirmPassword" 
                type="password" 
                show-password
                :placeholder="isCreateMode ? '请再次输入密码' : '确认新密码'"
                clearable
              />
              <div class="password-hint">{{ isCreateMode ? '密码至少需要8个字符' : '新密码至少需要8个字符，不改密码留空' }}</div>
            </el-form-item>
          </el-col>
        </el-row>
        
        <el-form-item class="form-actions">
          <el-button 
            type="primary" 
            @click="submitForm" 
            :loading="loading"
            size="large"
            round
          >
            <el-icon><Check /></el-icon>
            {{ isCreateMode ? '创建管理员' : '保存更改' }}
          </el-button>
          <el-button @click="resetForm" size="large" round>
            <el-icon><Refresh /></el-icon>
            重置
          </el-button>
        </el-form-item>
      </el-form>
    </el-card>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
// 导入Element Plus组件
import {
  ElCard,
  ElAlert,
  ElForm,
  ElFormItem,
  ElInput,
  ElRow,
  ElCol,
  ElButton,
  ElIcon
} from 'element-plus'
import {
  User,
  Edit,
  Check,
  Refresh
} from '@element-plus/icons-vue'

// 定义 props
const props = defineProps({
  admin: {
    type: Object,
    default: () => ({})
  },
  submitUrl: {
    type: String,
    default: ''
  },
  isCreateMode: {
    type: Boolean,
    default: false
  }
})

// 定义响应式数据
const formRef = ref()
const loading = ref(false)
const successMessage = ref('')
const errorMessages = ref([])

const form = reactive({
  username: props.admin?.username || '',
  email: props.admin?.email || '',
  mark: props.admin?.mark || '',
  currentPassword: '',
  newPassword: '',
  confirmPassword: ''
})

// 验证规则
const rules = {
  username: [
    { required: true, message: '请输入用户名', trigger: 'blur' }
  ],
  email: [
    { required: true, message: '请输入邮箱', trigger: 'blur' },
    { type: 'email', message: '请输入有效的邮箱地址', trigger: 'blur' }
  ],
  mark: [
    { required: true, message: '请输入标记', trigger: 'blur' }
  ],
  newPassword: [
    { min: 8, message: '密码至少需要8个字符', trigger: 'blur' }
  ],
  confirmPassword: [
    { 
      validator: validateConfirmPassword, 
      trigger: 'blur' 
    }
  ]
}

// 验证确认密码
function validateConfirmPassword(rule, value, callback) {
  // 只有当新密码不为空时才验证确认密码
  if (form.newPassword && !value) {
    callback(new Error('请确认密码'))
  } else if (value && form.newPassword !== value) {
    callback(new Error('密码和确认密码不一致'))
  } else {
    callback()
  }
}

// 提交表单
const submitForm = async () => {
  if (!formRef.value) return
  
  await formRef.value.validate((valid) => {
    if (valid) {
      submitProfile()
    }
  })
}

// 重置表单
const resetForm = () => {
  if (props.isCreateMode) {
    form.username = ''
    form.email = ''
    form.mark = ''
    form.newPassword = ''
    form.confirmPassword = ''
  } else {
    form.email = props.admin?.email || ''
    form.mark = props.admin?.mark || ''
    form.currentPassword = ''
    form.newPassword = ''
    form.confirmPassword = ''
  }
  errorMessages.value = []
  successMessage.value = ''
}



// 提交个人信息
const submitProfile = async () => {
  loading.value = true
  errorMessages.value = []
  successMessage.value = ''
  
  try {
    // 准备表单数据
    const formData = new FormData()
    formData.append('username', form.username)
    formData.append('email', form.email)
    formData.append('mark', form.mark)
    
    // 添加/编辑模式下的不同处理
    if (props.isCreateMode) {
      // 创建模式下必须有密码
      formData.append('password', form.newPassword)
      formData.append('confirm_password', form.confirmPassword)
    } else {
      // 编辑模式下只有当密码不为空时才提交密码相关字段
      if (form.currentPassword) {
        formData.append('current_password', form.currentPassword)
      }
      if (form.newPassword) {
        formData.append('new_password', form.newPassword)
        formData.append('confirm_password', form.confirmPassword)
      }
    }
    
    // 发送请求
    const response = await fetch(props.submitUrl, {
      method: 'POST',
      body: formData,
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    
    if (response.ok) {
      const result = await response.json()
      if (result.success) {
        successMessage.value = result.message || (props.isCreateMode ? '管理员创建成功' : '个人信息更新成功')
        // 清空密码字段
        form.currentPassword = ''
        form.newPassword = ''
        form.confirmPassword = ''
      } else {
        errorMessages.value = result.errors || ['操作失败，请重试']
      }
    } else {
      // 处理HTTP错误
      try {
        const result = await response.json()
        errorMessages.value = result.errors || ['操作失败，请重试']
      } catch (e) {
        errorMessages.value = ['网络错误，请重试']
      }
    }
  } catch (error) {
    errorMessages.value = ['网络错误，请重试']
  } finally {
    loading.value = false
  }
}

// 页面加载完成后初始化
onMounted(() => {
  // 可以在这里添加任何需要在组件挂载后执行的代码
})
</script>

<style scoped>
.profile-container {
  width: 100%;
  max-width: 1200px;
  box-sizing: border-box;
  margin: 0 auto;
  padding: 20px;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  width: 100%;
}

.page-title {
  font-size: 24px;
  font-weight: bold;
  margin: 0;
  display: flex;
  align-items: center;
}

.title-icon {
  margin-right: 10px;
  vertical-align: middle;
}

.header-actions {
  display: flex;
  gap: 10px;
}

.page-title {
  font-size: 20px;
  font-weight: 600;
  margin: 0;
  display: flex;
  align-items: center;
  color: #303133;
}

.title-icon {
  margin-right: 10px;
  vertical-align: middle;
  font-size: 28px;
}

.profile-card {
  width: 100%;
  box-shadow: 0 2px 12px 0 rgba(0, 0, 0, 0.1);
  border-radius: 12px;
  overflow: hidden;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 15px;
}

.card-header-title {
  font-size: 18px;
  font-weight: bold;
  display: flex;
  align-items: center;
  color: #409eff;
}

.card-icon {
  margin-right: 10px;
  vertical-align: middle;
  font-size: 20px;
}

.message-alert {
  margin-bottom: 20px;
  border-radius: 8px;
}

.profile-form {
  margin-top: 20px;
}

.profile-form :deep(.el-form-item) {
  margin-bottom: 25px;
}

.profile-form :deep(.el-form-item__label) {
  font-weight: 500;
  color: #606266;
}

.profile-form :deep(.el-input__inner) {
  border-radius: 6px;
  transition: all 0.3s ease;
}

.profile-form :deep(.el-input__inner:hover) {
  border-color: #409eff;
}

.profile-form :deep(.el-input__inner:focus) {
  border-color: #409eff;
  box-shadow: 0 0 0 2px rgba(64, 158, 255, 0.2);
}

.password-hint {
  font-size: 12px;
  color: #909399;
  margin-top: 5px;
}

.form-actions {
  margin-top: 30px;
  text-align: center;
  padding: 20px 0;
  background-color: #f5f7fa;
  border-radius: 8px;
}

.form-actions .el-button {
  margin: 0 10px;
  padding: 12px 30px;
  font-size: 14px;
  transition: all 0.3s ease;
}

.form-actions .el-button:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}
</style>