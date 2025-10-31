<template>
  <div class="member-edit">
    <el-form :model="memberForm" :rules="rules" ref="formRef" label-width="120px">
      <!-- 基本信息 -->
      <el-row :gutter="20">
        <el-col :span="12">
          <el-form-item label="用户名" prop="username">
            <el-input v-model="memberForm.username" :disabled="!!memberForm.id" />
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
          <el-form-item label="新密码">
            <el-input v-model="memberForm.password" type="password" placeholder="留空则不修改密码" />
          </el-form-item>
        </el-col>
        <el-col :span="12">
          <el-form-item label="确认新密码">
            <el-input v-model="memberForm.confirmPassword" type="password" placeholder="留空则不修改密码" />
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
              endpoint-type="member"
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
              endpoint-type="member"
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
              endpoint-type="member"
            />
          </div>
        </el-col>
      </el-row>
      
      <!-- 状态信息 -->
      <h3>状态信息</h3>
      <el-row :gutter="20">
        <el-col :span="12">
          <el-form-item label="账号状态">
            <el-switch v-model="memberForm.isActive" active-text="激活" inactive-text="禁用" />
          </el-form-item>
        </el-col>
        <el-col :span="12">
          <el-form-item label="实名认证">
            <el-switch v-model="memberForm.isVerified" active-text="已认证" inactive-text="未认证" />
          </el-form-item>
        </el-col>
      </el-row>
      
      <!-- 操作按钮 -->
      <el-form-item>
        <el-button type="primary" @click="submitForm" :loading="submitting">保存</el-button>
        <el-button @click="cancelEdit">取消</el-button>
      </el-form-item>
    </el-form>
  </div>
</template>

<script setup>
import { ref, reactive, watch } from 'vue'
import {
  ElForm,
  ElFormItem,
  ElInput,
  ElRow,
  ElCol,
  ElSelect,
  ElOption,
  ElSwitch,
  ElButton,
  ElMessage
} from 'element-plus'
import FileUpload from './FileUpload.vue'

// 定义组件属性
const props = defineProps({
  adminLoginPath: {
    type: String,
    default: ''
  },
  member: {
    type: Object,
    default: () => ({})
  }
})

// 定义事件
const emit = defineEmits(['member-updated', 'cancel'])

// 表单引用
const formRef = ref()

// 提交状态
const submitting = ref(false)

// 会员表单数据
const memberForm = reactive({
  id: null,
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
  isActive: true,
  isVerified: false
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
    { 
      validator: (rule, value, callback) => {
        if (value && value.length < 8) {
          callback(new Error('密码长度至少8个字符'))
        } else {
          callback()
        }
      },
      trigger: 'blur'
    }
  ],
  confirmPassword: [
    { 
      validator: (rule, value, callback) => {
        if (value && value !== memberForm.password) {
          callback(new Error('两次输入的密码不一致'))
        } else {
          callback()
        }
      },
      trigger: 'blur'
    }
  ]
}

// 监听member属性变化，及时更新表单数据
watch(() => props.member, (newMember) => {
  if (newMember && newMember.id) {
    // 初始化表单数据
    Object.keys(memberForm).forEach(key => {
      if (newMember.hasOwnProperty(key)) {
        memberForm[key] = newMember[key]
      }
    })
  } else {
    // 重置表单数据
    Object.keys(memberForm).forEach(key => {
      if (key === 'gender') {
        memberForm[key] = 0
      } else if (key === 'isActive') {
        memberForm[key] = true
      } else if (key === 'isVerified') {
        memberForm[key] = false
      } else {
        memberForm[key] = ''
      }
    })
  }
}, { immediate: true })

// 提交表单
const submitForm = async () => {
  if (!formRef.value) return
  
  await formRef.value.validate(async (valid) => {
    if (valid) {
      submitting.value = true
      try {
        const response = await fetch(`/admin${props.adminLoginPath}/member/${memberForm.id}/update`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: JSON.stringify(memberForm)
        })
        
        const result = await response.json()
        
        if (result.success) {
          emit('member-updated', result.member)
        } else {
          ElMessage.error(result.error || '更新失败')
        }
      } catch (error) {
        ElMessage.error('更新失败: ' + error.message)
      } finally {
        submitting.value = false
      }
    } else {
      ElMessage.error('请填写正确的表单信息')
    }
  })
}

// 取消编辑
const cancelEdit = () => {
  emit('cancel')
}
</script>

<style scoped>
.member-edit {
  width: 100%;
  max-width: 1200px;
  margin: 0 auto;
}

h3 {
  margin: 20px 0 10px 0;
  padding-bottom: 10px;
  border-bottom: 1px solid #ebeef5;
  color: #606266;
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
</style>