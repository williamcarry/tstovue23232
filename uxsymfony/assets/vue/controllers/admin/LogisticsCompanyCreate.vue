<template>
  <div class="logistics-company-create-container">
    <!-- 页面头部 -->
    <div class="page-header">
      <h2 class="page-title">
        <el-icon class="title-icon"><Plus /></el-icon>
        添加物流公司
      </h2>
    </div>
    
    <!-- 创建表单 -->
    <el-card class="form-card">
      <el-form
        ref="formRef"
        :model="form"
        :rules="rules"
        label-position="right"
        label-width="120px"
        :disabled="submitting"
      >
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="公司名称" prop="name">
              <el-input v-model="form.name" placeholder="请输入物流公司名称" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="英文名称" prop="nameEn">
              <el-input v-model="form.nameEn" placeholder="请输入物流公司英文名称" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="排序" prop="sortOrder">
              <el-input-number v-model="form.sortOrder" :min="0" :max="9999" controls-position="right" style="width: 100%;" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="状态" prop="isActive">
              <el-switch
                v-model="form.isActive"
                active-text="启用"
                inactive-text="禁用"
              />
            </el-form-item>
          </el-col>
        </el-row>
        
        <el-form-item>
          <el-button type="primary" @click="submitForm" :loading="submitting">
            {{ submitting ? '提交中...' : '提交' }}
          </el-button>
          <el-button @click="resetForm">重置</el-button>
        </el-form-item>
      </el-form>
    </el-card>
  </div>
</template>

<script setup>
import { ref, defineEmits } from 'vue'
import {
  ElCard,
  ElForm,
  ElFormItem,
  ElInput,
  ElInputNumber,
  ElSwitch,
  ElButton,
  ElRow,
  ElCol,
  ElIcon,
  ElMessage
} from 'element-plus'
import { Plus } from '@element-plus/icons-vue'

// 定义组件属性
const props = defineProps({
  adminLoginPath: {
    type: String,
    default: ''
  }
})

// 定义事件发射
const emit = defineEmits(['logistics-company-created'])

// 表单引用
const formRef = ref()

// 表单数据
const form = ref({
  name: '',
  nameEn: '',
  sortOrder: 0,
  isActive: true
})

// 表单验证规则
const rules = {
  name: [
    { required: true, message: '请输入物流公司名称', trigger: 'blur' },
    { min: 1, max: 100, message: '长度在 1 到 100 个字符', trigger: 'blur' }
  ],
  nameEn: [
    { min: 0, max: 100, message: '长度在 0 到 100 个字符', trigger: 'blur' }
  ],
  sortOrder: [
    { required: true, message: '请输入排序值', trigger: 'blur' }
  ]
}

// 提交状态
const submitting = ref(false)

// 提交表单
const submitForm = async () => {
  if (!formRef.value) return
  
  await formRef.value.validate(async (valid) => {
    if (valid) {
      submitting.value = true
      try {
        const response = await fetch(`/admin${props.adminLoginPath}/logistics-company/create`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: JSON.stringify(form.value)
        })
        
        const result = await response.json()
        
        if (result.success) {
          ElMessage.success(result.message || '创建成功')
          // 重置表单
          resetForm()
          // 通知父组件
          emit('logistics-company-created')
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
  form.value = {
    name: '',
    nameEn: '',
    sortOrder: 0,
    isActive: true
  }
  
  if (formRef.value) {
    formRef.value.resetFields()
  }
}
</script>

<style scoped>
.logistics-company-create-container {
  width: 100%;
  max-width: 2000px;
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

.form-card {
  width: 100%;
}

.form-card :deep(.el-form-item) {
  margin-bottom: 20px;
}

.form-card :deep(.el-form-item__label) {
  font-weight: bold;
}
</style>