<template>
  <div class="supplier-create">
    <!-- 页面头部 -->
    <div class="page-header">
      <h2 class="page-title">
        <el-icon class="title-icon"><OfficeBuilding /></el-icon>
        添加供应商
      </h2>
    </div>
    
    <el-card class="box-card">
      <template #header>
        <div class="card-header">
          <div class="card-header-title">
            <el-icon class="card-icon"><Plus /></el-icon>
            供应商信息
          </div>
        </div>
      </template>
      
      <el-form :model="supplierForm" :rules="rules" ref="formRef" label-width="120px">
        <!-- 账号信息 -->
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="用户名" prop="username">
              <el-input v-model="supplierForm.username" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="邮箱" prop="email">
              <el-input v-model="supplierForm.email" />
            </el-form-item>
          </el-col>
        </el-row>
        
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="密码" prop="password">
              <el-input v-model="supplierForm.password" type="password" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="确认密码" prop="confirmPassword">
              <el-input v-model="supplierForm.confirmPassword" type="password" />
            </el-form-item>
          </el-col>
        </el-row>
        
        <!-- 基本信息 -->
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="联系人" prop="contactPerson">
              <el-input v-model="supplierForm.contactPerson" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="联系电话" prop="contactPhone">
              <el-input v-model="supplierForm.contactPhone" />
            </el-form-item>
          </el-col>
        </el-row>
        
        <el-form-item label="联系地址">
          <el-input v-model="supplierForm.contactAddress" type="textarea" />
        </el-form-item>
        
        <!-- 供应商类型 -->
        <el-form-item label="供应商类型" prop="supplierType">
          <el-radio-group v-model="supplierForm.supplierType">
            <el-radio label="company">公司</el-radio>
            <el-radio label="individual">个人</el-radio>
          </el-radio-group>
        </el-form-item>
        
        <!-- 公司信息 -->
        <div v-if="supplierForm.supplierType === 'company'">
          <h3>公司信息</h3>
          <el-row :gutter="20">
            <el-col :span="12">
              <el-form-item label="公司名称" prop="companyName">
                <el-input v-model="supplierForm.companyName" />
              </el-form-item>
            </el-col>
            <el-col :span="12">
              <el-form-item label="公司类型">
                <el-select v-model="supplierForm.companyType" placeholder="请选择公司类型">
                  <el-option label="工厂" value="factory" />
                  <el-option label="贸易商" value="trader" />
                  <el-option label="工贸一体" value="factory_trader" />
                  <el-option label="品牌商" value="brand" />
                </el-select>
              </el-form-item>
            </el-col>
          </el-row>
          
          <el-row :gutter="20">
            <el-col :span="12">
              <el-form-item label="营业执照号" prop="businessLicenseNumber">
                <el-input v-model="supplierForm.businessLicenseNumber" />
              </el-form-item>
            </el-col>
            <el-col :span="12">
              <el-form-item label="法人姓名" prop="legalPersonName">
                <el-input v-model="supplierForm.legalPersonName" />
              </el-form-item>
            </el-col>
          </el-row>
          
          <el-form-item label="法人身份证号" prop="legalPersonIdCard">
            <el-input v-model="supplierForm.legalPersonIdCard" />
          </el-form-item>
          
          <!-- 证件上传 - 在一行显示 -->
          <el-row :gutter="20">
            <el-col :span="8">
              <el-form-item label="营业执照">
                <file-upload
                  v-model="supplierForm.businessLicenseImage"
                  :admin-login-path="adminLoginPath"
                  accept="image/*"
                  :max-size="5"
                  endpoint-type="supplier"
                />
              </el-form-item>
            </el-col>
            <el-col :span="8">
              <el-form-item label="法人身份证正面">
                <file-upload
                  v-model="supplierForm.legalPersonIdFront"
                  :admin-login-path="adminLoginPath"
                  accept="image/*"
                  :max-size="5"
                  endpoint-type="supplier"
                />
              </el-form-item>
            </el-col>
            <el-col :span="8">
              <el-form-item label="法人身份证反面">
                <file-upload
                  v-model="supplierForm.legalPersonIdBack"
                  :admin-login-path="adminLoginPath"
                  accept="image/*"
                  :max-size="5"
                  endpoint-type="supplier"
                />
              </el-form-item>
            </el-col>
          </el-row>
        </div>
        
        <!-- 个人信息 -->
        <div v-if="supplierForm.supplierType === 'individual'">
          <h3>个人信息</h3>
          <el-row :gutter="20">
            <el-col :span="12">
              <el-form-item label="真实姓名" prop="individualName">
                <el-input v-model="supplierForm.individualName" />
              </el-form-item>
            </el-col>
            <el-col :span="12">
              <el-form-item label="身份证号" prop="individualIdCard">
                <el-input v-model="supplierForm.individualIdCard" />
              </el-form-item>
            </el-col>
          </el-row>
          
          <!-- 个人身份证正反面上传 - 在一行显示 -->
          <el-row :gutter="20">
            <el-col :span="12">
              <el-form-item label="身份证正面">
                <file-upload
                  v-model="supplierForm.individualIdFront"
                  :admin-login-path="adminLoginPath"
                  accept="image/*"
                  :max-size="5"
                  endpoint-type="supplier"
                />
              </el-form-item>
            </el-col>
            <el-col :span="12">
              <el-form-item label="身份证反面">
                <file-upload
                  v-model="supplierForm.individualIdBack"
                  :admin-login-path="adminLoginPath"
                  accept="image/*"
                  :max-size="5"
                  endpoint-type="supplier"
                />
              </el-form-item>
            </el-col>
          </el-row>
        </div>
        
        <!-- 业务信息 -->
        <h3>业务信息</h3>
        <el-form-item label="主营业务">
          <el-input v-model="supplierForm.mainCategory" type="textarea" />
        </el-form-item>
        
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="跨境经验">
              <el-switch v-model="supplierForm.hasCrossBorderExperience" active-text="有" inactive-text="无" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="年销售额(万元)">
              <el-input v-model="supplierForm.annualSalesVolume" type="number" />
            </el-form-item>
          </el-col>
        </el-row>
        
        <el-form-item>
          <el-button type="primary" @click="submitForm">创建供应商</el-button>
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
  ElRadioGroup,
  ElRadio,
  ElSelect,
  ElOption,
  ElSwitch,
  ElButton,
  ElMessage,
  ElIcon
} from 'element-plus'
import { OfficeBuilding, Plus } from '@element-plus/icons-vue'
import FileUpload from './FileUpload.vue'

