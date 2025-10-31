<template>
  <div class="product-list">
    <!-- 统计卡片 -->
    <el-row :gutter="20" class="statistics-row" v-if="statistics">
      <el-col :span="4">
        <el-card class="stat-card">
          <div class="stat-number">{{ statistics.total }}</div>
          <div class="stat-label">全部商品</div>
        </el-card>
      </el-col>
      <el-col :span="4">
        <el-card class="stat-card">
          <div class="stat-number pending">{{ statistics.pending }}</div>
          <div class="stat-label">待审核</div>
        </el-card>
      </el-col>
      <el-col :span="4">
        <el-card class="stat-card">
          <div class="stat-number approved">{{ statistics.approved }}</div>
          <div class="stat-label">已通过</div>
        </el-card>
      </el-col>
      <el-col :span="4">
        <el-card class="stat-card">
          <div class="stat-number rejected">{{ statistics.rejected }}</div>
          <div class="stat-label">已拒绝</div>
        </el-card>
      </el-col>
      <el-col :span="4">
        <el-card class="stat-card">
          <div class="stat-number draft">{{ statistics.draft }}</div>
          <div class="stat-label">草稿</div>
        </el-card>
      </el-col>
      <el-col :span="4">
        <el-card class="stat-card">
          <div class="stat-number offline">{{ statistics.offline }}</div>
          <div class="stat-label">已下架</div>
        </el-card>
      </el-col>
    </el-row>

    <!-- 搜索和筛选 -->
    <el-card class="search-card">
      <el-form :model="searchParams" inline>
        <el-form-item label="关键词">
          <el-input
            v-model="searchParams.keyword"
            placeholder="SKU/SPU/商品名称"
            clearable
            style="width: 250px"
            @keyup.enter="handleSearch"
          >
            <template #prefix>
              <el-icon><Search /></el-icon>
            </template>
          </el-input>
        </el-form-item>
        
        <el-form-item label="状态">
          <el-select v-model="searchParams.status" placeholder="全部" clearable style="width: 120px">
            <el-option label="全部" value="" />
            <el-option label="草稿" value="draft" />
            <el-option label="待审核" value="pending" />
            <el-option label="已通过" value="approved" />
            <el-option label="已拒绝" value="rejected" />
            <el-option label="已下架" value="offline" />
          </el-select>
        </el-form-item>
        
        <el-form-item label="供应商用户名">
          <el-input
            v-model="searchParams.supplierUsername"
            placeholder="请输入供应商用户名"
            clearable
            style="width: 200px"
            @keyup.enter="handleSearch"
          >
            <template #prefix>
              <el-icon><User /></el-icon>
            </template>
          </el-input>
        </el-form-item>
        
        <el-form-item>
          <el-button type="primary" @click="handleSearch">
            <el-icon><Search /></el-icon>
            搜索
          </el-button>
          <el-button @click="handleReset">重置</el-button>
        </el-form-item>
      </el-form>
    </el-card>

    <!-- 操作栏 -->
    <el-card class="operation-card">
      <el-button type="success" :disabled="selectedIds.length === 0" @click="handleBatchAudit('approved')">
        批量通过
      </el-button>
      <el-button type="danger" :disabled="selectedIds.length === 0" @click="handleBatchAudit('rejected')">
        批量拒绝
      </el-button>
      <el-button @click="handleRefresh">
        <el-icon><Refresh /></el-icon>
        刷新
      </el-button>
    </el-card>

    <!-- 商品列表表格 -->
    <el-card class="table-card">
      <el-table
        :data="products"
        v-loading="loading"
        @selection-change="handleSelectionChange"
        style="width: 100%; margin-top: 20px;"
        :fit="true"
        :border="true"
        :stripe="true"
        :header-cell-style="{ textAlign: 'center', backgroundColor: '#f5f7fa' }"
        :cell-style="{ textAlign: 'center' }"
      >
        <el-table-column type="selection" width="55" />
        
        <el-table-column label="序号" width="70">
          <template #default="scope">
            {{ (pagination.currentPage - 1) * pagination.itemsPerPage + scope.$index + 1 }}
          </template>
        </el-table-column>
        
        <el-table-column label="供应商" width="150">
          <template #default="scope">
            <span 
              v-if="scope.row.supplier" 
              class="supplier-info"
              :title="`${scope.row.supplier.username}/${scope.row.supplier.companyName || '-'}`"
            >
              {{ formatSupplierInfo(scope.row.supplier) }}
            </span>
            <span v-else>-</span>
          </template>
        </el-table-column>
        
        <el-table-column label="商品图片" width="100">
          <template #default="scope">
            <el-image
              v-if="scope.row.mainImages && scope.row.mainImages.length > 0"
              :src="scope.row.mainImages[0].url"
              fit="cover"
              style="width: 60px; height: 60px; border-radius: 4px"
              :preview-src-list="scope.row.mainImages.map(img => img.url)"
              preview-teleported
            />
            <el-image
              v-else-if="scope.row.mainImage"
              :src="scope.row.mainImage"
              fit="cover"
              style="width: 60px; height: 60px; border-radius: 4px"
              :preview-src-list="[scope.row.mainImage]"
              preview-teleported
            />
            <span v-else>无图片</span>
          </template>
        </el-table-column>
        
        <el-table-column label="SKU/SPU" min-width="200">
          <template #default="scope">
            <div>
              <div>SKU: {{ scope.row.sku || '-' }}</div>
              <div>SPU: {{ scope.row.spu || '-' }}</div>
            </div>
          </template>
        </el-table-column>
        
        <el-table-column prop="title" label="商品名称" min-width="200" show-overflow-tooltip />
        
        <el-table-column label="分类" width="120">
          <template #default="scope">
            {{ scope.row.category || '-' }}
          </template>
        </el-table-column>
        
        <el-table-column prop="brand" label="品牌" width="100" />
        
        <el-table-column label="状态" width="100">
          <template #default="scope">
            <el-tag :type="getStatusTagType(scope.row.status)">
              {{ scope.row.statusText }}
            </el-tag>
          </template>
        </el-table-column>
        
        <el-table-column prop="salesCount" label="销量" width="80" align="center" />
        
        <el-table-column prop="createdAt" label="创建时间" width="160" />
        
        <el-table-column label="操作" width="220" fixed="right">
          <template #default="scope">
            <div class="button-group">
              <el-button
                v-if="scope.row.status === 'pending'"
                type="success"
                size="small"
                @click="handleAudit(scope.row, 'approved')"
              >
                通过
              </el-button>
              <el-button
                v-if="scope.row.status === 'pending'"
                type="warning"
                size="small"
                @click="handleAudit(scope.row, 'rejected')"
              >
                拒绝
              </el-button>
              <el-button
                size="small"
                @click="handleView(scope.row)"
              >
                查看
              </el-button>
              <el-button
                type="danger"
                size="small"
                @click="handleDelete(scope.row)"
              >
                删除
              </el-button>
            </div>
          </template>
        </el-table-column>
      </el-table>

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
    </el-card>

    <!-- 审核对话框 -->
    <el-dialog v-model="auditDialogVisible" title="审核商品" width="500px">
      <el-form :model="auditForm" label-width="100px">
        <el-form-item label="审核结果">
          <el-radio-group v-model="auditForm.status">
            <el-radio label="approved">通过</el-radio>
            <el-radio label="rejected">拒绝</el-radio>
          </el-radio-group>
        </el-form-item>
        <el-form-item label="审核备注">
          <el-input
            v-model="auditForm.remark"
            type="textarea"
            :rows="4"
            placeholder="请输入审核备注（选填）"
          />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="auditDialogVisible = false">取消</el-button>
        <el-button type="primary" @click="confirmAudit" :loading="auditing">确定</el-button>
      </template>
    </el-dialog>

    <!-- 查看商品详情对话框 -->
    <el-dialog v-model="viewDialogVisible" title="商品详情" width="800px">
      <el-descriptions v-if="currentProduct" :column="2" border>
        <el-descriptions-item label="SKU">{{ currentProduct.sku }}</el-descriptions-item>
        <el-descriptions-item label="SPU">{{ currentProduct.spu }}</el-descriptions-item>
        <el-descriptions-item label="商品名称">{{ currentProduct.title }}</el-descriptions-item>
        <el-descriptions-item label="英文名称">{{ currentProduct.titleEn || '-' }}</el-descriptions-item>
        <el-descriptions-item label="供应商">
          {{ currentProduct.supplier?.companyName || currentProduct.supplier?.username || '-' }}
        </el-descriptions-item>
        <el-descriptions-item label="品牌">{{ currentProduct.brand || '-' }}</el-descriptions-item>
        <el-descriptions-item label="分类">{{ currentProduct.category || '-' }}</el-descriptions-item>
        <el-descriptions-item label="状态">
          <el-tag :type="getStatusTagType(currentProduct.status)">
            {{ currentProduct.statusText }}
          </el-tag>
        </el-descriptions-item>
        <el-descriptions-item label="浏览量">{{ currentProduct.viewCount }}</el-descriptions-item>
        <el-descriptions-item label="销量">{{ currentProduct.salesCount }}</el-descriptions-item>
        <el-descriptions-item label="创建时间">{{ currentProduct.createdAt }}</el-descriptions-item>
        <el-descriptions-item label="更新时间">{{ currentProduct.updatedAt }}</el-descriptions-item>
        <el-descriptions-item label="审核备注">{{ currentProduct.auditRemark || '-' }}</el-descriptions-item>
        <el-descriptions-item label="审核人">{{ currentProduct.auditedBy || '-' }}</el-descriptions-item>
        <el-descriptions-item label="审核时间">{{ currentProduct.auditedAt || '-' }}</el-descriptions-item>
        <el-descriptions-item label="首次上架时间">{{ currentProduct.publishDate || '-' }}</el-descriptions-item>
        <el-descriptions-item label="商品图片" :span="2">
          <el-image
            v-if="currentProduct.mainImages && currentProduct.mainImages.length > 0"
            :src="currentProduct.mainImages[0].url"
            fit="contain"
            style="max-width: 200px; max-height: 200px"
            :preview-src-list="currentProduct.mainImages.map(img => img.url)"
            preview-teleported
          />
          <el-image
            v-else-if="currentProduct.mainImage"
            :src="currentProduct.mainImage"
            fit="contain"
            style="max-width: 200px; max-height: 200px"
            :preview-src-list="[currentProduct.mainImage]"
            preview-teleported
          />
          <span v-else>无图片</span>
        </el-descriptions-item>
      </el-descriptions>
      <template #footer>
        <el-button @click="viewDialogVisible = false">关闭</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, defineProps, defineExpose } from 'vue'
