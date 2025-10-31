<template>
  <div class="product-list-container">
    <el-card class="box-card">
      <template #header>
        <div class="card-header">
          <span class="header-title">商品列表</span>
          <div class="header-actions">
            <el-button type="primary" @click="navigateToAdd">添加商品</el-button>
          </div>
        </div>
      </template>

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
            <div class="stat-number draft">{{ statistics.draft }}</div>
            <div class="stat-label">草稿</div>
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
            <div class="stat-label">已上架</div>
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
            <div class="stat-number offline">{{ statistics.offline }}</div>
            <div class="stat-label">已下架</div>
          </el-card>
        </el-col>
      </el-row>

      <!-- 搜索筛选栏 -->
      <div class="search-bar">
        <el-row :gutter="20">
          <el-col :span="6">
            <el-input
              v-model="searchParams.keyword"
              placeholder="搜索SKU/SPU/商品名称"
              clearable
              @keyup.enter="handleSearch"
            >
              <template #prefix>
                <el-icon><Search /></el-icon>
              </template>
            </el-input>
          </el-col>
          <el-col :span="4">
            <el-select v-model="searchParams.status" placeholder="商品状态" clearable>
              <el-option label="全部状态" value="" />
              <el-option label="草稿" value="draft" />
              <el-option label="待审核" value="pending" />
              <el-option label="已上架" value="approved" />
              <el-option label="已拒绝" value="rejected" />
              <el-option label="已下架" value="offline" />
            </el-select>
          </el-col>
          <el-col :span="4">
            <el-select v-model="searchParams.isNew" placeholder="是否新品" clearable>
              <el-option label="全部" value="" />
              <el-option label="新品" :value="true" />
              <el-option label="非新品" :value="false" />
            </el-select>
          </el-col>
          <el-col :span="4">
            <el-select v-model="searchParams.isHot" placeholder="是否热卖" clearable>
              <el-option label="全部" value="" />
              <el-option label="热卖" :value="true" />
              <el-option label="非热卖" :value="false" />
            </el-select>
          </el-col>
          <el-col :span="6">
            <el-button type="primary" @click="handleSearch">搜索</el-button>
            <el-button @click="handleReset">重置</el-button>
          </el-col>
        </el-row>
      </div>

      <!-- 商品表格 -->
      <el-table
        :data="products"
        style="width: 100%; margin-top: 20px;"
        v-loading="loading"
        :fit="true"
        :border="true"
        :stripe="true"
        :header-cell-style="{ textAlign: 'center', backgroundColor: '#f5f7fa' }"
        :cell-style="{ textAlign: 'center' }"
      >
        <el-table-column prop="id" label="ID" min-width="80" />
        <el-table-column label="商品图片" min-width="120">
          <template #default="scope">
            <el-image
              v-if="scope && scope.row && scope.row.mainImages && scope.row.mainImages.length > 0"
              :src="scope.row.mainImages[0].url"
              :preview-src-list="scope.row.mainImages.map(img => img.url)"
              :preview-teleported="true"
              fit="cover"
              style="width: 80px; height: 80px; cursor: pointer;"
            />
            <span v-else class="no-image">暂无图片</span>
          </template>
        </el-table-column>
        <el-table-column label="SKU/SPU" min-width="200" show-overflow-tooltip>
          <template #default="scope">
            <div>
              <div>SKU: {{ scope.row.sku || '-' }}</div>
              <div>SPU: {{ scope.row.spu || '-' }}</div>
            </div>
          </template>
        </el-table-column>
        <el-table-column prop="title" label="商品名称" min-width="200" show-overflow-tooltip />
        <el-table-column label="分类" min-width="150">
          <template #default="scope">
            {{ (scope && scope.row && scope.row.category) || '-' }}
          </template>
        </el-table-column>
        <el-table-column label="状态" min-width="100">
          <template #default="scope">
            <el-tag v-if="scope && scope.row" :type="getStatusType(scope.row.status)">
              {{ scope.row.statusText }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="标签" min-width="150">
          <template #default="scope">
            <template v-if="scope && scope.row && scope.row.tags && scope.row.tags.length > 0">
              <!-- 只显示自定义标签 -->
              <el-tag
                v-for="(tag, index) in scope.row.tags"
                :key="index"
                type="info"
                size="small"
                style="margin-right: 5px;"
              >
                {{ tag }}
              </el-tag>
            </template>
            <template v-else>
              <span>-</span>
            </template>
          </template>
        </el-table-column>
        <el-table-column prop="salesCount" label="销量" min-width="80" />
        <el-table-column prop="createdAt" label="创建时间" min-width="180" />
        <el-table-column label="操作" min-width="180" fixed="right">
          <template #default="scope">
            <div class="button-group">
              <template v-if="scope && scope.row">
                <el-button 
                  type="primary" 
                  size="small" 
                  @click="handleEdit(scope.row)"
                  :disabled="scope.row.status === 'pending'"
                >
                  编辑
                </el-button>
                <el-button 
                  type="danger" 
                  size="small" 
                  @click="handleDelete(scope.row)"
                >
                  删除
                </el-button>
              </template>
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
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, defineProps, defineExpose } from 'vue'
import {
  ElCard,
  ElRow,
  ElCol,
  ElInput,
  ElSelect,
  ElOption,
  ElButton,
  ElTable,
  ElTableColumn,
  ElPagination,
  ElImage,
  ElTag,
  ElMessage,
  ElMessageBox,
  ElIcon
} from 'element-plus'
import { Search } from '@element-plus/icons-vue'