// 定义组件属性
const props = defineProps({
  adminLoginPath: {
    type: String,
    default: ''
  }
})

// 定义事件
const emit = defineEmits(['supplier-created'])

// 表单引用
const formRef = ref()

// 提交状态
const submitting = ref(false)

// 供应商表单数据
const supplierForm = reactive({
  username: '',
  email: '',
  password: '',
  confirmPassword: '',
  contactPerson: '',
  contactPhone: '',
  contactAddress: '',
  supplierType: 'company',
  companyName: '',
  companyType: '',
  businessLicenseNumber: '',
  businessLicenseImage: '',
  legalPersonName: '',
  legalPersonIdCard: '',
  legalPersonIdFront: '',
  legalPersonIdBack: '',
  individualName: '',
  individualIdCard: '',
  individualIdFront: '',
  individualIdBack: '',
  mainCategory: '',
  hasCrossBorderExperience: false,
  annualSalesVolume: '',
  warehouseAddress: ''
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
        if (value !== supplierForm.password) {
          callback(new Error('两次输入的密码不一致'))
        } else {
          callback()
        }
      },
      trigger: 'blur'
    }
  ],
  contactPerson: [
    { required: true, message: '请输入联系人', trigger: 'blur' }
  ],
  contactPhone: [
    { required: true, message: '请输入联系电话', trigger: 'blur' }
  ],
  companyName: [
    { 
      required: true, 
      message: '请输入公司名称', 
      trigger: 'blur',
      validator: (rule, value, callback) => {
        if (supplierForm.supplierType === 'company' && !value) {
          callback(new Error('请输入公司名称'))
        } else {
          callback()
        }
      }
    }
  ],
  businessLicenseNumber: [
    { 
      required: true, 
      message: '请输入营业执照号', 
      trigger: 'blur',
      validator: (rule, value, callback) => {
        if (supplierForm.supplierType === 'company' && !value) {
          callback(new Error('请输入营业执照号'))
        } else {
          callback()
        }
      }
    }
  ],
  legalPersonName: [
    { 
      required: true, 
      message: '请输入法人姓名', 
      trigger: 'blur',
      validator: (rule, value, callback) => {
        if (supplierForm.supplierType === 'company' && !value) {
          callback(new Error('请输入法人姓名'))
        } else {
          callback()
        }
      }
    }
  ],
  legalPersonIdCard: [
    { 
      required: true, 
      message: '请输入法人身份证号', 
      trigger: 'blur',
      validator: (rule, value, callback) => {
        if (supplierForm.supplierType === 'company' && !value) {
          callback(new Error('请输入法人身份证号'))
        } else {
          callback()
        }
      }
    }
  ],
  individualName: [
    { 
      required: true, 
      message: '请输入真实姓名', 
      trigger: 'blur',
      validator: (rule, value, callback) => {
        if (supplierForm.supplierType === 'individual' && !value) {
          callback(new Error('请输入真实姓名'))
        } else {
          callback()
        }
      }
    }
  ],
  individualIdCard: [
    { 
      required: true, 
      message: '请输入身份证号', 
      trigger: 'blur',
      validator: (rule, value, callback) => {
        if (supplierForm.supplierType === 'individual' && !value) {
          callback(new Error('请输入身份证号'))
        } else {
          callback()
        }
      }
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
        const response = await fetch(`/admin${props.adminLoginPath}/supplier/create`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: JSON.stringify(supplierForm)
        })
        
        const result = await response.json()
        
        if (result.success) {
          ElMessage.success(result.message)
          emit('supplier-created', result.supplier)
          // 重置表单
          resetForm()
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
  
  // 重置文件上传组件
  const fileUploadComponents = document.querySelectorAll('file-upload')
  fileUploadComponents.forEach(component => {
    if (component.clearFile) {
      component.clearFile()
    }
  })
}
</script>

<style scoped>
.supplier-create {
  width: 100%;
  max-width: 1200px;
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

h3 {
  margin: 20px 0 10px 0;
  padding-bottom: 10px;
  border-bottom: 1px solid #ebeef5;
  color: #606266;
}
</style>