import {
  ElCard,
  ElRow,
  ElCol,
  ElForm,
  ElFormItem,
  ElInput,
  ElSelect,
  ElOption,
  ElButton,
  ElTable,
  ElTableColumn,
  ElPagination,
  ElImage,
  ElTag,
  ElDialog,
  ElRadioGroup,
  ElRadio,
  ElDescriptions,
  ElDescriptionsItem,
  ElMessage,
  ElMessageBox,
  ElIcon
} from 'element-plus'
import { Search, Refresh, User } from '@element-plus/icons-vue'

const props = defineProps({
  adminLoginPath: {
    type: String,
    required: true
  },
  autoLoad: {
    type: Boolean,
    default: false
  }
})

const loading = ref(false)
const products = ref([])
const statistics = ref(null)
const suppliers = ref([])
const selectedIds = ref([])
const auditDialogVisible = ref(false)
const viewDialogVisible = ref(false)
const auditing = ref(false)
const currentProduct = ref(null)

// 搜索参数
const searchParams = reactive({
  keyword: '',
  status: '',
  supplierUsername: '', // 供应商用户名搜索字段
  sortField: 'createdAt',
  sortOrder: 'DESC'
})

// 分页数据
const pagination = reactive({
  currentPage: 1,
  totalPages: 1,
  totalItems: 0,
  itemsPerPage: 20
})

