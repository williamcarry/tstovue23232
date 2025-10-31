<template>
  <div class="logistics-company-edit-container">
    <!-- 页面头部 -->
    <div class="page-header">
      <h2 class="page-title">
        <el-icon class="title-icon"><Edit /></el-icon>
        编辑物流公司
      </h2>
    </div>
    
    <!-- 编辑表单 -->
    <el-card class="form-card" v-loading="loading">
      <el-form
        ref="formRef"
        :model="form"
        :rules="rules"
        label-position="right"
        label-width="120px"
        :disabled="submitting"
        v-if="!loading"
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
import { ref, onMounted, defineProps, defineEmits } from 'vue'
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
import { Edit } from '@element-plus/icons-vue'

// 定义组件属性
const props = defineProps({
  adminLoginPath: {
    type: String,
    default: ''
  },
  logisticsCompanyId: {
    type: Number,
    default: null
  },
  // 添加一个属性来控制是否自动加载数据
  autoLoad: {
    type: Boolean,
    default: false
  }
})

// 定义事件发射
const emit = defineEmits(['logistics-company-updated'])

// 表单引用
const formRef = ref()

// 表单数据
const form = ref({
  id: null,
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

// 加载状态
const loading = ref(false)

// 提交状态
const submitting = ref(false)

// 初始化数据
const initData = async () => {
  if (!props.logisticsCompanyId) {
    ElMessage.error('未指定物流公司ID')
    return
  }
  
  loading.value = true
  try {
    const response = await fetch(`/admin${props.adminLoginPath}/logistics-company/${props.logisticsCompanyId}/edit/tab`, {
      method: 'GET',
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    
    const result = await response.json()
    
    if (result.success) {
      form.value = {
        id: result.data.id,
        name: result.data.name,
        nameEn: result.data.nameEn,
        sortOrder: result.data.sortOrder,
        isActive: result.data.isActive
      }
    } else {
      ElMessage.error(result.error || '加载数据失败')
    }
  } catch (error) {
    ElMessage.error('加载数据失败: ' + error.message)
  } finally {
    loading.value = false
  }
}

// 提交表单
const submitForm = async () => {
  if (!formRef.value) return
  
  await formRef.value.validate(async (valid) => {
    if (valid) {
      submitting.value = true
      try {
        const response = await fetch(`/admin${props.adminLoginPath}/logistics-company/${form.value.id}/update`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: JSON.stringify(form.value)
        })
        
        const result = await response.json()
        
        if (result.success) {
          ElMessage.success(result.message || '更新成功')
          // 通知父组件
          emit('logistics-company-updated')
          
          // 关闭当前编辑标签页并返回物流列表页
          // 先关闭当前标签页
          window.dispatchEvent(new CustomEvent('close-current-tab'))
          // 然后导航到物流列表页
          setTimeout(() => {
            window.dispatchEvent(new CustomEvent('navigate-to', {
              detail: { key: 'logistics-company-list' }
            }))
          }, 100)
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

// 重置表单
const resetForm = () => {
  if (formRef.value) {
    formRef.value.resetFields()
  }
}

// 组件挂载时加载数据
onMounted(() => {
  // 只有当 autoLoad 为 true 时才自动加载数据
  if (props.autoLoad) {
    initData()
  }
})

// 暴露方法给父组件调用
defineExpose({
  initData
})
</script>

<style scoped>
.logistics-company-edit-container {
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