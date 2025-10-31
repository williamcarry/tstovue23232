<template>
  <div class="supplier-profile-container">
    <!-- 页面头部 -->
    <div class="page-header">
      <h2 class="page-title">
        <el-icon class="title-icon"><User /></el-icon>
        个人信息
      </h2>
    </div>
    
    <el-card class="profile-card" shadow="hover">
      <template #header>
        <div class="card-header">
          <div class="card-header-title">
            <el-icon class="card-icon"><Edit /></el-icon>
            编辑个人信息
          </div>
        </div>
      </template>
      
      <el-form 
        :model="formData" 
        label-width="120px" 
        v-loading="loading"
        label-position="left"
        class="profile-form"
      >
        <!-- 账号信息 -->
        <el-divider content-position="left">
          <span class="divider-title">
            <el-icon><User /></el-icon>
            账号信息
          </span>
        </el-divider>
        <el-row :gutter="30">
          <el-col :span="12">
            <el-form-item label="供应商ID">
              <el-input v-model="formData.id" disabled />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="用户名">
              <el-input v-model="formData.username" disabled />
            </el-form-item>
          </el-col>
        </el-row>
        
        <el-row :gutter="30">
          <el-col :span="12">
            <el-form-item label="邮箱">
              <el-input 
                v-model="formData.email" 
                placeholder="请输入邮箱地址"
                clearable
              />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="供应商类型">
              <el-select v-model="formData.supplierType" disabled>
                <el-option label="公司" value="company" />
                <el-option label="个人" value="individual" />
              </el-select>
            </el-form-item>
          </el-col>
        </el-row>
        
        <el-row :gutter="30">
          <el-col :span="12">
            <el-form-item label="显示名称">
              <el-input 
                v-model="formData.displayName" 
                placeholder="请输入显示名称"
                clearable
              />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="注册时间">
              <el-input v-model="formData.createdAt" disabled />
            </el-form-item>
          </el-col>
        </el-row>
        
        <el-row :gutter="30">
          <el-col :span="12">
            <el-form-item label="最后登录时间">
              <el-input v-model="formData.lastLoginAt" disabled />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="最后更新时间">
              <el-input v-model="formData.updatedAt" disabled />
            </el-form-item>
          </el-col>
        </el-row>
        
        <!-- 基本信息（可编辑） -->
        <el-divider content-position="left">
          <span class="divider-title">
            <el-icon><InfoFilled /></el-icon>
            基本信息
          </span>
        </el-divider>
        <el-row :gutter="30">
          <el-col :span="12">
            <el-form-item label="联系人">
              <el-input 
                v-model="formData.contactPerson" 
                placeholder="请输入联系人姓名"
                clearable
              />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="联系电话">
              <el-input 
                v-model="formData.contactPhone" 
                placeholder="请输入联系电话"
                clearable
              />
            </el-form-item>
          </el-col>
        </el-row>
        
        <el-form-item label="联系地址">
          <el-input 
            v-model="formData.contactAddress" 
            type="textarea"
            placeholder="请输入联系地址"
            :rows="3"
            clearable
          />
        </el-form-item>
        
        <!-- 公司信息（根据审核状态决定是否可编辑） -->
        <div v-if="formData.supplierType === 'company'">
          <el-divider content-position="left">
            <span class="divider-title">
              <el-icon><OfficeBuilding /></el-icon>
              公司信息
            </span>
          </el-divider>
          <el-row :gutter="30">
            <el-col :span="12">
              <el-form-item label="公司名称">
                <el-input 
                  v-model="formData.companyName" 
                  :disabled="shouldDisableFields"
                  placeholder="请输入公司名称"
                  clearable
                />
              </el-form-item>
            </el-col>
            <el-col :span="12">
              <el-form-item label="公司类型">
                <el-select 
                  v-model="formData.companyType" 
                  :disabled="shouldDisableFields"
                  placeholder="请选择公司类型"
                >
                  <el-option label="工厂" value="factory" />
                  <el-option label="贸易商" value="trader" />
                  <el-option label="工贸一体" value="factory_trader" />
                  <el-option label="品牌商" value="brand" />
                </el-select>
              </el-form-item>
            </el-col>
          </el-row>
          
          <el-row :gutter="30">
            <el-col :span="12">
              <el-form-item label="营业执照号">
                <el-input 
                  v-model="formData.businessLicenseNumber" 
                  :disabled="shouldDisableFields"
                  placeholder="请输入营业执照号"
                  clearable
                />
              </el-form-item>
            </el-col>
            <el-col :span="12">
              <el-form-item label="法人姓名">
                <el-input 
                  v-model="formData.legalPersonName" 
                  :disabled="shouldDisableFields"
                  placeholder="请输入法人姓名"
                  clearable
                />
              </el-form-item>
            </el-col>
          </el-row>
          
          <el-row :gutter="30">
            <el-col :span="12">
              <el-form-item label="法人身份证号">
                <el-input 
                  v-model="formData.legalPersonIdCard" 
                  :disabled="shouldDisableFields"
                  placeholder="请输入法人身份证号"
                  clearable
                />
              </el-form-item>
            </el-col>
            <el-col :span="12">
              <el-form-item label="注册资本(万元)">
                <el-input 
                  v-model="formData.registeredCapital" 
                  :disabled="shouldDisableFields"
                  placeholder="请输入注册资本"
                  clearable
                />
              </el-form-item>
            </el-col>
          </el-row>
          
          <el-row :gutter="30">
            <el-col :span="12">
              <el-form-item label="公司成立日期">
                <el-input 
                  v-model="formData.establishmentDate" 
                  :disabled="shouldDisableFields"
                  placeholder="请输入公司成立日期"
                  clearable
                />
              </el-form-item>
            </el-col>
            <el-col :span="12">
              <el-form-item label="税务登记号">
                <el-input 
                  v-model="formData.taxNumber" 
                  :disabled="shouldDisableFields"
                  placeholder="请输入税务登记号"
                  clearable
                />
              </el-form-item>
            </el-col>
          </el-row>
          
          <el-form-item label="经营范围">
            <el-input 
              v-model="formData.businessScope" 
              type="textarea"
              :disabled="shouldDisableFields"
              placeholder="请输入经营范围"
              :rows="3"
              clearable
            />
          </el-form-item>
          
          <el-row :gutter="30">
            <el-col :span="12">
              <el-form-item label="银行开户名称">
                <el-input 
                  v-model="formData.bankAccountName" 
                  placeholder="请输入银行开户名称"
                  clearable
                />
              </el-form-item>
            </el-col>
            <el-col :span="12">
              <el-form-item label="银行账号">
                <el-input 
                  v-model="formData.bankAccountNumber" 
                  placeholder="请输入银行账号"
                  clearable
                />
              </el-form-item>
            </el-col>
          </el-row>
          
          <el-row :gutter="30">
            <el-col :span="12">
              <el-form-item label="开户银行">
                <el-input 
                  v-model="formData.bankName" 
                  placeholder="请输入开户银行"
                  clearable
                />
              </el-form-item>
            </el-col>
            <el-col :span="12">
              <el-form-item label="开户支行">
                <el-input 
                  v-model="formData.bankBranch" 
                  placeholder="请输入开户支行"
                  clearable
                />
              </el-form-item>
            </el-col>
          </el-row>
          
          <!-- 公司证件图片(根据审核状态决定是否可编辑) -->
          <el-divider content-position="left">
            <span class="divider-title">
              <el-icon><Picture /></el-icon>
              公司证件图片
            </span>
          </el-divider>
          <el-row :gutter="30">
            <el-col :span="8">
              <div class="image-upload-container">
                <div class="image-label">营业执照</div>
                <div v-if="shouldDisableFields" class="image-preview">
                  <el-image 
                    v-if="formData.businessLicenseImageUrl" 
                    :src="formData.businessLicenseImageUrl" 
                    fit="contain" 
                    :preview-src-list="[formData.businessLicenseImageUrl]" 
                    class="preview-image"
                  />
                  <span v-else class="no-image">暂无图片</span>
                </div>
                <file-upload
                  v-else
                  v-model="formData.businessLicenseImage"
                  :supplier-login-path="props.supplierLoginPath"
                  accept="image/*"
                  :max-size="5"
                  :show-remove="false"
                />
                <div v-if="!shouldDisableFields" class="image-upload-note">审核未通过时可重新上传</div>
              </div>
            </el-col>
            <el-col :span="8">
              <div class="image-upload-container">
                <div class="image-label">法人身份证正面</div>
                <div v-if="shouldDisableFields" class="image-preview">
                  <el-image 
                    v-if="formData.legalPersonIdFrontUrl" 
                    :src="formData.legalPersonIdFrontUrl" 
                    fit="contain" 
                    :preview-src-list="[formData.legalPersonIdFrontUrl]" 
                    class="preview-image"
                  />
                  <span v-else class="no-image">暂无图片</span>
                </div>
                <file-upload
                  v-else
                  v-model="formData.legalPersonIdFront"
                  :supplier-login-path="props.supplierLoginPath"
                  accept="image/*"
                  :max-size="5"
                  :show-remove="false"
                />
                <div v-if="!shouldDisableFields" class="image-upload-note">审核未通过时可重新上传</div>
              </div>
            </el-col>
            <el-col :span="8">
              <div class="image-upload-container">
                <div class="image-label">法人身份证反面</div>
                <div v-if="shouldDisableFields" class="image-preview">
                  <el-image 
                    v-if="formData.legalPersonIdBackUrl" 
                    :src="formData.legalPersonIdBackUrl" 
                    fit="contain" 
                    :preview-src-list="[formData.legalPersonIdBackUrl]" 
                    class="preview-image"
                  />
                  <span v-else class="no-image">暂无图片</span>
                </div>
                <file-upload
                  v-else
                  v-model="formData.legalPersonIdBack"
                  :supplier-login-path="props.supplierLoginPath"
                  accept="image/*"
                  :max-size="5"
                  :show-remove="false"
                />
                <div v-if="!shouldDisableFields" class="image-upload-note">审核未通过时可重新上传</div>
              </div>
            </el-col>
          </el-row>
        </div>
        
        <!-- 个人信息（根据审核状态决定是否可编辑） -->
        <div v-if="formData.supplierType === 'individual'">
          <el-divider content-position="left">
            <span class="divider-title">
              <el-icon><User /></el-icon>
              个人信息
            </span>
          </el-divider>
          <el-row :gutter="30">
            <el-col :span="12">
              <el-form-item label="真实姓名">
                <el-input 
                  v-model="formData.individualName" 
                  :disabled="shouldDisableFields"
                  placeholder="请输入真实姓名"
                  clearable
                />
              </el-form-item>
            </el-col>
            <el-col :span="12">
              <el-form-item label="身份证号">
                <el-input 
                  v-model="formData.individualIdCard" 
                  :disabled="shouldDisableFields"
                  placeholder="请输入身份证号"
                  clearable
                />
              </el-form-item>
            </el-col>
          </el-row>
          
          <el-row :gutter="30">
            <el-col :span="12">
              <el-form-item label="个人银行账号">
                <el-input 
                  v-model="formData.individualBankAccount" 
                  placeholder="请输入个人银行账号"
                  clearable
                />
              </el-form-item>
            </el-col>
            <el-col :span="12">
              <el-form-item label="个人开户银行">
                <el-input 
                  v-model="formData.individualBankName" 
                  placeholder="请输入个人开户银行"
                  clearable
                />
              </el-form-item>
            </el-col>
          </el-row>
          
          <!-- 个人证件图片(根据审核状态决定是否可编辑) -->
          <el-divider content-position="left">
            <span class="divider-title">
              <el-icon><Picture /></el-icon>
              个人证件图片
            </span>
          </el-divider>
          <el-row :gutter="30">
            <el-col :span="12">
              <div class="image-upload-container">
                <div class="image-label">身份证正面</div>
                <div v-if="shouldDisableFields" class="image-preview">
                  <el-image 
                    v-if="formData.individualIdFrontUrl" 
                    :src="formData.individualIdFrontUrl" 
                    fit="contain" 
                    :preview-src-list="[formData.individualIdFrontUrl]" 
                    class="preview-image"
                  />
                  <span v-else class="no-image">暂无图片</span>
                </div>
                <file-upload
                  v-else
                  v-model="formData.individualIdFront"
                  :supplier-login-path="props.supplierLoginPath"
                  accept="image/*"
                  :max-size="5"
                  :show-remove="false"
                />
                <div v-if="!shouldDisableFields" class="image-upload-note">审核未通过时可重新上传</div>
              </div>
            </el-col>
            <el-col :span="12">
              <div class="image-upload-container">
                <div class="image-label">身份证反面</div>
                <div v-if="shouldDisableFields" class="image-preview">
                  <el-image 
                    v-if="formData.individualIdBackUrl" 
                    :src="formData.individualIdBackUrl" 
                    fit="contain" 
                    :preview-src-list="[formData.individualIdBackUrl]" 
                    class="preview-image"
                  />
                  <span v-else class="no-image">暂无图片</span>
                </div>
                <file-upload
                  v-else
                  v-model="formData.individualIdBack"
                  :supplier-login-path="props.supplierLoginPath"
                  accept="image/*"
                  :max-size="5"
                  :show-remove="false"
                />
                <div v-if="!shouldDisableFields" class="image-upload-note">审核未通过时可重新上传</div>
              </div>
            </el-col>
          </el-row>
        </div>
        
        <!-- 业务信息（可编辑） -->
        <el-divider content-position="left">
          <span class="divider-title">
            <el-icon><TrendCharts /></el-icon>
            业务信息
          </span>
        </el-divider>
        <el-form-item label="主营业务">
          <el-input 
            v-model="formData.mainCategory" 
            type="textarea"
            placeholder="请输入主营业务"
            :rows="3"
            clearable
          />
        </el-form-item>
        
        <el-row :gutter="30">
          <el-col :span="12">
            <el-form-item label="跨境经验">
              <el-switch v-model="formData.hasCrossBorderExperience" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="年销售额(万元)">
              <el-input 
                v-model="formData.annualSalesVolume" 
                placeholder="请输入年销售额"
                clearable
              />
            </el-form-item>
          </el-col>
        </el-row>
        
        <el-form-item label="仓库地址">
          <el-input 
            v-model="formData.warehouseAddress" 
            type="textarea"
            placeholder="请输入仓库地址"
            :rows="3"
            clearable
          />
        </el-form-item>
        
        <!-- 审核信息（只读） -->
        <el-divider content-position="left">
          <span class="divider-title">
            <el-icon><DocumentChecked /></el-icon>
            审核信息（只读）
          </span>
        </el-divider>
        <el-row :gutter="30">
          <el-col :span="12">
            <el-form-item label="审核状态">
              <el-select v-model="formData.auditStatus" disabled>
                <el-option label="待审核" value="pending" />
                <el-option label="已通过" value="approved" />
                <el-option label="已拒绝" value="rejected" />
                <el-option label="待重新提交" value="resubmit" />
              </el-select>
            </el-form-item>
          </el-col>
        </el-row>
        
        <el-form-item label="审核备注" v-if="formData.auditRemark">
          <el-input 
            v-model="formData.auditRemark" 
            type="textarea" 
            disabled
            :rows="3"
          />
        </el-form-item>
        
        <!-- 修改密码 -->
        <el-divider content-position="left">
          <span class="divider-title">
            <el-icon><Lock /></el-icon>
            修改密码
          </span>
        </el-divider>
        <el-row :gutter="30">
          <el-col :span="12">
            <el-form-item label="新密码">
              <el-input 
                v-model="passwordData.newPassword" 
                type="password" 
                show-password 
                placeholder="至少8个字符，不修改请留空"
                clearable
              />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="确认新密码">
              <el-input 
                v-model="passwordData.confirmPassword" 
                type="password" 
                show-password 
                placeholder="再次输入新密码"
                clearable
              />
            </el-form-item>
          </el-col>
        </el-row>
        
        <!-- 提交按钮 -->
        <el-form-item class="form-actions">
          <el-button 
            type="primary" 
            @click="handleSave" 
            :loading="saving"
            size="large"
            round
          >
            <el-icon><Check /></el-icon>
            保存修改
          </el-button>
          <el-button 
            @click="handleReset"
            size="large"
            round
          >
            <el-icon><Refresh /></el-icon>
            重置
          </el-button>
        </el-form-item>
      </el-form>
    </el-card>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import {
  ElCard,
  ElForm,
  ElFormItem,
  ElInput,
  ElSelect,
  ElOption,
  ElSwitch,
  ElDivider,
  ElRow,
  ElCol,
  ElButton,
  ElMessage,
  ElImage,
  ElIcon
} from 'element-plus'
import {
  User,
  Edit,
  InfoFilled,
  OfficeBuilding,
  Picture,
  TrendCharts,
  DocumentChecked,
  Lock,
  Check,
  Refresh
} from '@element-plus/icons-vue'