// 审核表单
const auditForm = reactive({
  productId: null,
  status: 'approved',
  remark: ''
})

// 搜索
const handleSearch = () => {
  pagination.currentPage = 1
  loadProductData()
}

// 重置
const handleReset = () => {
  searchParams.keyword = ''
  searchParams.status = ''
  searchParams.supplierUsername = ''
  searchParams.sortField = 'createdAt'
  searchParams.sortOrder = 'DESC'
  pagination.currentPage = 1
  loadProductData()
}

// 加载商品数据
const loadProductData = async (page = 1, limit = 20) => {
  loading.value = true
  try {
    // 构建查询参数
    const params = new URLSearchParams({
      page: page,
      limit: limit,
      keyword: searchParams.keyword,
      status: searchParams.status,
      sortField: searchParams.sortField,
      sortOrder: searchParams.sortOrder
    })

    // 如果供应商用户名不为空，则添加到参数中
    if (searchParams.supplierUsername !== '') {
      params.append('supplierUsername', searchParams.supplierUsername)
    }

    const url = `/admin${props.adminLoginPath}/product/list/data?${params}`

    const response = await fetch(url, {
      method: 'GET',
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      }
    })

    const result = await response.json()

    if (result.success) {
      products.value = result.data
      pagination.currentPage = result.pagination.currentPage
      pagination.totalPages = result.pagination.totalPages
      pagination.totalItems = result.pagination.totalItems
      pagination.itemsPerPage = result.pagination.itemsPerPage
    } else {
      ElMessage.error(result.message || '加载数据失败')
    }
  } catch (error) {
    console.error('加载数据失败:', error)
    ElMessage.error('网络错误，请稍后重试')
  } finally {
    loading.value = false
  }
}

