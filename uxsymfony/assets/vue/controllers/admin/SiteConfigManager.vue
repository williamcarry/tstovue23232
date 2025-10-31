<template>
  <div class="site-config-manager">
    <!-- 页面头部 -->
    <div class="page-header">
      <h2 class="page-title">
        <el-icon class="title-icon"><Setting /></el-icon>
        网站参数管理
      </h2>
      <div class="header-actions">
        <el-button type="primary" @click="showCreateDialog" :icon="Plus">
          添加配置项
        </el-button>
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
            <el-form-item label="关键词">
              <el-input v-model="searchForm.keyword" placeholder="配置键名/描述" />
            </el-form-item>
          </el-col>
          <el-col :span="8" :xs="24" class="action-col">
            <el-form-item label=" ">
              <div class="form-actions">
                <el-button type="primary" @click="searchConfigs" :icon="Search">
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
    
    <!-- 配置项表格 -->
    <el-card class="table-card">
      <template #header>
        <div class="table-header">
          <div class="table-header-title">
            <el-icon class="table-icon"><List /></el-icon>
            配置项列表
          </div>
          <div class="table-header-info">
            共 {{ pagination.totalItems }} 条记录
          </div>
        </div>
      </template>
      
      <el-table 
        :data="configs" 
        class="config-table" 
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
        <el-table-column prop="configKey" label="配置键名" min-width="150" show-overflow-tooltip />
        <el-table-column prop="configValue" label="配置值" min-width="200" show-overflow-tooltip>
          <template #default="scope">
            <div class="config-value-cell">
              <!-- 特殊处理图片配置项 -->
              <div v-if="isImageConfig(scope.row.configKey)" class="image-config">
                <img v-if="getImageUrl(scope.row.configValue)" 
                     :src="getImageUrl(scope.row.configValue)" 
                     class="config-image-preview" 
                     @click="showImagePreview(getImageUrl(scope.row.configValue))"
                     @error="handleImageError(scope.row.configKey)" />
                <span v-else class="no-image">无图片</span>
              </div>
              <div v-else>
                {{ scope.row.configValue || '-' }}
              </div>
            </div>
          </template>
        </el-table-column>
        <el-table-column prop="description" label="描述" min-width="200" show-overflow-tooltip />
        <el-table-column prop="updatedAt" label="更新时间" min-width="160" />
        <el-table-column label="操作" min-width="200" fixed="right">
          <template #default="scope">
            <div class="action-buttons">
              <el-button size="small" @click="editConfig(scope.row)">编辑</el-button>
              <el-button size="small" type="danger" @click="deleteConfig(scope.row)">删除</el-button>
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
    
    <!-- 添加/编辑配置项对话框 -->
    <el-dialog v-model="dialogVisible" :title="dialogTitle" width="600px">
      <el-form :model="configForm" label-width="100px" ref="configFormRef">
        <el-form-item label="配置键名" prop="configKey" :rules="[
          { required: true, message: '请输入配置键名', trigger: 'blur' },
          { pattern: /^[a-zA-Z][a-zA-Z0-9_]*$/, message: '键名必须以字母开头，只能包含字母、数字和下划线', trigger: 'blur' }
        ]">
          <el-input v-model="configForm.configKey" :disabled="isEditMode" />
        </el-form-item>
        <el-form-item label="配置值" prop="configValue">
          <!-- 特殊处理图片配置项 -->
          <div v-if="isImageConfig(configForm.configKey)" class="image-upload-section">
            <file-upload 
              v-model="configForm.configValue" 
              :admin-login-path="adminLoginPath"
              accept="image/*"
              :max-size="5"
              endpoint-type="site-config"
              @upload-success="onImageUploadSuccess"
            />
          </div>
          <el-input v-else v-model="configForm.configValue" type="textarea" :rows="4" />
        </el-form-item>
        <el-form-item label="描述" prop="description">
          <el-input v-model="configForm.description" type="textarea" :rows="2" />
        </el-form-item>
      </el-form>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="dialogVisible = false">取消</el-button>
          <el-button type="primary" @click="saveConfig" :loading="saving">确定</el-button>
        </span>
      </template>
    </el-dialog>
    
    <!-- 图片查看器 -->
    <el-image-viewer
      v-if="imageViewerVisible"
      :url-list="imageViewerUrls"
      @close="closeImageViewer"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, defineExpose, computed } from 'vue'