// 导入FileUpload组件
import FileUpload from '../admin/FileUpload.vue'

const props = defineProps({
  supplierLoginPath: {
    type: String,
    default: ''
  },
  autoLoad: {
    type: Boolean,
    default: false
  }
})

const loading = ref(false)
const saving = ref(false)
const formData = reactive({
  id: null,
  username: '',
  email: '',
  supplierType: 'company',
  displayName: '',
  createdAt: '',
  lastLoginAt: '',
  updatedAt: '',
  contactPerson: '',
  contactPhone: '',
  contactAddress: '',
  // 公司信息
  companyName: '',
  companyType: '',
  businessLicenseNumber: '',
  businessLicenseImage: '',
  businessLicenseImageUrl: '',
  legalPersonName: '',
  legalPersonIdCard: '',
  legalPersonIdFront: '',
  legalPersonIdFrontUrl: '',
  legalPersonIdBack: '',
  legalPersonIdBackUrl: '',
  registeredCapital: '',
  establishmentDate: '',
  businessScope: '',
  bankAccountName: '',
  bankAccountNumber: '',
  bankName: '',
  bankBranch: '',
  taxNumber: '',
  // 个人信息
  individualName: '',
  individualIdCard: '',
  individualIdFront: '',
  individualIdFrontUrl: '',
  individualIdBack: '',
  individualIdBackUrl: '',
  individualBankAccount: '',
  individualBankName: '',
  // 业务信息
  mainCategory: '',
  hasCrossBorderExperience: false,
  annualSalesVolume: '',
  warehouseAddress: '',
  // 审核信息
  auditStatus: 'pending',
  auditRemark: ''
})

