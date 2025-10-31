<template>
  <div class="supplier-edit" v-loading="loading">
    <el-card class="box-card">
      <template #header>
        <div class="card-header">
          <span>编辑供应商</span>
        </div>
      </template>
      
      <el-form :model="formData" label-width="120px">
        <!-- 账号信息 -->
        <el-divider content-position="left">账号信息</el-divider>
        <el-row :gutter="20">
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
        
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="邮箱">
              <el-input v-model="formData.email" />
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
        
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="显示名称">
              <el-input v-model="formData.displayName" disabled />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="注册时间">
              <el-input v-model="formData.createdAt" disabled />
            </el-form-item>
          </el-col>
        </el-row>
        
        <el-row :gutter="20">
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
        
        <!-- 基本信息 -->
        <el-divider content-position="left">基本信息</el-divider>
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="联系人">
              <el-input v-model="formData.contactPerson" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="联系电话">
              <el-input v-model="formData.contactPhone" />
            </el-form-item>
          </el-col>
        </el-row>
        
        <el-form-item label="联系地址">
          <el-input v-model="formData.contactAddress" type="textarea" />
        </el-form-item>
        
        <!-- 公司信息 -->
        <div v-if="formData.supplierType === 'company'">
          <el-divider content-position="left">公司信息</el-divider>
          <el-row :gutter="20">
            <el-col :span="12">
              <el-form-item label="公司名称">
                <el-input v-model="formData.companyName" />
              </el-form-item>
            </el-col>
            <el-col :span="12">
              <el-form-item label="公司类型">
                <el-select v-model="formData.companyType">
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
              <el-form-item label="营业执照号">
                <el-input v-model="formData.businessLicenseNumber" />
              </el-form-item>
            </el-col>
            <el-col :span="12">
              <el-form-item label="法人姓名">
                <el-input v-model="formData.legalPersonName" />
              </el-form-item>
            </el-col>
          </el-row>
          
          <el-row :gutter="20">
            <el-col :span="12">
              <el-form-item label="法人身份证号">
                <el-input v-model="formData.legalPersonIdCard" />
              </el-form-item>
            </el-col>
            <el-col :span="12">
              <el-form-item label="注册资本(万元)">
                <el-input v-model="formData.registeredCapital" />
              </el-form-item>
            </el-col>
          </el-row>
          
          <el-row :gutter="20">
            <el-col :span="12">
              <el-form-item label="公司成立日期">
                <el-input v-model="formData.establishmentDate" />
              </el-form-item>
            </el-col>
            <el-col :span="12">
              <el-form-item label="税务登记号">
                <el-input v-model="formData.taxNumber" />
              </el-form-item>
            </el-col>
          </el-row>
          
          <el-form-item label="经营范围">
            <el-input v-model="formData.businessScope" type="textarea" />
          </el-form-item>
          
          <el-row :gutter="20">
            <el-col :span="12">
              <el-form-item label="银行开户名称">
                <el-input v-model="formData.bankAccountName" />
              </el-form-item>
            </el-col>
            <el-col :span="12">
              <el-form-item label="银行账号">
                <el-input v-model="formData.bankAccountNumber" />
              </el-form-item>
            </el-col>
          </el-row>
          
          <el-row :gutter="20">
            <el-col :span="12">
              <el-form-item label="开户银行">
                <el-input v-model="formData.bankName" />
              </el-form-item>
            </el-col>
            <el-col :span="12">
              <el-form-item label="开户支行">
                <el-input v-model="formData.bankBranch" />
              </el-form-item>
            </el-col>
          </el-row>
          
          <!-- 公司证件图片 -->
          <el-divider content-position="left">公司证件图片</el-divider>
          <el-row :gutter="20">
            <el-col :span="8">
              <div class="image-upload-container">
                <div class="image-label">营业执照</div>
                <file-upload
                  :key="'business-license-' + formData.id"
                  :model-value="formData.businessLicenseImage"
                  @update:model-value="val => { formData.businessLicenseImage = val; console.log('[SupplierEditTab] businessLicenseImage updated:', val) }"
                  :admin-login-path="adminLoginPath"
                  accept="image/*"
                  :max-size="5"
                  endpoint-type="supplier"
                />
              </div>
            </el-col>
            <el-col :span="8">
              <div class="image-upload-container">
                <div class="image-label">法人身份证正面</div>
                <file-upload
                  :key="'legal-id-front-' + formData.id"
                  :model-value="formData.legalPersonIdFront"
                  @update:model-value="val => { formData.legalPersonIdFront = val; console.log('[SupplierEditTab] legalPersonIdFront updated:', val) }"
                  :admin-login-path="adminLoginPath"
                  accept="image/*"
                  :max-size="5"
                  endpoint-type="supplier"
                />
              </div>
            </el-col>
            <el-col :span="8">
              <div class="image-upload-container">
                <div class="image-label">法人身份证反面</div>
                <file-upload
                  :key="'legal-id-back-' + formData.id"
                  :model-value="formData.legalPersonIdBack"
                  @update:model-value="val => { formData.legalPersonIdBack = val; console.log('[SupplierEditTab] legalPersonIdBack updated:', val) }"
                  :admin-login-path="adminLoginPath"
                  accept="image/*"
                  :max-size="5"
                  endpoint-type="supplier"
                />
              </div>
            </el-col>
          </el-row>
        </div>
        
        <!-- 个人信息 -->
        <div v-if="formData.supplierType === 'individual'">
          <el-divider content-position="left">个人信息</el-divider>
          <el-row :gutter="20">
            <el-col :span="12">
              <el-form-item label="真实姓名">
                <el-input v-model="formData.individualName" />
              </el-form-item>
            </el-col>
            <el-col :span="12">
              <el-form-item label="身份证号">
                <el-input v-model="formData.individualIdCard" />
              </el-form-item>
            </el-col>
          </el-row>
          
          <el-row :gutter="20">
            <el-col :span="12">
              <el-form-item label="个人银行账号">
                <el-input v-model="formData.individualBankAccount" />
              </el-form-item>
            </el-col>
            <el-col :span="12">
              <el-form-item label="个人开户银行">
                <el-input v-model="formData.individualBankName" />
              </el-form-item>
            </el-col>
          </el-row>
          
          <!-- 个人证件图片 -->
          <el-divider content-position="left">个人证件图片</el-divider>
          <el-row :gutter="20">
            <el-col :span="12">
              <div class="image-upload-container">
                <div class="image-label">身份证正面</div>
                <file-upload
                  :key="'individual-id-front-' + formData.id"
                  :model-value="formData.individualIdFront"
                  @update:model-value="val => { formData.individualIdFront = val; console.log('[SupplierEditTab] individualIdFront updated:', val) }"
                  :admin-login-path="adminLoginPath"
                  accept="image/*"
                  :max-size="5"
                  endpoint-type="supplier"
                />
              </div>
            </el-col>
            <el-col :span="12">
              <div class="image-upload-container">
                <div class="image-label">身份证反面</div>
                <file-upload
                  :key="'individual-id-back-' + formData.id"
                  :model-value="formData.individualIdBack"
                  @update:model-value="val => { formData.individualIdBack = val; console.log('[SupplierEditTab] individualIdBack updated:', val) }"
                  :admin-login-path="adminLoginPath"
                  accept="image/*"
                  :max-size="5"
                  endpoint-type="supplier"
                />
              </div>
            </el-col>
          </el-row>
        </div>
        
        <!-- 业务信息 -->
        <el-divider content-position="left">业务信息</el-divider>
        <el-form-item label="主营业务">
          <el-input v-model="formData.mainCategory" type="textarea" />
        </el-form-item>
        
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="跨境经验">
              <el-switch v-model="formData.hasCrossBorderExperience" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="年销售额(万元)">
              <el-input v-model="formData.annualSalesVolume" />
            </el-form-item>
          </el-col>
        </el-row>
        
        <el-form-item label="仓库地址">
          <el-input v-model="formData.warehouseAddress" type="textarea" />
        </el-form-item>
        
        <!-- 审核信息 -->
        <el-divider content-position="left">审核信息</el-divider>
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="审核状态">
              <el-select v-model="formData.auditStatus">
                <el-option label="待审核" value="pending" />
                <el-option label="已通过" value="approved" />
                <el-option label="已拒绝" value="rejected" />
                <el-option label="待重新提交" value="resubmit" />
              </el-select>
            </el-form-item>
          </el-col>
        </el-row>
        
        <el-form-item label="审核备注">
          <el-input v-model="formData.auditRemark" type="textarea" />
        </el-form-item>
        
        <!-- 财务信息 -->
        <el-divider content-position="left">财务信息</el-divider>
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="账户余额">
              <el-input v-model="formData.balance" disabled>
                <template #append>元</template>
              </el-input>
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="冻结余额">
              <el-input v-model="formData.balanceFrozen" disabled>
                <template #append>元</template>
              </el-input>
            </el-form-item>
          </el-col>
        </el-row>
        
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="佣金比例">
              <el-input v-model="commissionRateDisplay" @input="updateCommissionRate">
                <template #append>%</template>
              </el-input>
              <div class="commission-note">
                <p style="color: #409eff; font-weight: bold;">佣金计算优先级：</p>
                <p style="color: #67c23a;">1. 会员有效期内：享受免佣金特权</p>
                <p style="color: #e6a23c;">2. 非会员且佣金比例&gt;0：使用自定义佣金比例</p>
                <p style="color: #909399;">3. 佣金比例为0或空：使用网站通用佣金比例</p>
              </div>
            </el-form-item>
          </el-col>
        </el-row>
        
        <!-- 会员信息 -->
        <el-divider content-position="left">会员信息</el-divider>
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="会员类型">
              <el-select v-model="formData.membershipType" disabled>
                <el-option label="无会员" value="none" />
                <el-option label="月会员" value="monthly" />
                <el-option label="季度会员" value="quarterly" />
                <el-option label="半年会员" value="half_yearly" />
                <el-option label="年会员" value="yearly" />
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="会员到期时间">
              <el-input v-model="formData.membershipExpiredAt" disabled />
            </el-form-item>
          </el-col>
        </el-row>
        
        <!-- 账号状态 -->
        <el-divider content-position="left">账号状态</el-divider>
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="账号状态">
              <el-switch v-model="formData.isActive" active-text="激活" inactive-text="禁用" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="邮箱验证">
              <el-switch v-model="formData.isVerified" active-text="已验证" inactive-text="未验证" />
            </el-form-item>
          </el-col>
        </el-row>
        
        <!-- 提交按钮 -->
        <el-form-item>
          <el-button type="primary" @click="handleSave" :loading="saving">保存</el-button>
          <el-button @click="handleCancel">取消</el-button>
        </el-form-item>
      </el-form>
    </el-card>
  </div>