const props = defineProps({
  supplierLoginPath: {
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

// 搜索参数
const searchParams = reactive({
  keyword: '',
  status: '',
  categoryId: '',
  subcategoryId: '',
  itemId: '',
  isNew: '',
  isHot: '',
  isPromotion: '',
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

// 加载商品数据
const loadProductData = async (page = 1, limit = 20) => {
  loading.value = true
  try {
    const params = new URLSearchParams({
      page: page,
      limit: limit,
      ...searchParams
    })

    const url = `/supplier${props.supplierLoginPath}/product/list/data?${params}`

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
    const response = await fetch(`/supplier${props.supplierLoginPath}/product/statistics`, {
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

// 搜索
const handleSearch = () => {
  pagination.currentPage = 1
  loadProductData(1, pagination.itemsPerPage)
}

// 重置
const handleReset = () => {
  searchParams.keyword = ''
  searchParams.status = ''
  searchParams.categoryId = ''
  searchParams.subcategoryId = ''
  searchParams.itemId = ''
  searchParams.isNew = ''
  searchParams.isHot = ''
  searchParams.isPromotion = ''
  handleSearch()
}

// 分页大小变化
const handleSizeChange = (val) => {
  pagination.itemsPerPage = val
  loadProductData(pagination.currentPage, val)
}

// 当前页变化
const handleCurrentChange = (val) => {
  pagination.currentPage = val
  loadProductData(val, pagination.itemsPerPage)
}

// 导航到添加商品页面
const navigateToAdd = () => {
  window.dispatchEvent(new CustomEvent('navigate-to', {
    detail: { key: 'product-add' }
  }))
}

// 编辑商品（导航到编辑页面）
const handleEdit = (row) => {
  window.dispatchEvent(new CustomEvent('navigate-to', {
    detail: { key: 'product-edit', params: { id: row.id } }
  }))
}

// 删除
const handleDelete = (row) => {
  ElMessageBox.confirm(
    `确定要删除商品"${row.title}"吗？`,
    '提示',
    {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning',
    }
  ).then(async () => {
    try {
      const response = await fetch(`/supplier${props.supplierLoginPath}/product/delete/${row.id}`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        }
      })

      const result = await response.json()

      if (result.success) {
        ElMessage.success('删除成功')
        loadProductData(pagination.currentPage, pagination.itemsPerPage)
        loadStatistics()
      } else {
        ElMessage.error(result.message || '删除失败')
      }
    } catch (error) {
      console.error('删除失败:', error)
      ElMessage.error('网络错误，请稍后重试')
    }
  }).catch(() => {
    // 取消删除
  })
}

// 获取状态类型
const getStatusType = (status) => {
  const typeMap = {
    draft: 'info',
    pending: 'warning',
    approved: 'success',
    rejected: 'danger',
    offline: ''
  }
  return typeMap[status] || ''
}

// 组件挂载
onMounted(() => {
  if (props.autoLoad) {
    loadProductData()
    loadStatistics()
  }
})

// 暴露方法供父组件调用（按需加载）
defineExpose({
  loadProductData,
  loadStatistics
})
</script>

<style scoped>
.product-list-container {
  width: 100%;
  max-width: 2000px;
  margin: 0 auto;
 
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.header-title {
  font-size: 18px;
  font-weight: 600;
}

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

.search-bar {
  margin-bottom: 20px;
}

.no-image {
  color: #909399;
  font-size: 12px;
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
</style>