const passwordData = reactive({
  newPassword: '',
  confirmPassword: ''
})

// 计算属性：判断审核状态是否为"approved"
const isApproved = computed(() => {
  return formData.auditStatus === 'approved';
});

// 计算属性：判断字段是否应该禁用（审核状态为approved或pending时禁用）
const shouldDisableFields = computed(() => {
  return formData.auditStatus === 'approved' || formData.auditStatus === 'pending';
});

// 原始数据备份，用于重置
let originalData = {}

const loadProfileData = async () => {
  loading.value = true
  try {
    const loginPath = props.supplierLoginPath || window.supplierLoginPath || ''
    const response = await fetch(`/supplier${loginPath}/profile/data`, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    
    const result = await response.json()
    
    if (result.success) {
      Object.assign(formData, result.data)
      // 保存原始数据
      originalData = JSON.parse(JSON.stringify(result.data))
    } else {
      ElMessage.error(result.message || '加载数据失败')
    }
  } catch (error) {
    ElMessage.error('加载数据失败: ' + error.message)
  } finally {
    loading.value = false
  }
}



const handleSave = async () => {
  // 验证密码
  if (passwordData.newPassword || passwordData.confirmPassword) {
    if (!passwordData.newPassword) {
      ElMessage.warning('请输入新密码')
      return
    }
    if (passwordData.newPassword.length < 8) {
      ElMessage.warning('新密码至少需要8个字符')
      return
    }
    if (passwordData.newPassword !== passwordData.confirmPassword) {
      ElMessage.warning('两次输入的新密码不一致')
      return
    }
  }
  
  saving.value = true
  try {
    const loginPath = props.supplierLoginPath || window.supplierLoginPath || ''
    
    // 构建提交数据，包含所有图片字段的key
    const submitData = {
      email: formData.email,
      contactPerson: formData.contactPerson,
      contactPhone: formData.contactPhone,
      contactAddress: formData.contactAddress,
      displayName: formData.displayName,
      companyName: formData.companyName,
      companyType: formData.companyType,
      businessLicenseNumber: formData.businessLicenseNumber,
      legalPersonName: formData.legalPersonName,
      legalPersonIdCard: formData.legalPersonIdCard,
      registeredCapital: formData.registeredCapital,
      establishmentDate: formData.establishmentDate,
      businessScope: formData.businessScope,
      taxNumber: formData.taxNumber,
      individualName: formData.individualName,
      individualIdCard: formData.individualIdCard,
      bankAccountName: formData.bankAccountName,
      bankAccountNumber: formData.bankAccountNumber,
      bankName: formData.bankName,
      bankBranch: formData.bankBranch,
      individualBankAccount: formData.individualBankAccount,
      individualBankName: formData.individualBankName,
      mainCategory: formData.mainCategory,
      annualSalesVolume: formData.annualSalesVolume,
      warehouseAddress: formData.warehouseAddress,
      newPassword: passwordData.newPassword,
      confirmPassword: passwordData.confirmPassword,
      // 关键：提交图片字段的key值，不是URL
      businessLicenseImage: formData.businessLicenseImage || '',
      legalPersonIdFront: formData.legalPersonIdFront || '',
      legalPersonIdBack: formData.legalPersonIdBack || '',
      individualIdFront: formData.individualIdFront || '',
      individualIdBack: formData.individualIdBack || ''
    }
    
    console.log('[SupplierProfileEdit] Submitting data:', submitData)
    
    const response = await fetch(`/supplier${loginPath}/profile/update`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify(submitData)
    })
    
    const result = await response.json()
    
    if (result.success) {
      ElMessage.success(result.message)
      // 清空密码字段
      passwordData.newPassword = ''
      passwordData.confirmPassword = ''
      // 重新加载数据
      await loadProfileData()
    } else {
      ElMessage.error(result.error || '保存失败')
    }
  } catch (error) {
    ElMessage.error('保存失败: ' + error.message)
  } finally {
    saving.value = false
  }
}

