<template>
  <div class="withdrawal-list">
    <!-- 页面头部 -->
    <div class="page-header">
      <h2 class="page-title">
        <el-icon class="title-icon"><Wallet /></el-icon>
        提现申请
      </h2>
      <div class="header-actions">
        <el-button type="primary" @click="refreshData" :icon="Refresh">
          刷新数据
        </el-button>
      </div>
    </div>
    
    <!-- 搜索和筛选 -->
    <el-card class="filter-card">
      <div class="filter-header">
        <el-icon class="filter-icon"><Search /></el-icon>
        <span class="filter-title">筛选条件</span>
      </div>
      <el-form :model="searchForm" class="search-form" label-position="left" label-width="80px">
        <el-row :gutter="20" align="middle">
          <el-col :span="5" :xs="24">
            <el-form-item label="状态">
              <el-select v-model="searchForm.status" placeholder="请选择状态" clearable>
                <el-option label="全部" value="" />
                <el-option label="待审核" value="pending" />
                <el-option label="已通过" value="approved" />
                <el-option label="已拒绝" value="rejected" />
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :span="5" :xs="24">
            <el-form-item label="供应商">
              <el-input v-model="searchForm.supplierName" placeholder="请输入供应商名称" />
            </el-form-item>
          </el-col>
          <el-col :span="6" :xs="24">
            <el-form-item label="审核时间">
              <div class="date-range-container">
                <el-date-picker
                  v-model="searchForm.reviewedAtStart"
                  type="datetime"
                  placeholder="请选择开始时间"
                  value-format="YYYY-MM-DD HH:mm:ss"
                />
                <span class="date-separator">到</span>
                <el-date-picker
                  v-model="searchForm.reviewedAtEnd"
                  type="datetime"
                  placeholder="请选择结束时间"
                  value-format="YYYY-MM-DD HH:mm:ss"
                />
              </div>
            </el-form-item>
          </el-col>
          <el-col :span="8" :xs="24" class="action-col">
            <el-form-item label=" ">
              <div class="form-actions">
                <el-button type="primary" @click="searchWithdrawals" :icon="Search">
                  搜索
                </el-button>
                <el-button @click="resetSearch" :icon="RefreshLeft">
                  重置
                </el-button>
                <el-button type="success" @click="exportData" :icon="Upload">
                  导出
                </el-button>
              </div>
            </el-form-item>
          </el-col>
        </el-row>
      </el-form>
    </el-card>
    
    <!-- 提现申请表格 -->
    <el-card class="table-card">
      <template #header>
        <div class="table-header">
          <div class="table-header-title">
            <el-icon class="table-icon"><List /></el-icon>
            提现申请列表
          </div>
          <div class="table-header-info">
            共 {{ pagination.totalItems }} 条记录
          </div>
        </div>
      </template>
      
      <el-table 
        :data="withdrawals" 
        class="withdrawal-table" 
        v-loading="loading"
        :fit="true"
        :border="true"
        :header-cell-style="{ textAlign: 'center' }"
        :cell-style="{ textAlign: 'center' }"
        :stripe="true"
      >
        <el-table-column label="序号" min-width="60">
          <template #default="scope">
            {{ (pagination.currentPage - 1) * pagination.itemsPerPage + scope.$index + 1 }}
          </template>
        </el-table-column>
        <el-table-column label="供应商" min-width="120">
          <template #default="scope">
            <span style="color: #409eff;">{{ scope.row.username }}</span>
            <span>/</span>
            <span style="color: #67c23a;">{{ scope.row.companyName }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="amount" label="提现金额" min-width="100">
          <template #default="scope">
            <span style="color: #f56c6c;">{{ formatCurrency(scope.row.amount) }}</span>
          </template>
        </el-table-column>
        <el-table-column label="收款信息" min-width="250">
          <template #default="scope">
            <div style="white-space: pre-line; line-height: 1.6; font-family: 'Consolas', 'Monaco', monospace;">{{ scope.row.paymentInfo || '-' }}</div>
          </template>
        </el-table-column>
        <el-table-column prop="status" label="状态" min-width="100">
          <template #default="scope">
            <el-tag :type="getStatusTagType(scope.row.status)">
              {{ getStatusText(scope.row.status) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="createdAt" label="申请时间" min-width="160" />
        <el-table-column prop="reviewedAt" label="审核时间" min-width="160" />
        <el-table-column prop="reviewedBy" label="审核人" min-width="100" />
        <el-table-column label="打款截图" min-width="100">
          <template #default="scope">
            <div v-if="scope.row.paymentScreenshotUrl">
              <el-image
                :src="scope.row.paymentScreenshotUrl"
                fit="cover"
                style="width: 50px; height: 50px; cursor: pointer;"
                :preview-src-list="[scope.row.paymentScreenshotUrl]"
                preview-teleported
              />
            </div>
            <div v-else>
              <span>-</span>
            </div>
          </template>
        </el-table-column>
        <el-table-column label="操作" min-width="200">
          <template #default="scope">
            <el-button size="small" @click="viewDetail(scope.row)">操作</el-button>
            <el-button 
              v-if="scope.row.status === 'approved'" 
              size="small" 
              @click="showUploadDialog(scope.row)"
            >
              打款截图
            </el-button>
          </template>
        </el-table-column>
      </el-table>
    </el-card>
    
    <!-- 分页 -->
    <div class="pagination-container">
      <el-pagination
        v-model:current-page="pagination.currentPage"
        v-model:page-size="pagination.itemsPerPage"
        :page-sizes="[10, 20, 50, 100]"
        :total="pagination.totalItems"
        :pager-count="5"
        layout="total, sizes, prev, pager, next, jumper"
        prev-text="上一页"
        next-text="下一页"
        background
        @size-change="handleSizeChange"
        @current-change="handleCurrentChange"
      />
    </div>
    
    <!-- 提现申请详情对话框 -->
    <el-dialog v-model="detailDialogVisible" title="提现申请详情" width="600px">
      <el-form :model="currentWithdrawal" label-width="100px">
        <el-form-item label="供应商：">
          <span style="color: #409eff;">{{ currentWithdrawal.username || '-' }}</span>
          <span>/</span>
          <span style="color: #67c23a;">{{ currentWithdrawal.companyName || '无' }}</span>
        </el-form-item>
        <el-form-item label="提现金额：">
          <span style="color: #f56c6c; font-weight: bold;">{{ formatCurrency(currentWithdrawal.amount) }}</span>
        </el-form-item>
        <el-form-item label="收款信息：">
          <div style="white-space: pre-line; line-height: 1.8; font-family: 'Consolas', 'Monaco', monospace;">{{ currentWithdrawal.paymentInfo || '-' }}</div>
        </el-form-item>
        <el-form-item label="打款截图：">
          <div v-if="currentWithdrawal.paymentScreenshotUrl">
            <el-image
              :src="currentWithdrawal.paymentScreenshotUrl"
              fit="contain"
              style="max-width: 300px; max-height: 300px; cursor: pointer;"
              :preview-src-list="[currentWithdrawal.paymentScreenshotUrl]"
              preview-teleported
            />
          </div>
          <div v-else>
            <span>暂无打款截图</span>
          </div>
        </el-form-item>
        <el-form-item label="状态：">
          <el-tag :type="getStatusTagType(currentWithdrawal.status)">
            {{ getStatusText(currentWithdrawal.status) }}
          </el-tag>
        </el-form-item>
        <el-form-item label="申请时间：">
          <span>{{ currentWithdrawal.createdAt }}</span>
        </el-form-item>
        <el-form-item label="审核时间：">
          <span>{{ currentWithdrawal.reviewedAt || '-' }}</span>
        </el-form-item>
        <el-form-item label="审核人：">
          <span>{{ currentWithdrawal.reviewedBy || '-' }}</span>
        </el-form-item>
        <el-form-item label="备注：">
          <el-input 
            v-model="currentWithdrawal.remark" 
            type="textarea" 
            :rows="3" 
            placeholder="请输入备注信息"
            :disabled="currentWithdrawal.status !== 'pending'"
          />
        </el-form-item>
      </el-form>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="detailDialogVisible = false">关闭</el-button>
          <el-button 
            v-if="currentWithdrawal.status === 'pending'" 
            type="primary" 
            @click="reviewWithdrawal(currentWithdrawal, 'approved')"
          >
            通过
          </el-button>
          <el-button 
            v-if="currentWithdrawal.status === 'pending'" 
            type="danger" 
            @click="reviewWithdrawal(currentWithdrawal, 'rejected')"
          >
            拒绝
          </el-button>
        </span>
      </template>
    </el-dialog>
    
    <!-- 打款截图上传对话框 -->
    <el-dialog v-model="uploadDialogVisible" title="上传打款截图" width="500px">
      <el-form label-width="100px">
        <el-form-item label="选择图片：">
          <withdrawal-payment-screenshot-upload 
            v-model="uploadPaymentScreenshot"
            :admin-login-path="adminLoginPath"
            :withdrawal-id="selectedWithdrawalId"
            @upload-success="handleUploadSuccess"
            @upload-error="handleUploadError"
          />
        </el-form-item>
      </el-form>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="uploadDialogVisible = false">关闭</el-button>
        </span>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, onMounted, defineExpose, reactive } from 'vue'
import {
  ElCard,
  ElTable,
  ElTableColumn,
  ElButton,
  ElForm,
  ElFormItem,
  ElInput,
  ElSelect,
  ElOption,
  ElTag,
  ElMessage,
  ElPagination,
  ElDialog,
  ElImage,
  ElDatePicker,
  ElLoading,
  ElRow,
  ElCol,
  ElIcon
} from 'element-plus'
import { Wallet, Refresh, Search, RefreshLeft, List, Upload } from '@element-plus/icons-vue'
import WithdrawalPaymentScreenshotUpload from './WithdrawalPaymentScreenshotUpload.vue'

// 定义组件属性
const props = defineProps({
  adminLoginPath: {
    type: String,
    default: ''
  },
  // 添加一个属性来控制是否自动加载数据
  autoLoad: {
    type: Boolean,
    default: false
  }
})

// 定义响应式数据
const withdrawals = ref([])
const loading = ref(false)
const detailDialogVisible = ref(false)
const uploadDialogVisible = ref(false)
const currentWithdrawal = ref({})
const selectedWithdrawalId = ref(null)
const uploadPaymentScreenshot = ref('')

const pagination = ref({
  currentPage: 1,
  totalPages: 1,
  totalItems: 0,
  itemsPerPage: 20
})

// 搜索表单
const searchForm = ref({
  status: '',
  supplierName: '',
  reviewedAtStart: '',
  reviewedAtEnd: ''
})

// 获取状态显示文本
const getStatusText = (status) => {
  const statusMap = {
    'pending': '待审核',
    'approved': '已通过',
    'rejected': '已拒绝'
  }
  return statusMap[status] || status
}

// 获取状态标签类型
const getStatusTagType = (status) => {
  const typeMap = {
    'pending': 'warning',
    'approved': 'success',
    'rejected': 'danger'
  }
  return typeMap[status] || 'info'
}

// 加载提现申请数据
const loadWithdrawalData = async (page = 1, limit = 20) => {
  loading.value = true
  try {
    // 构建查询参数
    const paramsObj = {
      page: page,
      limit: limit
    };
    
    // 只有非空值才添加到参数中
    if (searchForm.value.status) {
      paramsObj.status = searchForm.value.status;
    }
    if (searchForm.value.supplierName) {
      paramsObj.supplierName = searchForm.value.supplierName;
    }
    if (searchForm.value.reviewedAtStart) {
      paramsObj.reviewedAtStart = searchForm.value.reviewedAtStart;
    }
    if (searchForm.value.reviewedAtEnd) {
      paramsObj.reviewedAtEnd = searchForm.value.reviewedAtEnd;
    }
    
    const params = new URLSearchParams(paramsObj);
    
    const url = `/admin${props.adminLoginPath}/withdrawal/list?${params}`
    
    const response = await fetch(url, {
      method: 'GET',
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    
    const result = await response.json()
    
    if (result.success) {
      withdrawals.value = result.data
      pagination.value = result.pagination
    } else {
      ElMessage.error(result.message || '加载数据失败')
    }
  } catch (error) {
    ElMessage.error('加载数据失败: ' + error.message)
  } finally {
    loading.value = false
  }
}

// 刷新数据
const refreshData = () => {
  loadWithdrawalData(pagination.value.currentPage, pagination.value.itemsPerPage)
}

// 搜索提现申请
const searchWithdrawals = () => {
  pagination.value.currentPage = 1
  loadWithdrawalData(1, pagination.value.itemsPerPage)
}

// 重置搜索
const resetSearch = () => {
  searchForm.value = {
    status: '',
    supplierName: '',
    reviewedAtStart: '',
    reviewedAtEnd: ''
  }
  pagination.value.currentPage = 1
  loadWithdrawalData(1, pagination.value.itemsPerPage)
}

// 处理分页大小变化
const handleSizeChange = (val) => {
  pagination.value.itemsPerPage = val
  loadWithdrawalData(pagination.value.currentPage, val)
}

// 处理当前页变化
const handleCurrentChange = (val) => {
  pagination.value.currentPage = val
  loadWithdrawalData(val, pagination.value.itemsPerPage)
}

// 查看详情
const viewDetail = (withdrawal) => {
  currentWithdrawal.value = { ...withdrawal }
  detailDialogVisible.value = true
}

// 显示上传对话框
const showUploadDialog = (withdrawal) => {
  // 只有已通过状态的提现申请才能上传打款截图
  if (withdrawal.status !== 'approved') {
    ElMessage.warning('只有已通过状态的提现申请才能上传打款截图')
    return
  }
  
  selectedWithdrawalId.value = withdrawal.id
  uploadPaymentScreenshot.value = withdrawal.paymentScreenshot || ''
  uploadDialogVisible.value = true
}

// 导出数据
const exportData = async () => {
  try {
    const params = new URLSearchParams({
      status: searchForm.value.status,
      supplierName: searchForm.value.supplierName,
      reviewedAtStart: searchForm.value.reviewedAtStart,
      reviewedAtEnd: searchForm.value.reviewedAtEnd,
      export: 'excel'
    })
    
    const url = `/admin${props.adminLoginPath}/withdrawal/list?${params}`
    
    // 创建一个隐藏的iframe来触发下载
    const iframe = document.createElement('iframe')
    iframe.style.display = 'none'
    iframe.src = url
    document.body.appendChild(iframe)
    
    // 3秒后移除iframe
    setTimeout(() => {
      document.body.removeChild(iframe)
    }, 3000)
    
    ElMessage.success('导出任务已开始，请稍候查看下载文件')
  } catch (error) {
    ElMessage.error('导出失败: ' + error.message)
  }
}

// 图片上传失败处理
const handleUploadError = (error) => {
  console.error('打款截图上传失败:', error)
  ElMessage.error('打款截图上传失败: ' + (error.message || '未知错误'))
}

// 图片上传成功处理
const handleUploadSuccess = (result) => {
  console.log('打款截图上传成功:', result)
  ElMessage.success('打款截图上传成功')
  uploadDialogVisible.value = false
  // 重新加载数据以更新缩略图
  loadWithdrawalData(pagination.value.currentPage, pagination.value.itemsPerPage)
}

// 审核提现申请
const reviewWithdrawal = async (withdrawal, action) => {
  try {
    const url = `/admin${props.adminLoginPath}/withdrawal/${withdrawal.id}/review`
    
    const response = await fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify({
        action: action,
        remark: currentWithdrawal.value.remark
      })
    })
    
    const result = await response.json()
    
    if (result.success) {
      ElMessage.success(result.message || '操作成功')
      detailDialogVisible.value = false
      // 重新加载数据
      loadWithdrawalData(pagination.value.currentPage, pagination.value.itemsPerPage)
    } else {
      ElMessage.error(result.message || '操作失败')
    }
  } catch (error) {
    ElMessage.error('操作失败: ' + error.message)
  }
}

// 组件挂载时加载数据
onMounted(() => {
  // 只有当 autoLoad 为 true 时才自动加载数据
  if (props.autoLoad) {
    loadWithdrawalData()
  }
})

// 暴露方法给父组件调用
defineExpose({
  loadWithdrawalData
})

// 格式化货币显示
const formatCurrency = (amount) => {
  if (amount === null || amount === undefined) return '0.00'
  return parseFloat(amount).toFixed(2)
}

// 图片预览功能
const previewImage = (imageUrl) => {
  // 创建一个新窗口来显示图片
  window.open(imageUrl, '_blank');
}
</script>

<style scoped>
.withdrawal-list {
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

.header-actions {
  display: flex;
  gap: 10px;
}

.filter-card {
  margin-bottom: 20px;
  width: 100%;
}

.filter-header {
  display: flex;
  align-items: center;
  margin-bottom: 15px;
}

.filter-icon {
  margin-right: 10px;
  vertical-align: middle;
}

.filter-title {
  font-size: 18px;
  font-weight: bold;
  display: flex;
  align-items: center;
}

.search-form {
  width: 100%;
}

.search-form :deep(.el-form-item) {
  margin-bottom: 0;
  display: flex;
  align-items: center;
}

.search-form :deep(.el-form-item__label) {
  width: 80px;
  margin-right: 10px;
  margin-bottom: 0;
  text-align: right;
  flex-shrink: 0;
}

.search-form :deep(.el-form-item__content) {
  width: calc(100% - 90px);
  display: flex;
  align-items: center;
}

.date-range-container {
  display: flex;
  align-items: center;
  gap: 10px;
  width: 100%;
}

.date-separator {
  margin: 0 5px;
  white-space: nowrap;
}

.action-col {
  display: flex;
  justify-content: flex-end;
  align-items: center;
}

.form-actions {
  display: flex;
  gap: 10px;
  align-items: center;
}

.table-card {
  width: 100%;
}

.table-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 15px;
}

.table-header-title {
  font-size: 18px;
  font-weight: bold;
  display: flex;
  align-items: center;
}

.table-icon {
  margin-right: 10px;
  vertical-align: middle;
}

.table-header-info {
  font-size: 14px;
  color: #909399;
}

.withdrawal-table {
  width: 100% !important;
  font-size: 14px;
}

.withdrawal-table :deep(.el-table__header th) {
  background-color: #f5f7fa;
  color: #606266;
  font-weight: bold;
}

.withdrawal-table :deep(.el-table__row:hover) {
  background-color: #f5f7fa;
}

.withdrawal-table :deep(.el-table__row:hover td) {
  background-color: #f5f7fa !important;
}

.pagination-container {
  margin-top: 20px;
  display: flex;
  justify-content: center;
  width: 100%;
}

.dialog-footer {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}

.bank-info {
  width: 100%;
}

.bank-info-row {
  display: flex;
  margin-bottom: 4px;
}

.bank-info-label {
  width: 80px;
  text-align: right;
  margin-right: 8px;
  white-space: nowrap;
}

.bank-info-value {
  flex: 1;
  text-align: left;
}

@media (max-width: 768px) {
  .search-form {
    flex-direction: column;
    align-items: stretch;
  }
  
  .search-form :deep(.el-form-item) {
    margin-right: 0;
    width: 100%;
  }
  
  .search-form :deep(.el-form-item:last-child) {
    margin-left: 0;
    margin-top: 10px;
  }
  
  .date-range-container {
    flex-direction: column;
    align-items: stretch;
  }
  
  .date-separator {
    margin: 5px 0;
    text-align: center;
  }
}
</style>