import {
  ElCard,
  ElTable,
  ElTableColumn,
  ElButton,
  ElDialog,
  ElForm,
  ElFormItem,
  ElInput,
  ElMessageBox,
  ElMessage,
  ElPagination,
  ElUpload,
  ElIcon,
  ElImageViewer,
  ElRow,
  ElCol
} from 'element-plus'
import { Setting, Plus, Refresh, Search, RefreshLeft, List } from '@element-plus/icons-vue'
import FileUpload from './FileUpload.vue'

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
const configs = ref([])
const loading = ref(false)
const dialogVisible = ref(false)
const isEditMode = ref(false)
const saving = ref(false)
const configFormRef = ref(null)

const configForm = ref({
  id: null,
  configKey: '',
  configValue: '',
  description: ''
})

// 搜索表单
const searchForm = ref({
  keyword: ''
})

const pagination = ref({
  currentPage: 1,
  totalPages: 1,
  totalItems: 0,
  itemsPerPage: 20
})

// 图片URL缓存
const imageUrls = ref({})

// 图片预览
const imageViewerVisible = ref(false)
const imageViewerUrls = ref([])

// 对话框标题计算属性
const dialogTitle = computed(() => {
  return isEditMode.value ? '编辑配置项' : '添加配置项'
})

// 判断是否为图片配置项
const isImageConfig = (configKey) => {
  return configKey === 'site_logo' || configKey === 'site_favicon'
}

// 获取图片URL
const getImageUrl = (key) => {
  if (!key) return ''
  return imageUrls.value[key] || ''
}

// 处理图片加载错误
const handleImageError = (configKey) => {
  console.log('Image load error for config:', configKey)
  // 可以在这里添加错误处理逻辑
}

// 显示图片预览
const showImagePreview = (url) => {
  if (url) {
    imageViewerUrls.value = [url]
    imageViewerVisible.value = true
  }
}

// 关闭图片预览
const closeImageViewer = () => {
  imageViewerVisible.value = false
  imageViewerUrls.value = []
}

