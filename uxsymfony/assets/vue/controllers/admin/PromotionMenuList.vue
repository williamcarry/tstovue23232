<template>
  <div class="promotion-menu-list-container">
    <!-- 页面头部 -->
    <div class="page-header">
      <h2 class="page-title">
        <el-icon class="title-icon"><Image /></el-icon>
        促销菜单管理
      </h2>
      <div class="header-actions">
        <el-button type="primary" @click="showCreateDialog" :icon="Plus">
          添加促销菜单
        </el-button>
      </div>
    </div>
    
    <!-- 促销菜单表格 -->
    <el-card class="table-card">
      <template #header>
        <div class="table-header">
          <div class="table-header-title">
            <el-icon class="table-icon"><List /></el-icon>
            促销菜单列表
          </div>
          <div class="table-header-info">
            共 {{ totalMenus }} 条记录
          </div>
        </div>
      </template>
      
      <el-table 
        :data="promotionMenus" 
        class="promotion-menu-table" 
        v-loading="loading"
        :fit="true"
        :border="true"
        :header-cell-style="{ textAlign: 'center' }"
        :cell-style="{ textAlign: 'center' }"
        :stripe="true"
      >
        <el-table-column label="序号" min-width="80">
          <template #default="scope">
            {{ (currentPage - 1) * 20 + scope.$index + 1 }}
          </template>
        </el-table-column>
        <el-table-column prop="categoryTitle" label="一级分类" min-width="150" show-overflow-tooltip />
        <el-table-column prop="title" label="中文名称" min-width="150" show-overflow-tooltip />
        <el-table-column prop="titleEn" label="英文名称" min-width="150" show-overflow-tooltip />
        <el-table-column label="图片" min-width="150">
          <template #default="scope">
            <el-image
              v-if="scope.row.imageUrl"
              :src="scope.row.imageUrl"
              fit="cover"
              style="width: 100px; height: 60px"
              preview-teleported
              :preview-src-list="[scope.row.imageUrl]"
            />
            <span v-else>无图片</span>
          </template>
        </el-table-column>
        <el-table-column label="状态" min-width="100">
          <template #default="scope">
            <el-tag :type="scope.row.isActive ? 'success' : 'danger'">
              {{ scope.row.isActive ? '启用' : '禁用' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="createdAt" label="创建时间" min-width="180" />
        <el-table-column prop="updatedAt" label="更新时间" min-width="180" />
        <el-table-column label="操作" min-width="150" fixed="right">
          <template #default="scope">
            <el-button size="small" @click="showEditDialog(scope.row)">编辑</el-button>
            <el-button size="small" type="danger" @click="deletePromotion(scope.row)">删除</el-button>
          </template>
        </el-table-column>
      </el-table>
    </el-card>
    
    <!-- 分页 -->
    <div class="pagination-container">
      <el-pagination
        v-model:current-page="currentPage"
        :page-size="20"
        :total="totalMenus"
        :pager-count="5"
        layout="total, prev, pager, next, jumper"
        prev-text="上一页"
        next-text="下一页"
        background
        @current-change="handlePageChange"
      />
    </div>
    
    <!-- 添加/编辑促销菜单对话框 -->
    <el-dialog v-model="dialogVisible" :title="dialogTitle">
      <el-form :model="currentPromotion" label-width="100px">
        <el-form-item label="一级分类" required>
          <el-select v-model="currentPromotion.categoryId" placeholder="请选择一级分类">
            <el-option
              v-for="category in menuCategories"
              :key="category.id"
              :label="category.title"
              :value="category.id"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="中文名称" required>
          <el-input v-model="currentPromotion.title" />
        </el-form-item>
        <el-form-item label="英文名称">
          <el-input v-model="currentPromotion.titleEn" />
        </el-form-item>
        <el-form-item label="图片">
          <promotion-menu-upload 
            v-model="currentPromotion.imageUrl"
            :admin-login-path="adminLoginPath"
            @upload-success="handleUploadSuccess"
            @upload-error="handleUploadError"
          />
        </el-form-item>
        <el-form-item label="状态">
          <el-switch
            v-model="currentPromotion.isActive"
            active-text="启用"
            inactive-text="禁用"
          />
        </el-form-item>
      </el-form>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="dialogVisible = false">取消</el-button>
          <el-button type="primary" @click="savePromotion">保存</el-button>
        </span>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
// 直接导入所需的 Element Plus 组件
import {
  ElCard,
  ElTable,
  ElTableColumn,
  ElButton,
  ElDialog,
  ElForm,
  ElFormItem,
  ElInput,
  ElSwitch,
  ElTag,
  ElPagination,
  ElSelect,
  ElOption,
  ElImage,
  ElMessageBox,
  ElMessage,
  ElLoading,
  ElIcon
} from 'element-plus'
// 导入图片上传组件
import PromotionMenuUpload from './PromotionMenuUpload.vue'

import { Image, Plus, List } from '@element-plus/icons-vue'

// 定义 props
const props = defineProps({
  promotionMenus: {
    type: Array,
    default: () => []
  },
  menuCategories: {
    type: Array,
    default: () => []
  },
  currentPage: {
    type: Number,
    default: 1
  },
  totalPages: {
    type: Number,
    default: 1
  },
  totalMenus: {
    type: Number,
    default: 0
  },
  adminLoginPath: {
    type: String,
    default: ''
  }
});

// 添加调试信息
onMounted(() => {
  console.log('PromotionMenuList component mounted with menus:', props.promotionMenus);
});

// 定义响应式数据
const loading = ref(false)
const dialogVisible = ref(false)
const dialogTitle = ref('')
const currentPromotion = reactive({
  id: null,
  categoryId: null,
  title: '',
  titleEn: '',
  imageUrl: '',
  isActive: true
})
const currentPage = ref(props.currentPage)

// 定义方法
const showCreateDialog = () => {
  dialogTitle.value = '添加促销菜单'
  Object.assign(currentPromotion, { 
    id: null, 
    categoryId: null,
    title: '', 
    titleEn: '', 
    imageUrl: '',
    isActive: true 
  })
  dialogVisible.value = true
}

const showEditDialog = (promotion) => {
  dialogTitle.value = '编辑促销菜单'
  // 使用imageKey而不是imageUrl，因为imageUrl是带签名的临时URL
  Object.assign(currentPromotion, { 
    id: promotion.id,
    categoryId: promotion.categoryId,
    title: promotion.title,
    titleEn: promotion.titleEn,
    imageUrl: promotion.imageKey || promotion.imageUrl,  // 使用imageKey，如果不存在则使用imageUrl
    isActive: promotion.isActive
  })
  dialogVisible.value = true
}

const deletePromotion = (promotion) => {
  ElMessageBox.confirm(
    `确定要删除促销菜单 "${promotion.title}" 吗？`,
    '确认删除',
    {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning',
    }
  ).then(async () => {
    try {
      loading.value = true
      // 使用从prop传入的adminLoginPath
      const loginPath = props.adminLoginPath || window.adminLoginPath || '';
      const response = await fetch(`/admin${loginPath}/menu/promotion/delete/${promotion.id}`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        }
      })
      
      const result = await response.json()
      
      if (result.success) {
        ElMessage({
          type: 'success',
          message: '删除成功',
        })
        // 重新加载数据而不是刷新页面
        window.dispatchEvent(new CustomEvent('menu-promotion-updated'))
      } else {
        ElMessage({
          type: 'error',
          message: result.message || '删除失败',
        })
      }
    } catch (error) {
      ElMessage({
        type: 'error',
        message: '删除失败: ' + error.message,
      })
    } finally {
      loading.value = false
    }
  }).catch(() => {
    ElMessage({
      type: 'info',
      message: '已取消删除',
    })
  })
}