// 加载统计数据
const loadStatistics = async () => {
  try {
    const response = await fetch(`/admin${props.adminLoginPath}/product/statistics`, {
      method: 'GET',
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      }
    })

    const result = await response.json()

    if (result.success) {
      statistics.value = result.data
    }
  } catch (error) {
    console.error('加载统计数据失败:', error)
  }
}

// 加载供应商列表
const loadSuppliers = async () => {
  try {
    const response = await fetch(`/admin${props.adminLoginPath}/product/suppliers`, {
      method: 'GET',
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      }
    })

    const result = await response.json()

    if (result.success) {
      suppliers.value = result.data
    }
  } catch (error) {
    console.error('加载供应商列表失败:', error)
  }
}

// 刷新
const handleRefresh = () => {
  loadProductData(pagination.currentPage, pagination.itemsPerPage)
  loadStatistics()
}

// 分页大小改变
const handleSizeChange = (val) => {
  pagination.itemsPerPage = val
  loadProductData(pagination.currentPage, val)
}

// 当前页改变
const handleCurrentChange = (val) => {
  pagination.currentPage = val
  loadProductData(val, pagination.itemsPerPage)
}

// 选择改变
const handleSelectionChange = (selection) => {
  selectedIds.value = selection.map(item => item.id)
}

// 审核商品
const handleAudit = (product, status) => {
  auditForm.productId = product.id
  auditForm.status = status
  auditForm.remark = ''
  auditDialogVisible.value = true
}

// 确认审核
const confirmAudit = async () => {
  auditing.value = true
  try {
    const response = await fetch(`/admin${props.adminLoginPath}/product/audit/${auditForm.productId}`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify({
        status: auditForm.status,
        remark: auditForm.remark
      })
    })

    const result = await response.json()

    if (result.success) {
      ElMessage.success(result.message)
      auditDialogVisible.value = false
      loadProductData(pagination.currentPage, pagination.itemsPerPage)
      loadStatistics()
    } else {
      ElMessage.error(result.message || '审核失败')
    }
  } catch (error) {
    console.error('审核失败:', error)
    ElMessage.error('网络错误，请稍后重试')
  } finally {
    auditing.value = false
  }
}