</template>

<script>
import { ref, reactive, onMounted, defineProps, defineExpose, watch } from 'vue'
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
  ElMessage
} from 'element-plus'
import FileUpload from './FileUpload.vue'

export default {
  name: 'SupplierEditTab',
  components: {
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
    FileUpload
  },
  props: {
    adminLoginPath: {
      type: String,
      required: true
    },
    supplierId: {
      type: [Number, String],
      required: true
    },
    autoLoad: {
      type: Boolean,
      default: false
    }
  },
  setup(props) {
    const loading = ref(false)
    const saving = ref(false)
    
    // 初始化formData，确保所有图片字段都存在
    const initFormData = (supplier) => {
      return {
        ...supplier,
        // 确保图片字段存在，即使是空值
        businessLicenseImage: supplier.businessLicenseImage || '',
        legalPersonIdFront: supplier.legalPersonIdFront || '',
        legalPersonIdBack: supplier.legalPersonIdBack || '',
        individualIdFront: supplier.individualIdFront || '',
        individualIdBack: supplier.individualIdBack || ''
      }
    }
    
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
      companyName: '',
      companyType: '',
      businessLicenseNumber: '',
      legalPersonName: '',
      legalPersonIdCard: '',
      registeredCapital: '',
      establishmentDate: '',
      taxNumber: '',
      businessScope: '',
      bankAccountName: '',
      bankAccountNumber: '',
      bankName: '',
      bankBranch: '',
      businessLicenseImage: '',
      legalPersonIdFront: '',
      legalPersonIdBack: '',
      individualName: '',
      individualIdCard: '',
      individualBankAccount: '',
      individualBankName: '',
      individualIdFront: '',
      individualIdBack: '',
      mainCategory: '',
      hasCrossBorderExperience: false,
      annualSalesVolume: '',
      warehouseAddress: '',
      auditStatus: 'pending',
      auditRemark: '',
      balance: '0.00',
      balanceFrozen: '0.00',
      commissionRate: null,
      membershipType: 'none',
      membershipExpiredAt: '',
      isActive: true,
      isVerified: false
    })

    // 佣金比例显示值（百分比形式）
    const commissionRateDisplay = ref('')

    // 监听供应商数据变化，更新佣金比例显示值
    watch(() => formData, (newVal) => {
      if (newVal && newVal.commissionRate !== null && newVal.commissionRate !== undefined) {
        commissionRateDisplay.value = (parseFloat(newVal.commissionRate) * 100).toFixed(2)
      } else {
        commissionRateDisplay.value = ''
      }
    }, { immediate: true })

    // 更新佣金比例原始值
    const updateCommissionRate = (value) => {
      if (value === '' || value === null || value === undefined) {
        formData.commissionRate = null
      } else {
        // 将百分比转换为小数形式存储
        const rate = parseFloat(value) / 100
        // 限制在0-1之间
        if (rate >= 0 && rate <= 1) {
          formData.commissionRate = rate.toFixed(4)
        }
      }
    }

    // 加载供应商详情数据
    const loadSupplierDetail = async () => {
      if (!props.supplierId) {
        console.error('供应商ID缺失')
        return
      }

      loading.value = true
      try {
        const response = await fetch(`/admin${props.adminLoginPath}/supplier/${props.supplierId}/detail`, {
          method: 'GET',
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          }
        })

        const result = await response.json()

        if (result.success) {
          const supplierData = result.data
          
          // 重新赋值表单数据，使用initFormData确保所有图片字段都存在
          // 注意：图片字段使用原始key而不是带签名的URL
          const supplierCopy = {
            ...supplierData,
            // 使用key而不是Url字段
            businessLicenseImage: supplierData.businessLicenseImage || '',
            legalPersonIdFront: supplierData.legalPersonIdFront || '',
            legalPersonIdBack: supplierData.legalPersonIdBack || '',
            individualIdFront: supplierData.individualIdFront || '',
            individualIdBack: supplierData.individualIdBack || ''
          }
          Object.assign(formData, initFormData(supplierCopy))
          
          // 更新佣金比例显示值
          if (supplierData.commissionRate !== null && supplierData.commissionRate !== undefined) {
            commissionRateDisplay.value = (parseFloat(supplierData.commissionRate) * 100).toFixed(2)
          } else {
            commissionRateDisplay.value = ''
          }
        } else {
          console.error(result.message || '加载供应商详情失败')
        }
      } catch (error) {
        console.error('加载供应商详情失败:', error)
      } finally {
        loading.value = false
      }
    }

    // 初始化数据加载
    const initData = () => {
      loadSupplierDetail()
    }

    // 保存供应商信息
    const handleSave = async () => {
      try {
        saving.value = true
        
        console.log('=== [SupplierEditTab] handleSave 开始 ===');
        console.log('[SupplierEditTab] supplierType:', formData.supplierType);
        console.log('[SupplierEditTab] formData 图片字段值:');
        console.log('  businessLicenseImage:', formData.businessLicenseImage);
        console.log('  legalPersonIdFront:', formData.legalPersonIdFront);
        console.log('  legalPersonIdBack:', formData.legalPersonIdBack);
        console.log('  individualIdFront:', formData.individualIdFront);
        console.log('  individualIdBack:', formData.individualIdBack);
        
        // 创建发送到后端的数据对象，显式包含所有字段
        const sendData = {
          id: formData.id,
          username: formData.username,
          email: formData.email,
          contactPerson: formData.contactPerson,
          contactPhone: formData.contactPhone,
          contactAddress: formData.contactAddress,
          supplierType: formData.supplierType,
          isActive: formData.isActive,
          isVerified: formData.isVerified,
          auditStatus: formData.auditStatus,
          auditRemark: formData.auditRemark,
          mainCategory: formData.mainCategory,
          hasCrossBorderExperience: formData.hasCrossBorderExperience,
          annualSalesVolume: formData.annualSalesVolume,
          warehouseAddress: formData.warehouseAddress,
        }
        
        // 公司类型的字段
        if (formData.supplierType === 'company') {
          sendData.companyName = formData.companyName || ''
          sendData.companyType = formData.companyType || ''
          sendData.businessLicenseNumber = formData.businessLicenseNumber || ''
          sendData.legalPersonName = formData.legalPersonName || ''
          sendData.legalPersonIdCard = formData.legalPersonIdCard || ''
          sendData.registeredCapital = formData.registeredCapital || ''
          sendData.establishmentDate = formData.establishmentDate || ''
          sendData.taxNumber = formData.taxNumber || ''
          sendData.businessScope = formData.businessScope || ''
          sendData.bankAccountName = formData.bankAccountName || ''
          sendData.bankAccountNumber = formData.bankAccountNumber || ''
          sendData.bankName = formData.bankName || ''
          sendData.bankBranch = formData.bankBranch || ''
          // 图片字段：存储的是key（如 business_license_xxx.jpg），不是完整URL
          sendData.businessLicenseImage = formData.businessLicenseImage || ''
          sendData.legalPersonIdFront = formData.legalPersonIdFront || ''
          sendData.legalPersonIdBack = formData.legalPersonIdBack || ''
          
          console.log('[SupplierEditTab] 公司类型 - 准备发送的图片字段:');
          console.log('  businessLicenseImage:', sendData.businessLicenseImage);
          console.log('  legalPersonIdFront:', sendData.legalPersonIdFront);
          console.log('  legalPersonIdBack:', sendData.legalPersonIdBack);
        }
        
        // 个人类型的字段
        if (formData.supplierType === 'individual') {
          sendData.individualName = formData.individualName || ''
          sendData.individualIdCard = formData.individualIdCard || ''
          sendData.individualBankAccount = formData.individualBankAccount || ''
          sendData.individualBankName = formData.individualBankName || ''
          // 图片字段：存储的是key（如 individual_id_xxx.jpg），不是完整URL
          sendData.individualIdFront = formData.individualIdFront || ''
          sendData.individualIdBack = formData.individualIdBack || ''
          
          console.log('[SupplierEditTab] 个人类型 - 准备发送的图片字段:');
          console.log('  individualIdFront:', sendData.individualIdFront);
          console.log('  individualIdBack:', sendData.individualIdBack);
        }
        
        // 确保佣金比例以正确格式发送
        if (commissionRateDisplay.value === '' || commissionRateDisplay.value === null || commissionRateDisplay.value === undefined) {
          sendData.commissionRate = null
        } else {
          // 将显示的百分比转换为小数
          const rate = parseFloat(commissionRateDisplay.value) / 100
          if (isNaN(rate)) {
            sendData.commissionRate = null
          } else {
            // 限制在0-1之间，并格式化为4位小数
            sendData.commissionRate = Math.max(0, Math.min(1, rate)).toFixed(4)
          }
        }
        
        console.log('[SupplierEditTab] 最终发送到后端的完整数据:');
        console.log(JSON.stringify(sendData, null, 2));
        console.log('===========================================');
        
        const response = await fetch(`/admin${props.adminLoginPath}/supplier/${formData.id}/update`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: JSON.stringify(sendData)
        })
        
        const result = await response.json()
        
        if (result.success) {
          ElMessage.success(result.message)
          // 触发保存成功事件
          window.dispatchEvent(new CustomEvent('supplier-save-success'))
        } else {
          ElMessage.error(result.error || '保存失败')
        }
      } catch (error) {
        ElMessage.error('保存失败: ' + error.message)
      } finally {
        saving.value = false
      }
    }

    // 取消编辑
    const handleCancel = () => {
      // 触发关闭当前标签页事件
      window.dispatchEvent(new CustomEvent('close-current-tab'))
    }

    // 暴露方法给父组件
    defineExpose({
      initData
    })

    // 组件挂载时
    onMounted(() => {
      if (props.autoLoad) {
        initData()
      }
    })

    return {
      loading,
      saving,
      formData,
      commissionRateDisplay,
      loadSupplierDetail,
      initData,
      handleSave,
      handleCancel,
      updateCommissionRate
    }
  }
}
</script>

<style scoped>
.supplier-edit {
  padding: 20px;
}

.box-card {
  max-width: 1200px;
  margin: 0 auto;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-weight: bold;
  font-size: 16px;
}

:deep(.el-form-item__label) {
  font-weight: 500;
}

:deep(.el-divider__text) {
  font-weight: bold;
  color: #303133;
}

/* 图片上传区域容器 - 文字在上，图片在下，垂直居中 */
.image-upload-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  width: 100%;
}

.image-label {
  text-align: center;
  margin-bottom: 10px;
  font-size: 14px;
  font-weight: 500;
  color: #606266;
}

.commission-note {
  font-size: 12px;
  color: #909399;
  margin-top: 5px;
  line-height: 1.4;
}
</style>