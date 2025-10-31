<template>
  <div class="member-create">
    <!-- 页面头部 -->
    <div class="page-header">
      <h2 class="page-title">
        <el-icon class="title-icon"><User /></el-icon>
        添加会员
      </h2>
    </div>
    
    <el-card class="box-card">
      <template #header>
        <div class="card-header">
          <div class="card-header-title">
            <el-icon class="card-icon"><Plus /></el-icon>
            会员信息
          </div>
        </div>
      </template>
      
      <el-form :model="memberForm" :rules="rules" ref="formRef" label-width="120px">
        <!-- 基本信息 -->
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="用户名" prop="username">
              <el-input v-model="memberForm.username" />
              <div class="form-item-help">用户名必须是英文字母、数字和下划线的组合，且不能以数字或下划线开头</div>
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="邮箱" prop="email">
              <el-input v-model="memberForm.email" />
            </el-form-item>
          </el-col>
        </el-row>
        
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="密码" prop="password">
              <el-input v-model="memberForm.password" type="password" />
              <div class="form-item-help">密码长度至少8个字符</div>
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="确认密码" prop="confirmPassword">
              <el-input v-model="memberForm.confirmPassword" type="password" />
            </el-form-item>
          </el-col>
        </el-row>
        
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="昵称">
              <el-input v-model="memberForm.nickname" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="真实姓名">
              <el-input v-model="memberForm.realName" />
            </el-form-item>
          </el-col>
        </el-row>
        
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="手机号">
              <el-input v-model="memberForm.mobile" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="性别">
              <el-select v-model="memberForm.gender">
                <el-option label="未知" :value="0" />
                <el-option label="男" :value="1" />
                <el-option label="女" :value="2" />
              </el-select>
            </el-form-item>
          </el-col>
        </el-row>
        
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="身份证号">
              <el-input v-model="memberForm.individualIdCard" />
            </el-form-item>
          </el-col>
        </el-row>
        
        <!-- 上传区域 - 头像、身份证正面、身份证反面在同一排 -->
        <el-row :gutter="20" class="upload-row">
          <el-col :span="8">
            <div class="upload-section">
              <div class="upload-label">头像</div>
              <file-upload
                v-model="memberForm.avatar"
                :admin-login-path="adminLoginPath"
                accept="image/*"
                :max-size="5"
              />
            </div>
          </el-col>
          <el-col :span="8">
            <div class="upload-section">
              <div class="upload-label">身份证正面</div>
              <file-upload
                v-model="memberForm.individualIdFront"
                :admin-login-path="adminLoginPath"
                accept="image/*"
                :max-size="5"
              />
            </div>
          </el-col>
          <el-col :span="8">
            <div class="upload-section">
              <div class="upload-label">身份证反面</div>
              <file-upload
                v-model="memberForm.individualIdBack"
                :admin-login-path="adminLoginPath"
                accept="image/*"
                :max-size="5"
              />
            </div>
          </el-col> 
        </el-row>
        
        <!-- 操作按钮 -->
        <el-form-item class=" bottom-btns">
          <el-button type="primary" @click="submitForm" :loading="submitting">创建会员</el-button>
          <el-button @click="resetForm">重置</el-button>
        </el-form-item>
      </el-form>
    </el-card>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import {
  ElCard,
  ElForm,
  ElFormItem,
  ElInput,
  ElRow,
  ElCol,
  ElSelect,
  ElOption,
  ElButton,
  ElMessage
} from 'element-plus'
import FileUpload from './FileUpload.vue'

// 定义组件属性
const props = defineProps({
  adminLoginPath: {
    type: String,
    default: ''
  }
})

// 定义事件
const emit = defineEmits(['member-created'])

// 表单引用
const formRef = ref()

// 提交状态
const submitting = ref(false)

// 会员表单数据
const memberForm = reactive({
  username: '',
  email: '',
  password: '',
  confirmPassword: '',
  nickname: '',
  realName: '',
  mobile: '',
  gender: 0,
  avatar: '',
  individualIdCard: '',
  individualIdFront: '',
  individualIdBack: '',
  parentId: 0
})

// 表单验证规则
const rules = {
  username: [
    { required: true, message: '请输入用户名', trigger: 'blur' },
    { min: 3, max: 20, message: '用户名长度应在3-20个字符之间', trigger: 'blur' },
    { 
      pattern: /^[a-zA-Z][a-zA-Z0-9_]*$/, 
      message: '用户名必须是英文字母、数字和下划线的组合，且不能以数字或下划线开头', 
      trigger: 'blur' 
    }
  ],
  email: [
    { required: true, message: '请输入邮箱', trigger: 'blur' },
    { type: 'email', message: '请输入正确的邮箱格式', trigger: 'blur' }
  ],
  password: [
    { required: true, message: '请输入密码', trigger: 'blur' },
    { min: 8, message: '密码长度至少8个字符', trigger: 'blur' }
  ],
  confirmPassword: [
    { required: true, message: '请确认密码', trigger: 'blur' },
    { 
      validator: (rule, value, callback) => {
        if (value !== memberForm.password) {
          callback(new Error('两次输入的密码不一致'))
        } else {
          callback()
        }
      },
      trigger: 'blur'
    }
  ]
}

// 提交表单
const submitForm = async () => {
  if (!formRef.value) return
  
  await formRef.value.validate(async (valid) => {
    if (valid) {
      submitting.value = true
      try {
        const response = await fetch(`/admin${props.adminLoginPath}/member/create`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: JSON.stringify(memberForm)
        })
        
        const result = await response.json()
        
        if (result.success) {
          ElMessage.success(result.message)
          resetForm()
          emit('member-created', result.member)
        } else {
          ElMessage.error(result.error || '创建失败')
        }
      } catch (error) {
        ElMessage.error('创建失败: ' + error.message)
      } finally {
        submitting.value = false
      }
    } else {
      ElMessage.error('请填写正确的表单信息')
    }
  })
}

// 重置表单
const resetForm = () => {
  if (formRef.value) {
    formRef.value.resetFields()
  }
  
  // 重置表单数据
  Object.keys(memberForm).forEach(key => {
    if (typeof memberForm[key] === 'string') {
      memberForm[key] = ''
    } else if (typeof memberForm[key] === 'number') {
      memberForm[key] = key === 'gender' ? 0 : 0
    }
  })
}
</script>

<style scoped>
.member-create {
  width: 100%;
  max-width: 1200px;
  box-sizing: border-box;
  margin: 0 auto;
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

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.card-header-title {
  font-size: 18px;
  font-weight: bold;
  display: flex;
  align-items: center;
}

.card-icon {
  margin-right: 10px;
  vertical-align: middle;
}

.form-item-help {
  font-size: 12px;
  color: #909399;
  line-height: 1.5;
  margin-top: 5px;
}

.upload-row {
  margin-bottom: 20px;
}

.upload-section {
  text-align: center;
}

.upload-label {
  margin-bottom: 10px;
  font-weight: 500;
  color: #606266;
}

.form-actions {
  display: flex;
  justify-content: center;
  gap: 10px;
  margin-top: 20px;
}
.bottom-btns {
  margin-left: 0!important;
  display: flex;
  justify-content: center;
}
</style>