// 批量审核
const handleBatchAudit = async (status) => {
  if (selectedIds.value.length === 0) {
    ElMessage.warning('请选择要审核的商品')
    return
  }

  const statusText = status === 'approved' ? '通过' : '拒绝'
  
  try {
    await ElMessageBox.confirm(
      `确定要批量${statusText}选中的 ${selectedIds.value.length} 个商品吗？`,
      '提示',
      {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }
    )

    const response = await fetch(`/admin${props.adminLoginPath}/product/batch-audit`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify({
        ids: selectedIds.value,
        status: status
      })
    })

    const result = await response.json()

    if (result.success) {
      ElMessage.success(result.message)
      selectedIds.value = []
      loadProductData(pagination.currentPage, pagination.itemsPerPage)
      loadStatistics()
    } else {
      ElMessage.error(result.message || '批量审核失败')
    }
  } catch (error) {
    if (error !== 'cancel') {
      console.error('批量审核失败:', error)
      ElMessage.error('网络错误，请稍后重试')
    }
  }
}

// 查看商品
const handleView = (product) => {
  // 触发导航事件，在新标签页中打开商品详情
  window.dispatchEvent(new CustomEvent('navigate-to-product-view', {
    detail: { productId: product.id }
  }))
}

// 删除商品
const handleDelete = async (product) => {
  try {
    await ElMessageBox.confirm(
      `确定要删除商品 "${product.title}" 吗？此操作不可恢复！`,
      '警告',
      {
        confirmButtonText: '确定删除',
        cancelButtonText: '取消',
        type: 'error'
      }
    )

    const response = await fetch(`/admin${props.adminLoginPath}/product/delete/${product.id}`, {
      method: 'POST',
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      }
    })

    const result = await response.json()

    if (result.success) {
      ElMessage.success(result.message)
      loadProductData(pagination.currentPage, pagination.itemsPerPage)
      loadStatistics()
    } else {
      ElMessage.error(result.message || '删除失败')
    }
  } catch (error) {
    if (error !== 'cancel') {
      console.error('删除失败:', error)
      ElMessage.error('网络错误，请稍后重试')
    }
  }
}

// 获取状态标签类型
const getStatusTagType = (status) => {
  const typeMap = {
    'draft': 'info',
    'pending': 'warning',
    'approved': 'success',
    'rejected': 'danger',
    'offline': ''
  }
  return typeMap[status] || 'info'
}

// 格式化供应商信息显示
const formatSupplierInfo = (supplier) => {
  if (!supplier) return '-';
  
  const username = supplier.username || '';
  const companyName = supplier.companyName || '';
  
  // 如果公司名称为空，只显示用户名
  if (!companyName) {
    return truncateText(username, 20);
  }
  
  // 组合显示 username/companyName
  const combined = `${username}/${companyName}`;
  return truncateText(combined, 20);
};

// 截断文本并添加省略号
const truncateText = (text, maxLength) => {
  if (!text) return '';
  if (text.length <= maxLength) return text;
  return text.substring(0, maxLength) + '...';
};

// 初始化数据加载
const initData = () => {
  loadProductData()
  loadStatistics()
  loadSuppliers()
}

// 暴露方法给父组件
defineExpose({
  loadProductData,
  loadStatistics,
  initData
})

// 组件挂载时
onMounted(() => {
  if (props.autoLoad) {
    initData()
  }
})
</script>

<style scoped>
.statistics-row {
  margin-bottom: 20px;
}

.stat-card {
  text-align: center;
  cursor: pointer;
  transition: all 0.3s;
}

.stat-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.stat-number {
  font-size: 32px;
  font-weight: bold;
  color: #409eff;
  margin-bottom: 8px;
}

.stat-number.pending {
  color: #e6a23c;
}

.stat-number.approved {
  color: #67c23a;
}

.stat-number.rejected {
  color: #f56c6c;
}

.stat-number.draft {
  color: #909399;
}

.stat-number.offline {
  color: #909399;
}

.stat-label {
  font-size: 14px;
  color: #606266;
}

.search-card,
.operation-card,
.table-card {
  margin-bottom: 20px;
}

.pagination-container {
  margin-top: 20px;
  display: flex;
  justify-content: center;
  width: 100%;
}

:deep(.el-table) {
  font-size: 14px;
}

:deep(.el-table th) {
  background-color: #f5f7fa;
}

.button-group {
  display: flex;
  gap: 5px;
  flex-wrap: nowrap;
}

.button-group .el-button {
  margin: 0;
}

.supplier-info {
  display: inline-block;
  max-width: 100%;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  vertical-align: middle;
}
</style>