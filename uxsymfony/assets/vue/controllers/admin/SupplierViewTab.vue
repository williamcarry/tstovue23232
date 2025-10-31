<template>
  <div class="supplier-view" v-loading="loading">
    <el-card class="box-card">
      <template #header>
        <div class="card-header">
          <span>查看供应商</span>
        </div>
      </template>
      
      <el-form :model="supplier" label-width="120px" :disabled="true">
        <!-- 账号信息 -->
        <el-divider content-position="left">账号信息</el-divider>
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="供应商ID">
              <el-input v-model="supplier.id" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="用户名">
              <el-input v-model="supplier.username" />
            </el-form-item>
          </el-col>
        </el-row>
        
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="邮箱">
              <el-input v-model="supplier.email" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="供应商类型">
              <el-select v-model="supplier.supplierType">
                <el-option label="公司" value="company" />
                <el-option label="个人" value="individual" />
              </el-select>
            </el-form-item>
          </el-col>
        </el-row>
        
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="显示名称">
              <el-input v-model="supplier.displayName" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="注册时间">
              <el-input v-model="supplier.createdAt" />
            </el-form-item>
          </el-col>
        </el-row>
        
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="最后登录时间">
              <el-input v-model="supplier.lastLoginAt" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="最后更新时间">
              <el-input v-model="supplier.updatedAt" />
            </el-form-item>
          </el-col>
        </el-row>
        
        <!-- 基本信息 -->
        <el-divider content-position="left">基本信息</el-divider>
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="联系人">
              <el-input v-model="supplier.contactPerson" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="联系电话">
              <el-input v-model="supplier.contactPhone" />
            </el-form-item>
          </el-col>
        </el-row>
        
        <el-form-item label="联系地址">
          <el-input v-model="supplier.contactAddress" type="textarea" />
        </el-form-item>
        
        <!-- 公司信息 -->
        <div v-if="supplier.supplierType === 'company'">
          <el-divider content-position="left">公司信息</el-divider>
          <el-row :gutter="20">
            <el-col :span="12">
              <el-form-item label="公司名称">
                <el-input v-model="supplier.companyName" />
              </el-form-item>
            </el-col>
            <el-col :span="12">
              <el-form-item label="公司类型">
                <el-select v-model="supplier.companyType">
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
                <el-input v-model="supplier.businessLicenseNumber" />
              </el-form-item>
            </el-col>
            <el-col :span="12">
              <el-form-item label="法人姓名">
                <el-input v-model="supplier.legalPersonName" />
              </el-form-item>
            </el-col>
          </el-row>
          
          <el-row :gutter="20">
            <el-col :span="12">
              <el-form-item label="法人身份证号">
                <el-input v-model="supplier.legalPersonIdCard" />
              </el-form-item>
            </el-col>
            <el-col :span="12">
              <el-form-item label="注册资本(万元)">
                <el-input v-model="supplier.registeredCapital" />
              </el-form-item>
            </el-col>
          </el-row>
          
          <el-row :gutter="20">
            <el-col :span="12">
              <el-form-item label="公司成立日期">
                <el-input v-model="supplier.establishmentDate" />
              </el-form-item>
            </el-col>
            <el-col :span="12">
              <el-form-item label="税务登记号">
                <el-input v-model="supplier.taxNumber" />
              </el-form-item>
            </el-col>
          </el-row>
          
          <el-form-item label="经营范围">
            <el-input v-model="supplier.businessScope" type="textarea" />
          </el-form-item>
          
          <el-row :gutter="20">
            <el-col :span="12">
              <el-form-item label="银行开户名称">
                <el-input v-model="supplier.bankAccountName" />
              </el-form-item>
            </el-col>
            <el-col :span="12">
              <el-form-item label="银行账号">
                <el-input v-model="supplier.bankAccountNumber" />
              </el-form-item>
            </el-col>
          </el-row>
          
          <el-row :gutter="20">
            <el-col :span="12">
              <el-form-item label="开户银行">
                <el-input v-model="supplier.bankName" />
              </el-form-item>
            </el-col>
            <el-col :span="12">
              <el-form-item label="开户支行">
                <el-input v-model="supplier.bankBranch" />
              </el-form-item>
            </el-col>
          </el-row>
          
          <!-- 公司证件图片 -->
          <el-divider content-position="left">公司证件图片</el-divider>
          <el-row :gutter="20">
            <el-col :span="8">
              <div class="image-upload-container">
                <div class="image-label">营业执照</div>
                <div v-if="supplier.businessLicenseImageUrl" class="image-preview" @click="showImageViewer([supplier.businessLicenseImageUrl])">
                  <img :src="supplier.businessLicenseImageUrl" alt="营业执照" class="preview-image" />
                  <div class="image-overlay">
                    <el-icon><ZoomIn /></el-icon>
                  </div>
                </div>
                <div v-else class="no-image">无图片</div>
              </div>
            </el-col>
            <el-col :span="8">
              <div class="image-upload-container">
                <div class="image-label">法人身份证正面</div>
                <div v-if="supplier.legalPersonIdFrontUrl" class="image-preview" @click="showImageViewer([supplier.legalPersonIdFrontUrl])">
                  <img :src="supplier.legalPersonIdFrontUrl" alt="法人身份证正面" class="preview-image" />
                  <div class="image-overlay">
                    <el-icon><ZoomIn /></el-icon>
                  </div>
                </div>
                <div v-else class="no-image">无图片</div>
              </div>
            </el-col>
            <el-col :span="8">
              <div class="image-upload-container">
                <div class="image-label">法人身份证反面</div>
                <div v-if="supplier.legalPersonIdBackUrl" class="image-preview" @click="showImageViewer([supplier.legalPersonIdBackUrl])">
                  <img :src="supplier.legalPersonIdBackUrl" alt="法人身份证反面" class="preview-image" />
                  <div class="image-overlay">
                    <el-icon><ZoomIn /></el-icon>
                  </div>
                </div>
                <div v-else class="no-image">无图片</div>
              </div>
            </el-col>
          </el-row>
        </div>
        
        <!-- 个人信息 -->
        <div v-if="supplier.supplierType === 'individual'">
          <el-divider content-position="left">个人信息</el-divider>
          <el-row :gutter="20">
            <el-col :span="12">
              <el-form-item label="真实姓名">
                <el-input v-model="supplier.individualName" />
              </el-form-item>
            </el-col>
            <el-col :span="12">
              <el-form-item label="身份证号">
                <el-input v-model="supplier.individualIdCard" />
              </el-form-item>
            </el-col>
          </el-row>
          
          <el-row :gutter="20">
            <el-col :span="12">
              <el-form-item label="个人银行账号">
                <el-input v-model="supplier.individualBankAccount" />
              </el-form-item>
            </el-col>
            <el-col :span="12">
              <el-form-item label="个人开户银行">
                <el-input v-model="supplier.individualBankName" />
              </el-form-item>
            </el-col>
          </el-row>
          
          <!-- 个人证件图片 -->
          <el-divider content-position="left">个人证件图片</el-divider>
          <el-row :gutter="20">
            <el-col :span="12">
              <div class="image-upload-container">
                <div class="image-label">身份证正面</div>
                <div v-if="supplier.individualIdFrontUrl" class="image-preview" @click="showImageViewer([supplier.individualIdFrontUrl])">
                  <img :src="supplier.individualIdFrontUrl" alt="身份证正面" class="preview-image" />
                  <div class="image-overlay">
                    <el-icon><ZoomIn /></el-icon>
                  </div>
                </div>
                <div v-else class="no-image">无图片</div>
              </div>
            </el-col>
            <el-col :span="12">
              <div class="image-upload-container">
                <div class="image-label">身份证反面</div>
                <div v-if="supplier.individualIdBackUrl" class="image-preview" @click="showImageViewer([supplier.individualIdBackUrl])">
                  <img :src="supplier.individualIdBackUrl" alt="身份证反面" class="preview-image" />
                  <div class="image-overlay">
                    <el-icon><ZoomIn /></el-icon>
                  </div>
                </div>
                <div v-else class="no-image">无图片</div>
              </div>
            </el-col>
          </el-row>
        </div>
        
        <!-- 业务信息 -->
        <el-divider content-position="left">业务信息</el-divider>
        <el-form-item label="主营业务">
          <el-input v-model="supplier.mainCategory" type="textarea" />
        </el-form-item>
        
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="跨境经验">
              <el-switch v-model="supplier.hasCrossBorderExperience" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="年销售额(万元)">
              <el-input v-model="supplier.annualSalesVolume" />
            </el-form-item>
          </el-col>
        </el-row>
        
        <el-form-item label="仓库地址">
          <el-input v-model="supplier.warehouseAddress" type="textarea" />
        </el-form-item>
        
        <!-- 审核信息 -->
        <el-divider content-position="left">审核信息</el-divider>
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="审核状态">
              <el-select v-model="supplier.auditStatus">
                <el-option label="待审核" value="pending" />
                <el-option label="已通过" value="approved" />
                <el-option label="已拒绝" value="rejected" />
                <el-option label="待重新提交" value="resubmit" />
              </el-select>
            </el-form-item>
          </el-col>
          <!-- 在审核状态后面添加审核人 -->
          <el-col :span="12">
            <el-form-item label="审核人">
              <el-input v-model="supplier.auditedBy" />
            </el-form-item>
          </el-col>
        </el-row>
        
        <el-form-item label="审核备注">
          <el-input v-model="supplier.auditRemark" type="textarea" />
        </el-form-item>
        
        <!-- 财务信息 -->
        <el-divider content-position="left">财务信息</el-divider>
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="账户余额">
              <el-input v-model="supplier.balance">
                <template #append>元</template>
              </el-input>
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="冻结余额">
              <el-input v-model="supplier.balanceFrozen">
                <template #append>元</template>
              </el-input>
            </el-form-item>
          </el-col>
        </el-row>
        
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="佣金比例">
              <el-input :value="formatCommissionRate(supplier)" />
            </el-form-item>
          </el-col>
        </el-row>
        
        <!-- 会员信息 -->
        <el-divider content-position="left">会员信息</el-divider>
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="会员类型">
              <el-select v-model="supplier.membershipType">
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
              <el-input v-model="supplier.membershipExpiredAt" />
            </el-form-item>
          </el-col>
        </el-row>
        
        <!-- 账号状态 -->
        <el-divider content-position="left">账号状态</el-divider>
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="账号状态">
              <el-switch v-model="supplier.isActive" active-text="激活" inactive-text="禁用" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="邮箱验证">
              <el-switch v-model="supplier.isVerified" active-text="已验证" inactive-text="未验证" />
            </el-form-item>
          </el-col>
        </el-row>
      </el-form>
    </el-card>
    
    <!-- 图片查看器 -->
    <el-image-viewer
      v-if="imageViewerVisible"
      :url-list="imageViewerUrls"
      @close="closeImageViewer"
    />
  </div>