const savePromotion = async () => {
  try {
    loading.value = true
    
    // 验证必填字段
    if (!currentPromotion.categoryId) {
      ElMessage({
        type: 'error',
        message: '请选择一级分类',
      })
      loading.value = false
      return
    }
    
    if (!currentPromotion.title) {
      ElMessage({
        type: 'error',
        message: '请输入中文名称',
      })
      loading.value = false
      return
    }
    
    // 使用从prop传入的adminLoginPath
    const loginPath = props.adminLoginPath || window.adminLoginPath || '';
    const url = currentPromotion.id 
      ? `/admin${loginPath}/menu/promotion/update/${currentPromotion.id}`
      : `/admin${loginPath}/menu/promotion/create`
      
    const method = currentPromotion.id ? 'POST' : 'POST'
    
    const formData = new FormData()
    formData.append('categoryId', currentPromotion.categoryId)
    formData.append('title', currentPromotion.title)
    formData.append('titleEn', currentPromotion.titleEn || '')
    formData.append('imageUrl', currentPromotion.imageUrl || '')
    formData.append('isActive', currentPromotion.isActive)
    
    const response = await fetch(url, {
      method: method,
      body: formData
    })
    
    const result = await response.json()
    
    if (result.success) {
      ElMessage({
        type: 'success',
        message: currentPromotion.id ? '更新成功' : '添加成功',
      })
      dialogVisible.value = false
      // 重新加载数据而不是刷新页面
      window.dispatchEvent(new CustomEvent('menu-promotion-updated'))
    } else {
      ElMessage({
        type: 'error',
        message: result.errors ? result.errors.join(', ') : result.message,
      })
    }
  } catch (error) {
    ElMessage({
      type: 'error',
      message: '保存失败: ' + error.message,
    })
  } finally {
    loading.value = false
  }
}

const handlePageChange = (page) => {
  // 使用从prop传入的adminLoginPath
  const loginPath = props.adminLoginPath || window.adminLoginPath || '';
  // 触发页面更改事件而不是直接跳转
  window.dispatchEvent(new CustomEvent('menu-promotion-page-changed', { detail: { page: page, loginPath: loginPath } }))
}

// 图片上传成功处理
const handleUploadSuccess = (result) => {
  console.log('图片上传成功:', result)
}

// 图片上传失败处理
const handleUploadError = (error) => {
  console.error('图片上传失败:', error)
  ElMessage({
    type: 'error',
    message: '图片上传失败: ' + (error.message || '未知错误'),
  })
}
</script>

<style scoped>
.promotion-menu-list-container {
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

.promotion-menu-table {
  width: 100% !important;
  font-size: 14px;
}

.promotion-menu-table :deep(.el-table__header th) {
  background-color: #f5f7fa;
  color: #606266;
  font-weight: bold;
}

.promotion-menu-table :deep(.el-table__row:hover) {
  background-color: #f5f7fa;
}

.promotion-menu-table :deep(.el-table__row:hover td) {
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
</style>