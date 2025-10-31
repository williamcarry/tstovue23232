<template>
  <div class="balance-history-container">
    <!-- 页面头部 -->
    <div class="page-header">
      <h2 class="page-title">
        <el-icon class="title-icon"><TrendCharts /></el-icon>
        余额变化记录
      </h2>
      <div class="header-actions">
        <el-button type="primary" @click="refreshData" :icon="Refresh">
          刷新数据
        </el-button>
      </div>
    </div>
    
    <!-- 搜索和筛选区域 -->
    <el-card class="filter-card">
      <div class="filter-header">
        <el-icon class="filter-icon"><Search /></el-icon>
        <span class="filter-title">筛选条件</span>
      </div>
      <el-form :model="searchForm" class="search-form" label-position="left" label-width="80px">
        <el-row :gutter="20" align="middle">
          <el-col :span="8" :xs="24">
            <el-form-item label="业务类型">
              <el-select v-model="searchForm.type" placeholder="请选择业务类型" clearable style="width: 100%;">
                <el-option label="全部" value="" />
                <el-option
                  v-for="option in typeOptions"
                  :key="option.value"
                  :label="option.label"
                  :value="option.value"
                />
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :span="16" :xs="24" class="action-col">
            <el-form-item label=" ">
              <div class="form-actions">
                <el-button type="primary" @click="searchBalanceHistories" :icon="Search">
                  搜索记录
                </el-button>
                <el-button @click="resetSearch" :icon="RefreshLeft">
                  重置条件
                </el-button>
              </div>
            </el-form-item>
          </el-col>
        </el-row>
      </el-form>
    </el-card>
    
    <!-- 余额变化记录表格 -->
    <el-card class="table-card">
      <template #header>
        <div class="table-header">
          <div class="table-header-title">
            <el-icon class="table-icon"><List /></el-icon>
            余额明细列表
          </div>
          <div class="table-header-info">
            共 {{ pagination.totalItems }} 条记录
          </div>
        </div>
      </template>
      
      <el-table 
        :data="balanceHistories" 
        class="balance-history-table" 
        v-loading="loading"
        :fit="true"
        :border="false"
        :header-cell-style="{ textAlign: 'center' }"
        :cell-style="{ textAlign: 'center' }"
        :stripe="true"
        empty-text="暂无余额变化记录"
      >
        <el-table-column label="序号" width="60" fixed>
          <template #default="scope">
            {{ (pagination.currentPage - 1) * pagination.itemsPerPage + scope.$index + 1 }}
          </template>
        </el-table-column>
        <el-table-column prop="description" label="描述" min-width="200" show-overflow-tooltip>
          <template #default="scope">
            <span class="description-text">{{ scope.row.description || '-' }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="amount" label="变化金额" min-width="120" sortable>
          <template #default="scope">
            <div class="amount-cell">
              <span 
                class="amount-value" 
                :class="getAmountClass(scope.row.amount)"
              >
                {{ formatCurrencyWithSign(scope.row.amount) }}
              </span>
            </div>
          </template>
        </el-table-column>
        <el-table-column prop="balanceAfter" label="账户余额" min-width="120" sortable>
          <template #default="scope">
            <div class="balance-cell">
              <span class="balance-value">{{ formatCurrency(scope.row.balanceAfter) }}</span>
            </div>
          </template>
        </el-table-column>
        <el-table-column prop="frozenAmount" label="冻结变化" min-width="120">
          <template #default="scope">
            <div class="frozen-cell">
              <span 
                class="frozen-value" 
                :class="getAmountClass(scope.row.frozenAmount)"
              >
                {{ formatCurrencyWithSign(scope.row.frozenAmount) }}
              </span>
            </div>
          </template>
        </el-table-column>
        <el-table-column prop="type" label="业务类型" min-width="120">
          <template #default="scope">
            <el-tag class="type-tag" :type="getTypeTagType(scope.row.type)" effect="dark">
              {{ getTypeText(scope.row.type, scope.row.typeDescriptions) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="referenceId" label="关联ID" min-width="120" show-overflow-tooltip />
        <el-table-column prop="createdAt" label="时间" min-width="160" sortable>
          <template #default="scope">
            <div class="time-cell">
              <div class="date">{{ formatDate(scope.row.createdAt) }}</div>
              <div class="time">{{ formatTime(scope.row.createdAt) }}</div>
            </div>
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
  </div>
</template>

<script setup>
import { ref, onMounted, defineExpose } from 'vue'
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
  ElRow,
  ElCol,
  ElIcon
} from 'element-plus'
import {
  TrendCharts,
  Search,
  Refresh,
  RefreshLeft,
  List
} from '@element-plus/icons-vue'

// 定义组件属性
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
const balanceHistories = ref([])
const loading = ref(false)
const pagination = ref({
  currentPage: 1,
  totalPages: 1,
  totalItems: 0,
  itemsPerPage: 20
})

// 业务类型选项
const typeOptions = ref([])

// 搜索表单
const searchForm = ref({
  type: ''
})

// 获取业务类型显示文本
const getTypeText = (type, typeDescriptions = {}) => {
  // 首先检查是否有自定义类型说明
  if (typeDescriptions && typeDescriptions[type]) {
    return typeDescriptions[type];
  }
  
  // 默认类型映射
  const typeMap = {
    'recharge': '充值',
    'withdraw': '提现',
    'order': '订单',
    'commission': '佣金',
    'refund': '退款',
    'system': '系统调整',
    'vip_purchase': '购买VIP',
    'admin_add': '后台增加余额',
    'admin_deduct': '后台减少余额'
  }
  return typeMap[type] || type
}

// 获取业务类型标签类型
const getTypeTagType = (type) => {
  const typeMap = {
    'recharge': 'success',
    'withdraw': 'warning',
    'order': 'primary',
    'commission': 'info',
    'refund': 'danger',
    'system': '',
    'vip_purchase': 'success',
    'admin_add': 'success',
    'admin_deduct': 'danger'
  }
  return typeMap[type] || 'info'
}

// 获取金额样式类
const getAmountClass = (amount) => {
  const value = parseFloat(amount)
  if (value > 0) return 'positive'
  if (value < 0) return 'negative'
  return 'zero'
}

// 加载余额变化记录数据
const loadBalanceHistoryData = async (page = 1, limit = 20) => {
  loading.value = true
  try {
    const params = new URLSearchParams({
      page: page,
      limit: limit,
      type: searchForm.value.type
    })
    
    const url = `/supplier${props.supplierLoginPath}/balance-history/list?${params}`
    
    const response = await fetch(url, {
      method: 'GET',
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    
    const result = await response.json()
    
    if (result.success) {
      balanceHistories.value = result.data
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
  loadBalanceHistoryData(pagination.value.currentPage, pagination.value.itemsPerPage)
}

// 搜索余额变化记录
const searchBalanceHistories = () => {
  pagination.value.currentPage = 1
  loadBalanceHistoryData(1, pagination.value.itemsPerPage)
}

// 重置搜索
const resetSearch = () => {
  searchForm.value = {
    type: ''
  }
  pagination.value.currentPage = 1
  loadBalanceHistoryData(1, pagination.value.itemsPerPage)
}

// 处理分页大小变化
const handleSizeChange = (val) => {
  pagination.value.itemsPerPage = val
  loadBalanceHistoryData(pagination.value.currentPage, val)
}

// 处理当前页变化
const handleCurrentChange = (val) => {
  pagination.value.currentPage = val
  loadBalanceHistoryData(val, pagination.value.itemsPerPage)
}

// 组件挂载时加载数据
onMounted(() => {
  // 加载业务类型映射
  loadTypeMapping()
  
  // 只有当 autoLoad 为 true 时才自动加载数据
  if (props.autoLoad) {
    loadBalanceHistoryData()
  }
})

// 暴露方法给父组件调用
defineExpose({
  loadBalanceHistoryData
})

// 获取业务类型映射
const loadTypeMapping = async () => {
  try {
    const url = `/supplier${props.supplierLoginPath}/balance-history/type-mapping`
    
    const response = await fetch(url, {
      method: 'GET',
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    
    const result = await response.json()
    
    if (result.success) {
      typeOptions.value = result.data
    }
  } catch (error) {
    console.error('加载业务类型映射失败:', error)
  }
}

// 格式化货币显示
const formatCurrency = (amount) => {
  if (amount === null || amount === undefined) return '0.00'
  return parseFloat(amount).toFixed(2)
}

// 格式化带符号的货币显示
const formatCurrencyWithSign = (amount) => {
  if (amount === null || amount === undefined) return '0.00'
  const value = parseFloat(amount)
  if (value > 0) {
    return '+' + value.toFixed(2)
  }
  return value.toFixed(2)
}

// 格式化日期
const formatDate = (dateString) => {
  if (!dateString) return '-'
  const date = new Date(dateString)
  return date.toLocaleDateString('zh-CN', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit'
  })
}

// 格式化时间
const formatTime = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleTimeString('zh-CN', {
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit'
  })
}
</script>

<style scoped>
.balance-history-container {
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

.balance-history-table {
  width: 100% !important;
  font-size: 14px;
}

.balance-history-table :deep(.el-table__header th) {
  background-color: #f5f7fa;
  color: #606266;
  font-weight: bold;
}

/* 解决鼠标悬停时序号列变色延迟的问题 */
.balance-history-table :deep(.el-table__body td) {
  transition: background-color 0.3s;
}

.balance-history-table :deep(.el-table__row:hover) {
  background-color: #f5f7fa;
}

.balance-history-table :deep(.el-table__row:hover td) {
  background-color: #f5f7fa !important;
}

/* 确保固定列也有一致的悬停效果 */
.balance-history-table :deep(.el-table__fixed .el-table__row:hover td) {
  background-color: #f5f7fa !important;
}

/* 确保序号列在悬停时立即响应 */
.balance-history-table :deep(.el-table__row:hover .el-table-fixed-column--left) {
  background-color: #f5f7fa !important;
}

.time-cell {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.date {
  font-size: 14px;
  color: #303133;
}

.time {
  font-size: 12px;
  color: #909399;
}

.type-tag {
  font-size: 12px;
}

.description-text {
  font-size: 14px;
  color: #303133;
}

.amount-cell {
  display: flex;
  justify-content: center;
}

.amount-value {
  font-size: 14px;
}

.amount-value.positive {
  color: #67c23a;
}

.amount-value.negative {
  color: #f56c6c;
}

.amount-value.zero {
  color: #909399;
}

.balance-cell {
  display: flex;
  justify-content: center;
}

.balance-value {
  font-size: 14px;
  color: #409eff;
}

.frozen-cell {
  display: flex;
  justify-content: center;
}

.frozen-value {
  font-size: 14px;
}

.frozen-value.positive {
  color: #67c23a;
}

.frozen-value.negative {
  color: #f56c6c;
}

.frozen-value.zero {
  color: #909399;
}

.pagination-container {
  margin-top: 20px;
  display: flex;
  justify-content: center;
  width: 100%;
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
