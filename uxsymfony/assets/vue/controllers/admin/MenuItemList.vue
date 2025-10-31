<template>
  <div class="menu-item-list-container">
    <!-- 页面头部 -->
    <div class="page-header">
      <h2 class="page-title">
        <el-icon class="title-icon"><Layout /></el-icon>
        三级分类管理
      </h2>
      <div class="header-actions">
        <el-button type="danger" @click="batchDeleteItems" :disabled="selectedItems.length === 0" :icon="Delete">
          批量删除
        </el-button>
        <el-button type="primary" @click="showCreateDialog" :icon="Plus">
          添加三级分类
        </el-button>
      </div>
    </div>
    
    <!-- 三级分类表格 -->
    <el-card class="table-card">
      <template #header>
        <div class="table-header">
          <div class="table-header-title">
            <el-icon class="table-icon"><List /></el-icon>
            三级分类列表
          </div>
          <div class="table-header-info">
            共 {{ totalItems }} 条记录
          </div>
        </div>
      </template>
      
      <el-table 
        :data="menuItems" 
        class="menu-item-table" 
        v-loading="loading" 
        @selection-change="handleSelectionChange"
        :fit="true"
        :border="true"
        :header-cell-style="{ textAlign: 'center' }"
        :cell-style="{ textAlign: 'center' }"
        :stripe="true"
      >
        <el-table-column type="selection" min-width="55" />
        <el-table-column label="序号" min-width="80">
          <template #default="scope">
            {{ (currentPage - 1) * 20 + scope.$index + 1 }}
          </template>
        </el-table-column>
        <el-table-column prop="subcategoryTitle" label="二级分类" min-width="150" show-overflow-tooltip />
        <el-table-column prop="title" label="中文名称" min-width="150" show-overflow-tooltip />
        <el-table-column prop="titleEn" label="英文名称" min-width="150" show-overflow-tooltip />
        <el-table-column prop="sortOrder" label="排序" min-width="80" />
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
            <el-button size="small" type="danger" @click="deleteItem(scope.row)">删除</el-button>
          </template>
        </el-table-column>
      </el-table>
    </el-card>
    
    <!-- 分页 -->
    <div class="pagination-container">
      <el-pagination
        v-model:current-page="currentPage"
        :page-size="20"
        :total="totalItems"
        :pager-count="5"
        layout="total, prev, pager, next, jumper"
        prev-text="上一页"
        next-text="下一页"
        background
        @current-change="handlePageChange"
      />
    </div>
    
    <!-- 添加/编辑三级分类对话框 -->
    <el-dialog v-model="dialogVisible" :title="dialogTitle">
      <el-form :model="currentItem" label-width="100px">
        <el-form-item label="二级分类" required>
          <el-select v-model="currentItem.subcategoryId" placeholder="请选择二级分类">
            <el-option
              v-for="subcategory in menuSubcategories"
              :key="subcategory.id"
              :label="subcategory.title"
              :value="subcategory.id"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="中文名称" required>
          <el-input v-model="currentItem.title" />
        </el-form-item>
        <el-form-item label="英文名称">
          <el-input v-model="currentItem.titleEn" />
        </el-form-item>
        <el-form-item label="排序">
          <el-input-number v-model="currentItem.sortOrder" :min="0" />
        </el-form-item>
        <el-form-item label="状态">
          <el-switch
            v-model="currentItem.isActive"
            active-text="启用"
            inactive-text="禁用"
          />
        </el-form-item>
      </el-form>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="dialogVisible = false">取消</el-button>
          <el-button type="primary" @click="saveItem">保存</el-button>
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
  ElInputNumber,
  ElSwitch,
  ElTag,
  ElPagination,
  ElSelect,
  ElOption,
  ElMessageBox,
  ElMessage,
  ElLoading,
  ElIcon
} from 'element-plus'

import { Layout, Plus, List, Delete } from '@element-plus/icons-vue'