// 加载配置数据
const loadConfigData = async (page = 1, limit = 20) => {
  loading.value = true
  try {
    const params = new URLSearchParams({
      page: page,
      limit: limit,
      keyword: searchForm.value.keyword
    })
    
    const url = `/admin${props.adminLoginPath}/site-config/list/tab?${params}`
    
    const response = await fetch(url, {
      method: 'GET',
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    
    const result = await response.json()
    
    if (result.success) {
      configs.value = result.data
      pagination.value = result.pagination
      
      // 预加载图片URL
      await preloadImageUrls(result.data)
    } else {
      ElMessage.error(result.message || '加载数据失败')
    }
  } catch (error) {
    ElMessage.error('加载数据失败: ' + error.message)
  } finally {
    loading.value = false
  }
}

// 预加载图片URL
const preloadImageUrls = async (configs) => {
  const imageConfigs = configs.filter(config => isImageConfig(config.configKey) && config.configValue)
  
  for (const config of imageConfigs) {
    if (config.configValue && !config.configValue.startsWith('http')) {
      try {
        const signedUrl = await fetchSignedUrl(config.configValue)
        if (signedUrl) {
          imageUrls.value[config.configValue] = signedUrl
        }
      } catch (error) {
        console.error('Failed to preload image URL:', error)
      }
    } else if (config.configValue) {
      imageUrls.value[config.configValue] = config.configValue
    }
  }
}

// 获取签名URL
const fetchSignedUrl = async (key) => {
  if (!key || key.startsWith('http')) {
    return key
  }
  
  try {
    const loginPath = props.adminLoginPath || window.adminLoginPath || ''
    const signedUrlEndpoint = `/admin${loginPath}/site-config/file/signed-url`
    
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

// 刷新数据
const refreshData = () => {
  loadConfigData(pagination.value.currentPage, pagination.value.itemsPerPage)
}

// 搜索配置项
const searchConfigs = () => {
  pagination.value.currentPage = 1
  loadConfigData(1, pagination.value.itemsPerPage)
}

// 重置搜索
const resetSearch = () => {
  searchForm.value.keyword = ''
  pagination.value.currentPage = 1
  loadConfigData(1, pagination.value.itemsPerPage)
}

// 图片上传成功回调
const onImageUploadSuccess = (result) => {
  console.log('Image upload success:', result)
  // configForm.configValue 已经由 FileUpload 组件自动更新
}



// 显示创建对话框
const showCreateDialog = () => {
  isEditMode.value = false
  configForm.value = {
    id: null,
    configKey: '',
    configValue: '',
    description: ''
  }
  dialogVisible.value = true
}

// 编辑配置项
const editConfig = (config) => {
  isEditMode.value = true
  configForm.value = { ...config }
  dialogVisible.value = true
}

// 删除配置项
const deleteConfig = (config) => {
  ElMessageBox.confirm(
    `确定要删除配置项 "${config.configKey}" 吗？此操作不可恢复。`,
    '确认删除',
    {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning',
    }
  ).then(async () => {
    try {
      const response = await fetch(`/admin${props.adminLoginPath}/site-config/delete/${config.id}`, {
        method: 'POST',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Content-Type': 'application/json'
        }
      })
      
      const result = await response.json()
      
      if (result.success) {
        ElMessage.success(result.message || '删除成功')
        refreshData()
      } else {
        ElMessage.error(result.error || '删除失败')
      }
    } catch (error) {
      ElMessage.error('删除失败: ' + error.message)
    }
  }).catch(() => {
    // 用户取消删除
  })
}

// 保存配置项
const saveConfig = async () => {
  try {
    await configFormRef.value.validate()
  } catch (error) {
    return
  }
  
  saving.value = true
  try {
    let url, method
    const formData = {
      configKey: configForm.value.configKey,
      configValue: configForm.value.configValue,
      description: configForm.value.description
    }
    
    if (isEditMode.value) {
      // 编辑模式
      url = `/admin${props.adminLoginPath}/site-config/update/${configForm.value.id}`
      method = 'POST'
    } else {
      // 创建模式
      url = `/admin${props.adminLoginPath}/site-config/create`
      method = 'POST'
    }
    
    const response = await fetch(url, {
      method: method,
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(formData)
    })
    
    const result = await response.json()
    
    if (result.success) {
      ElMessage.success(result.message || (isEditMode.value ? '更新成功' : '创建成功'))
      dialogVisible.value = false
      refreshData()
    } else {
      ElMessage.error(result.error || (isEditMode.value ? '更新失败' : '创建失败'))
    }
  } catch (error) {
    ElMessage.error((isEditMode.value ? '更新失败' : '创建失败') + ': ' + error.message)
  } finally {
    saving.value = false
  }
}

// 处理分页大小变化
const handleSizeChange = (val) => {
  pagination.value.itemsPerPage = val
  loadConfigData(pagination.value.currentPage, val)
}

// 处理当前页变化
const handleCurrentChange = (val) => {
  pagination.value.currentPage = val
  loadConfigData(val, pagination.value.itemsPerPage)
}

// 组件挂载时加载数据
onMounted(() => {
  // 只有当 autoLoad 为 true 时才自动加载数据
  if (props.autoLoad) {
    loadConfigData()
  }
})

// 暴露方法给父组件调用
defineExpose({
  loadConfigData
})
</script>

<style scoped>
.site-config-manager {
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

.config-table {
  width: 100% !important;
  font-size: 14px;
}

.config-table :deep(.el-table__header th) {
  background-color: #f5f7fa;
  color: #606266;
  font-weight: bold;
}

.config-table :deep(.el-table__row:hover) {
  background-color: #f5f7fa;
}

.config-table :deep(.el-table__row:hover td) {
  background-color: #f5f7fa !important;
}

.config-value-cell {
  text-align: left;
  white-space: pre-wrap;
  word-break: break-all;
}

.image-config {
  display: flex;
  align-items: center;
  justify-content: center;
}

.config-image-preview {
  max-width: 100px;
  max-height: 100px;
  object-fit: contain;
  cursor: pointer;
}

.no-image {
  color: #909399;
  font-style: italic;
}

.image-upload-section {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 10px;
}

.current-image-info {
  margin-top: 10px;
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

.action-buttons {
  display: flex;
  gap: 10px;
  flex-wrap: nowrap;
  justify-content: center;
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