</template>

<script>
import { ref, reactive, onMounted, defineProps, defineExpose } from 'vue'
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
  ElIcon,
  ElImageViewer
} from 'element-plus'
import { ZoomIn } from '@element-plus/icons-vue'

export default {
  name: 'SupplierViewTab',
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
    ElIcon,
    ElImageViewer,
    ZoomIn
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
    const imageViewerVisible = ref(false)
    const imageViewerUrls = ref([])
    
    // 供应商数据
    const supplier = reactive({
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
      businessLicenseImageUrl: '',
      legalPersonIdFront: '',
      legalPersonIdFrontUrl: '',
      legalPersonIdBack: '',
      legalPersonIdBackUrl: '',
      individualName: '',
      individualIdCard: '',
      individualBankAccount: '',
      individualBankName: '',
      individualIdFront: '',
      individualIdFrontUrl: '',
      individualIdBack: '',
      individualIdBackUrl: '',
      mainCategory: '',
      hasCrossBorderExperience: false,
      annualSalesVolume: '',
      warehouseAddress: '',
      auditStatus: 'pending',
      auditRemark: '',
      auditedBy: '',
      balance: '0.00',
      balanceFrozen: '0.00',
      commissionRate: null,
      effectiveCommissionRatePercentage: null,
      membershipType: 'none',
      membershipExpiredAt: '',
      isActive: true,
      isVerified: false
    })

    // 获取文件签名URL
    const getSignedUrl = async (key) => {
      if (!key || key.startsWith('http')) {
        return key
      }
      
      try {
        const loginPath = props.adminLoginPath || window.adminLoginPath || ''
        const signedUrlEndpoint = `/admin${loginPath}/supplier/file/signed-url`
        
        const response = await fetch(signedUrlEndpoint, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: JSON.stringify({ key: key })
        })
        
        const result = await response.json()
        
        if (result.success) {
          return result.url
        }
      } catch (error) {
        console.error('获取签名URL失败:', error)
      }
      
      return key
    }

    // 为供应商数据添加图片URL
    const addImageUrlsToSupplier = async (supplierData) => {
      const supplierWithUrls = { ...supplierData }
      
      // 为公司信息图片获取签名URL
      if (supplierWithUrls.businessLicenseImage) {
        supplierWithUrls.businessLicenseImageUrl = await getSignedUrl(supplierWithUrls.businessLicenseImage)
      }
      if (supplierWithUrls.legalPersonIdFront) {
        supplierWithUrls.legalPersonIdFrontUrl = await getSignedUrl(supplierWithUrls.legalPersonIdFront)
      }
      if (supplierWithUrls.legalPersonIdBack) {
        supplierWithUrls.legalPersonIdBackUrl = await getSignedUrl(supplierWithUrls.legalPersonIdBack)
      }
      
      // 为个人信息图片获取签名URL
      if (supplierWithUrls.individualIdFront) {
        supplierWithUrls.individualIdFrontUrl = await getSignedUrl(supplierWithUrls.individualIdFront)
      }
      if (supplierWithUrls.individualIdBack) {
        supplierWithUrls.individualIdBackUrl = await getSignedUrl(supplierWithUrls.individualIdBack)
      }
      
      return supplierWithUrls
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
          
          // 为供应商数据添加图片URL
          const supplierWithUrls = await addImageUrlsToSupplier(supplierData)
          
          // 填充表单数据
          Object.assign(supplier, supplierWithUrls)
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

    // 显示图片查看器
    const showImageViewer = (urls) => {
      imageViewerUrls.value = urls
      imageViewerVisible.value = true
    }

    // 关闭图片查看器
    const closeImageViewer = () => {
      imageViewerVisible.value = false
      imageViewerUrls.value = []
    }

    // 检查供应商是否为活跃会员
    const isMemberActive = (supplierData) => {
      // 检查是否有会员类型且不为'none'
      if (!supplierData.membershipType || supplierData.membershipType === 'none' || !supplierData.membershipExpiredAt) {
        return false
      }
      
      // 检查会员是否过期
      const expiredAt = new Date(supplierData.membershipExpiredAt)
      const now = new Date()
      return expiredAt > now
    }

    // 格式化佣金比例显示
    const formatCommissionRate = (supplierData) => {
      // 检查是否为活跃会员
      if (isMemberActive(supplierData)) {
        return '免佣金'
      }
      
      // 检查是否有有效的佣金比例（包括系统默认）
      if (supplierData.effectiveCommissionRatePercentage !== null && supplierData.effectiveCommissionRatePercentage !== undefined) {
        return supplierData.effectiveCommissionRatePercentage.toFixed(2) + '%'
      }
      
      // 检查是否有自定义佣金比例
      if (supplierData.commissionRate !== null && supplierData.commissionRate !== undefined) {
        return (parseFloat(supplierData.commissionRate) * 100).toFixed(2) + '%'
      }
      
      return '系统默认'
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
      supplier,
      imageViewerVisible,
      imageViewerUrls,
      loadSupplierDetail,
      initData,
      showImageViewer,
      closeImageViewer,
      isMemberActive,
      formatCommissionRate
    }
  }
}
</script>

<style scoped>
.supplier-view {
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

.image-preview {
  display: flex;
  justify-content: center;
  align-items: center;
  width: 100%;
  height: 150px;
  border: 1px dashed #dcdfe6;
  border-radius: 4px;
  overflow: hidden;
  cursor: pointer;
  position: relative;
}

.image-preview:hover .image-overlay {
  display: flex;
}

.preview-image {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
}

.image-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  display: none;
  justify-content: center;
  align-items: center;
  color: white;
  font-size: 24px;
}

.no-image {
  display: flex;
  justify-content: center;
  align-items: center;
  width: 100%;
  height: 150px;
  color: #909399;
  font-size: 14px;
  border: 1px dashed #dcdfe6;
  border-radius: 4px;
}
</style>