// 定义 props
const props = defineProps({
  menuItems: {
    type: Array,
    default: () => []
  },
  menuSubcategories: {
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
  totalItems: {
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
  console.log('MenuItemList component mounted with items:', props.menuItems);
});

// 定义响应式数据
const loading = ref(false)
const dialogVisible = ref(false)
const dialogTitle = ref('')
const currentItem = reactive({
  id: null,
  subcategoryId: null,
  title: '',
  titleEn: '',
  sortOrder: 0,
  isActive: true
})
const currentPage = ref(props.currentPage)
const selectedItems = ref([])

// 定义方法
const showCreateDialog = () => {
  dialogTitle.value = '添加三级分类'
  Object.assign(currentItem, { 
    id: null, 
    subcategoryId: null,
    title: '', 
    titleEn: '', 
    sortOrder: 0, 
    isActive: true 
  })
  dialogVisible.value = true
}

const showEditDialog = (item) => {
  dialogTitle.value = '编辑三级分类'
  Object.assign(currentItem, { ...item })
  dialogVisible.value = true
}

const handleSelectionChange = (selection) => {
  selectedItems.value = selection
}

const batchDeleteItems = () => {
  if (selectedItems.value.length === 0) {
    ElMessage({
      type: 'warning',
      message: '请至少选择一个项目进行删除',
    })
    return
  }
  
  ElMessageBox.confirm(
    `确定要删除选中的 ${selectedItems.value.length} 个三级分类吗？`,
    '确认批量删除',
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
      
      // 收集所有要删除的ID
      const ids = selectedItems.value.map(item => item.id)
      
      // 发送批量删除请求
      const response = await fetch(`/admin${loginPath}/menu/item/batch-delete`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ ids: ids })
      })
      
      const result = await response.json()
      
      if (result.success) {
        ElMessage({
          type: 'success',
          message: `成功删除 ${result.deletedCount} 个项目`,
        })
        // 重新加载数据
        window.dispatchEvent(new CustomEvent('menu-item-updated'))
      } else {
        ElMessage({
          type: 'error',
          message: result.message || '批量删除失败',
        })
      }
    } catch (error) {
      ElMessage({
        type: 'error',
        message: '批量删除失败: ' + error.message,
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

const deleteItem = (item) => {
  ElMessageBox.confirm(
    `确定要删除三级分类 "${item.title}" 吗？`,
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
      const response = await fetch(`/admin${loginPath}/menu/item/delete/${item.id}`, {
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
        window.dispatchEvent(new CustomEvent('menu-item-updated'))
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

const saveItem = async () => {
  try {
    loading.value = true
    
    // 验证必填字段
    if (!currentItem.subcategoryId) {
      ElMessage({
        type: 'error',
        message: '请选择二级分类',
      })
      loading.value = false
      return
    }
    
    if (!currentItem.title) {
      ElMessage({
        type: 'error',
        message: '请输入中文名称',
      })
      loading.value = false
      return
    }
    
    // 使用从prop传入的adminLoginPath
    const loginPath = props.adminLoginPath || window.adminLoginPath || '';
    const url = currentItem.id 
      ? `/admin${loginPath}/menu/item/update/${currentItem.id}`
      : `/admin${loginPath}/menu/item/create`
      
    const method = currentItem.id ? 'POST' : 'POST'
    
    const formData = new FormData()
    formData.append('subcategoryId', currentItem.subcategoryId)
    formData.append('title', currentItem.title)
    formData.append('titleEn', currentItem.titleEn || '')
    formData.append('sortOrder', currentItem.sortOrder)
    formData.append('isActive', currentItem.isActive)
    
    const response = await fetch(url, {
      method: method,
      body: formData
    })
    
    const result = await response.json()
    
    if (result.success) {
      ElMessage({
        type: 'success',
        message: currentItem.id ? '更新成功' : '添加成功',
      })
      dialogVisible.value = false
      // 重新加载数据而不是刷新页面
      window.dispatchEvent(new CustomEvent('menu-item-updated'))
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
  window.dispatchEvent(new CustomEvent('menu-item-page-changed', { detail: { page: page, loginPath: loginPath } }))
}
</script>

<style scoped>
.menu-item-list-container {
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

.menu-item-table {
  width: 100% !important;
  font-size: 14px;
}

.menu-item-table :deep(.el-table__header th) {
  background-color: #f5f7fa;
  color: #606266;
  font-weight: bold;
}

.menu-item-table :deep(.el-table__row:hover) {
  background-color: #f5f7fa;
}

.menu-item-table :deep(.el-table__row:hover td) {
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