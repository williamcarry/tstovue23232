<template>
  <div class="withdrawal-list">
    <!-- 页面头部 -->
    <div class="page-header">
      <h2 class="page-title">
        <el-icon class="title-icon"><Wallet /></el-icon>
        提现申请记录
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
          <el-col :span="8" :xs="24">
            <el-form-item label="状态" class="right-aligned-label">
              <el-select v-model="searchForm.status" placeholder="请选择状态" clearable>
                <el-option label="全部" value="" />
                <el-option label="待审核" value="pending" />
                <el-option label="已通过" value="approved" />
                <el-option label="已拒绝" value="rejected" />
              </el-select>
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
        <el-table-column prop="statusText" label="状态" min-width="100">
          <template #default="scope">
            <el-tag :type="getStatusTagType(scope.row.status)">
              {{ scope.row.statusText }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="remark" label="审核意见" min-width="150" />
        <el-table-column prop="reviewedBy" label="审核人" min-width="100" />
        <el-table-column prop="createdAt" label="申请时间" min-width="160" />
        <el-table-column prop="reviewedAt" label="审核时间" min-width="160" />
        <el-table-column label="打款截图" min-width="120">
          <template #default="scope">
            <div v-if="scope.row.paymentScreenshotUrl">
              <el-image
                :src="scope.row.paymentScreenshotUrl"
                fit="cover"
                style="width: 80px; height: 80px; cursor: pointer;"
                :preview-src-list="[scope.row.paymentScreenshotUrl]"
                preview-teleported
                lazy
              />
            </div>
            <div v-else>
              <span>-</span>
            </div>
          </template>
        </el-table-column>
        <el-table-column label="操作" min-width="100">
          <template #default="scope">
            <el-button size="small" @click="viewDetail(scope.row)">查看</el-button>
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
    <el-dialog v-model="detailDialogVisible" title="提现申请详情" width="500px">
      <el-form :model="currentWithdrawal" label-width="100px">
        <el-form-item label="提现金额：">
          <span style="color: #f56c6c; font-weight: bold;">{{ formatCurrency(currentWithdrawal.amount) }}</span>
        </el-form-item>
        <el-form-item label="收款信息：">
          <div style="white-space: pre-line; line-height: 1.8; font-family: 'Consolas', 'Monaco', monospace;">{{ currentWithdrawal.paymentInfo || '-' }}</div>
        </el-form-item>
        <el-form-item label="状态：">
          <el-tag :type="getStatusTagType(currentWithdrawal.status)">
            {{ currentWithdrawal.statusText }}
          </el-tag>
        </el-form-item>
        <el-form-item label="申请时间：">
          <span>{{ currentWithdrawal.createdAt }}</span>
        </el-form-item>
        <el-form-item label="审核时间：">
          <span>{{ currentWithdrawal.reviewedAt || '-' }}</span>
        </el-form-item>
        <el-form-item label="审核意见：">
          <span>{{ currentWithdrawal.remark || '-' }}</span>
        </el-form-item>
        <el-form-item label="审核人：">
          <span>{{ currentWithdrawal.reviewedBy || '-' }}</span>
        </el-form-item>
        <el-form-item label="打款截图：">
          <div v-if="currentWithdrawal.paymentScreenshotUrl">
            <el-image
              :src="currentWithdrawal.paymentScreenshotUrl"
              fit="contain"
              style="max-width: 200px; max-height: 200px; cursor: pointer;"
              :preview-src-list="[currentWithdrawal.paymentScreenshotUrl]"
              preview-teleported
              lazy
            />
          </div>
          <div v-else>
            <span>-</span>
          </div>
        </el-form-item>
        <el-form-item label="最后更新：">
          <span>{{ currentWithdrawal.updatedAt || '-' }}</span>
        </el-form-item>
      </el-form>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="detailDialogVisible = false">关闭</el-button>
        </span>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, onMounted, defineProps, defineExpose } from 'vue'
import {
  ElCard,
  ElTable,
  ElTableColumn,
  ElButton,
  ElTag,
  ElDialog,
  ElForm,
  ElFormItem,
  ElSelect,
  ElOption,
  ElInput,
  ElDatePicker,
  ElPagination,
  ElMessage,
  ElImage,
  ElRow,
  ElCol,
  ElIcon
} from 'element-plus'
import { Wallet, Refresh, Search, RefreshLeft, List } from '@element-plus/icons-vue'

const props = defineProps({
  supplierLoginPath: {
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
const currentWithdrawal = ref({})

const pagination = ref({
  currentPage: 1,
  totalPages: 1,
  totalItems: 0,
  itemsPerPage: 20
})

// 搜索表单
const searchForm = ref({
  status: ''
})

// 获取状态标签类型
const getStatusTagType = (status) => {
  const typeMap = {
    'pending': 'warning',
    'approved': 'success',
    'rejected': 'danger'
  }
  return typeMap[status] || 'info'
}

// 格式化货币
const formatCurrency = (value) => {
  if (value === null || value === undefined) return '¥0.00'
  return '¥' + parseFloat(value).toFixed(2)
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
    
    // 添加状态筛选条件
    if (searchForm.value.status) {
      paramsObj.status = searchForm.value.status;
    }
    
    // 构建查询字符串
    const params = new URLSearchParams(paramsObj).toString();
    
    const response = await fetch(`/supplier${props.supplierLoginPath}/withdrawal/list?${params}`, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
      }
    })
    
    const result = await response.json()
    
    if (result.success) {
      withdrawals.value = result.data
      pagination.value = {
        ...pagination.value,
        ...result.pagination
      }
    } else {
      ElMessage.error(result.message || '获取数据失败')
    }
  } catch (error) {
    ElMessage.error('网络错误：' + error.message)
  } finally {
    loading.value = false
  }
}

// 搜索提现申请
const searchWithdrawals = () => {
  pagination.value.currentPage = 1
  loadWithdrawalData(1, pagination.value.itemsPerPage)
}

// 重置搜索
const resetSearch = () => {
  searchForm.value.status = ''
  pagination.value.currentPage = 1
  loadWithdrawalData(1, pagination.value.itemsPerPage)
}

// 刷新数据
const refreshData = () => {
  loadWithdrawalData(pagination.value.currentPage, pagination.value.itemsPerPage)
}

// 处理分页大小改变
const handleSizeChange = (val) => {
  pagination.value.itemsPerPage = val
  loadWithdrawalData(1, val)
}

// 处理当前页改变
const handleCurrentChange = (val) => {
  pagination.value.currentPage = val
  loadWithdrawalData(val, pagination.value.itemsPerPage)
}

// 查看详情
const viewDetail = (row) => {
  currentWithdrawal.value = { ...row }
  detailDialogVisible.value = true
}

// 在组件挂载时加载数据（如果autoLoad为true）
onMounted(() => {
  if (props.autoLoad) {
    loadWithdrawalData()
  }
})

// 暴露方法给父组件
defineExpose({
  loadWithdrawalData,
  refreshData
})
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

.search-form :deep(.right-aligned-label .el-form-item__label) {
  text-align: right;
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
  text-align: left;
}

.bank-info-row {
  display: flex;
  margin-bottom: 5px;
}

.bank-info-label {
  min-width: 80px;
  text-align: right;
  margin-right: 10px;
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
}
</style>