const handleReset = () => {
  Object.assign(formData, originalData)
  passwordData.newPassword = ''
  passwordData.confirmPassword = ''
  ElMessage.info('已重置')
}

// 暴露方法供父组件调用
defineExpose({
  loadProfileData
})

onMounted(() => {
  if (props.autoLoad) {
    loadProfileData()
  }
})
</script>

<style scoped>
.supplier-profile-container {
  width: 100%;
  max-width: 1400px;
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

.profile-form :deep(.el-textarea__inner) {
  border-radius: 6px;
  transition: all 0.3s ease;
}

.profile-form :deep(.el-textarea__inner:hover) {
  border-color: #409eff;
}

.profile-form :deep(.el-textarea__inner:focus) {
  border-color: #409eff;
  box-shadow: 0 0 0 2px rgba(64, 158, 255, 0.2);
}

.profile-form :deep(.el-divider__text) {
  font-size: 16px;
  font-weight: 500;
  color: #303133;
}

.divider-title {
  display: flex;
  align-items: center;
  font-weight: 600;
  color: #409eff;
}

.divider-title .el-icon {
  margin-right: 8px;
  font-size: 18px;
}

/* 图片上传区域容器 - 文字在上，图片在下，垂直居中 */
.image-upload-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  width: 100%;
  padding: 15px;
  background-color: #f8f9fa;
  border-radius: 8px;
  transition: all 0.3s ease;
}

.image-upload-container:hover {
  background-color: #e9ecef;
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.image-label {
  text-align: center;
  margin-bottom: 10px;
  font-size: 14px;
  font-weight: 500;
  color: #495057;
}

.image-preview {
  width: 200px;
  height: 150px;
  border: 2px dashed #d9d9d9;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  background-color: #ffffff;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
}

.image-preview :deep(.el-image) {
  width: 100%;
  height: 100%;
}

.preview-image {
  transition: transform 0.3s ease;
}

.preview-image:hover {
  transform: scale(1.05);
}

.no-image {
  color: #909399;
  font-size: 14px;
}

.image-upload-note {
  font-size: 12px;
  color: #909399;
  margin-top: 5px;
  text-align: center;
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