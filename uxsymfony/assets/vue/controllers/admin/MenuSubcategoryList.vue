<template>
  <div class="menu-subcategory-list-container">
    <!-- 页面头部 -->
    <div class="page-header">
      <h2 class="page-title">
        <el-icon class="title-icon"><Layout /></el-icon>
        二级分类管理
      </h2>
      <div class="header-actions">
        <el-button type="primary" @click="showCreateDialog" :icon="Plus">
          添加二级分类
        </el-button>
      </div>
    </div>
    
    <!-- 二级分类表格 -->
    <el-card class="table-card">
      <template #header>
        <div class="table-header">
          <div class="table-header-title">
            <el-icon class="table-icon"><List /></el-icon>
            二级分类列表
          </div>
          <div class="table-header-info">
            共 {{ totalSubcategories }} 条记录
          </div>
        </div>
      </template>
      
      <el-table 
        :data="menuSubcategories" 
        class="menu-subcategory-table" 
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
        <el-table-column label="中文名称" min-width="150">
          <template #default="scope">
            <el-button type="text" @click="showMenuItems(scope.row)">{{ scope.row.title }}</el-button>
          </template>
        </el-table-column>
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
            <el-button size="small" type="danger" @click="deleteSubcategory(scope.row)">删除</el-button>
          </template>
        </el-table-column>
      </el-table>
    </el-card>
    
    <!-- 分页 -->
    <div class="pagination-container">
      <el-pagination
        v-model:current-page="currentPage"
        :page-size="20"
        :total="totalSubcategories"
        :pager-count="5"
        layout="total, prev, pager, next, jumper"
        prev-text="上一页"
        next-text="下一页"
        background
        @current-change="handlePageChange"
      />
    </div>
    
    <!-- 添加/编辑二级分类对话框 -->
    <el-dialog v-model="dialogVisible" :title="dialogTitle">
      <el-form :model="currentSubcategory" label-width="100px">
        <el-form-item label="一级分类" required>
          <el-select v-model="currentSubcategory.categoryId" placeholder="请选择一级分类">
            <el-option
              v-for="category in menuCategories"
              :key="category.id"
              :label="category.title"
              :value="category.id"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="中文名称" required>
          <el-input v-model="currentSubcategory.title" />
        </el-form-item>
        <el-form-item label="英文名称">
          <el-input v-model="currentSubcategory.titleEn" />
        </el-form-item>
        <el-form-item label="排序">
          <el-input-number v-model="currentSubcategory.sortOrder" :min="0" />
        </el-form-item>
        <el-form-item label="状态">
          <el-switch
            v-model="currentSubcategory.isActive"
            active-text="启用"
            inactive-text="禁用"
          />
        </el-form-item>
      </el-form>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="dialogVisible = false">取消</el-button>
          <el-button type="primary" @click="saveSubcategory">保存</el-button>
        </span>
      </template>
    </el-dialog>
    
    <!-- 三级分类弹窗 -->
    <el-dialog v-model="menuItemsDialogVisible" :title="`三级分类 - ${selectedSubcategory.title}`" width="80%">
      <el-table :data="menuItems" style="width: 100%" v-loading="menuItemsLoading">
        <el-table-column label="序号" width="80">
          <template #default="scope">
            {{ (menuItemsCurrentPage - 1) * 20 + scope.$index + 1 }}
          </template>
        </el-table-column>
        <el-table-column prop="title" label="中文名称" width="150" />
        <el-table-column prop="titleEn" label="英文名称" width="150" />
        <el-table-column prop="sortOrder" label="排序" width="80" />
        <el-table-column label="状态" width="100">
          <template #default="scope">
            <el-tag :type="scope.row.isActive ? 'success' : 'danger'">
              {{ scope.row.isActive ? '启用' : '禁用' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="createdAt" label="创建时间" width="180" />
        <el-table-column prop="updatedAt" label="更新时间" width="180" />
        <el-table-column label="操作" width="150">
          <template #default="scope">
            <el-button size="small" @click="editMenuItem(scope.row)">编辑</el-button>
            <el-button size="small" type="danger" @click="deleteMenuItem(scope.row)">删除</el-button>
          </template>
        </el-table-column>
      </el-table>
      
      <!-- 三级分类分页 -->
      <div class="pagination-container">
        <el-pagination
          v-model:current-page="menuItemsCurrentPage"
          :page-size="20"
          :total="menuItemsTotal"
          :pager-count="5"
          layout="total, prev, pager, next, jumper"
          prev-text="上一页"
          next-text="下一页"
          background
          @current-change="handleMenuItemsPageChange"
        />
      </div>
      
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="menuItemsDialogVisible = false">关闭</el-button>
        </span>
      </template>
    </el-dialog>
    
    <!-- 编辑三级分类对话框 -->
    <el-dialog v-model="editMenuItemDialogVisible" :title="editMenuItemDialogTitle">
      <el-form :model="currentMenuItem" label-width="100px">
        <el-form-item label="中文名称" required>
          <el-input v-model="currentMenuItem.title" />
        </el-form-item>
        <el-form-item label="英文名称">
          <el-input v-model="currentMenuItem.titleEn" />
        </el-form-item>
        <el-form-item label="排序">
          <el-input-number v-model="currentMenuItem.sortOrder" :min="0" />
        </el-form-item>
        <el-form-item label="状态">
          <el-switch
            v-model="currentMenuItem.isActive"
            active-text="启用"
            inactive-text="禁用"
          />
        </el-form-item>
      </el-form>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="editMenuItemDialogVisible = false">取消</el-button>
          <el-button type="primary" @click="saveMenuItem">保存</el-button>
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

import { Layout, Plus, List } from '@element-plus/icons-vue'

// 定义 props
const props = defineProps({
  menuSubcategories: {
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
  totalSubcategories: {
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
  console.log('MenuSubcategoryList component mounted with subcategories:', props.menuSubcategories);
});

// 定义响应式数据
const loading = ref(false)
const dialogVisible = ref(false)
const dialogTitle = ref('')
const currentSubcategory = reactive({
  id: null,
  categoryId: null,
  title: '',
  titleEn: '',
  sortOrder: 0,
  isActive: true
})
const currentPage = ref(props.currentPage)

// 三级分类相关数据
const menuItemsDialogVisible = ref(false)
const menuItemsLoading = ref(false)
const menuItems = ref([])
const selectedSubcategory = ref({})
const menuItemsCurrentPage = ref(1)
const menuItemsTotal = ref(0)

// 编辑三级分类相关数据
const editMenuItemDialogVisible = ref(false)
const editMenuItemDialogTitle = ref('')
const currentMenuItem = reactive({
  id: null,
  title: '',
  titleEn: '',
  sortOrder: 0,
  isActive: true
})

// 定义方法
const showCreateDialog = () => {
  dialogTitle.value = '添加二级分类'
  Object.assign(currentSubcategory, { 
    id: null, 
    categoryId: null,
    title: '', 
    titleEn: '', 
    sortOrder: 0, 
    isActive: true 
  })
  dialogVisible.value = true
}

const showEditDialog = (subcategory) => {
  dialogTitle.value = '编辑二级分类'
  Object.assign(currentSubcategory, { ...subcategory })
  dialogVisible.value = true
}

const showMenuItems = async (subcategory) => {
  try {
    menuItemsLoading.value = true
    selectedSubcategory.value = subcategory
    menuItemsCurrentPage.value = 1 // 重置到第一页
    
    // 加载三级分类数据
    await loadMenuItems(subcategory.id, 1)
    menuItemsDialogVisible.value = true
  } catch (error) {
    ElMessage({
      type: 'error',
      message: '加载三级分类失败: ' + error.message,
    })
  } finally {
    menuItemsLoading.value = false
  }
}

const loadMenuItems = async (subcategoryId, page) => {
  try {
    // 使用从prop传入的adminLoginPath
    const loginPath = props.adminLoginPath || window.adminLoginPath || '';
    const response = await fetch(`/admin${loginPath}/menu/item/list/data?subcategoryId=${subcategoryId}&page=${page}`, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
      }
    })
    
    const result = await response.json()
    
    if (result.success) {
      menuItems.value = result.data.menuItems
      menuItemsCurrentPage.value = result.data.currentPage
      menuItemsTotal.value = result.data.totalItems
    } else {
      ElMessage({
        type: 'error',
        message: result.message || '加载三级分类失败',
      })
    }
  } catch (error) {
    ElMessage({
      type: 'error',
      message: '加载三级分类失败: ' + error.message,
    })
  }
}

const deleteSubcategory = (subcategory) => {
  ElMessageBox.confirm(
    `确定要删除二级分类 "${subcategory.title}" 吗？`,
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
      const response = await fetch(`/admin${loginPath}/menu/subcategory/delete/${subcategory.id}`, {
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
        window.dispatchEvent(new CustomEvent('menu-subcategory-updated'))
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

const saveSubcategory = async () => {
  try {
    loading.value = true
    
    // 验证必填字段
    if (!currentSubcategory.categoryId) {
      ElMessage({
        type: 'error',
        message: '请选择一级分类',
      })
      loading.value = false
      return
    }
    
    if (!currentSubcategory.title) {
      ElMessage({
        type: 'error',
        message: '请输入中文名称',
      })
      loading.value = false
      return
    }
    
    // 使用从prop传入的adminLoginPath
    const loginPath = props.adminLoginPath || window.adminLoginPath || '';
    const url = currentSubcategory.id 
      ? `/admin${loginPath}/menu/subcategory/update/${currentSubcategory.id}`
      : `/admin${loginPath}/menu/subcategory/create`
      
    const method = currentSubcategory.id ? 'POST' : 'POST'
    
    const formData = new FormData()
    formData.append('categoryId', currentSubcategory.categoryId)
    formData.append('title', currentSubcategory.title)
    formData.append('titleEn', currentSubcategory.titleEn || '')
    formData.append('sortOrder', currentSubcategory.sortOrder)
    formData.append('isActive', currentSubcategory.isActive)
    
    const response = await fetch(url, {
      method: method,
      body: formData
    })
    
    const result = await response.json()
    
    if (result.success) {
      ElMessage({
        type: 'success',
        message: currentSubcategory.id ? '更新成功' : '添加成功',
      })
      dialogVisible.value = false
      // 重新加载数据而不是刷新页面
      window.dispatchEvent(new CustomEvent('menu-subcategory-updated'))
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
  window.dispatchEvent(new CustomEvent('menu-subcategory-page-changed', { detail: { page: page, loginPath: loginPath } }))
}

// 三级分类相关方法
const handleMenuItemsPageChange = (page) => {
  // 加载指定页面的三级分类数据
  loadMenuItems(selectedSubcategory.value.id, page)
}

const editMenuItem = (menuItem) => {
  editMenuItemDialogTitle.value = '编辑三级分类'
  Object.assign(currentMenuItem, { ...menuItem })
  editMenuItemDialogVisible.value = true
}

const deleteMenuItem = (menuItem) => {
  ElMessageBox.confirm(
    `确定要删除三级分类 "${menuItem.title}" 吗？`,
    '确认删除',
    {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning',
    }
  ).then(async () => {
    try {
      menuItemsLoading.value = true
      // 使用从prop传入的adminLoginPath
      const loginPath = props.adminLoginPath || window.adminLoginPath || '';
      const response = await fetch(`/admin${loginPath}/menu/item/delete/${menuItem.id}`, {
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
        // 重新加载三级分类数据
        loadMenuItems(selectedSubcategory.value.id, menuItemsCurrentPage.value)
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
      menuItemsLoading.value = false
    }
  }).catch(() => {
    ElMessage({
      type: 'info',
      message: '已取消删除',
    })
  })
}

const saveMenuItem = async () => {
  try {
    menuItemsLoading.value = true
    
    // 使用从prop传入的adminLoginPath
    const loginPath = props.adminLoginPath || window.adminLoginPath || '';
    const url = `/admin${loginPath}/menu/item/update/${currentMenuItem.id}`
    
    const formData = new FormData()
    formData.append('subcategoryId', selectedSubcategory.value.id)
    formData.append('title', currentMenuItem.title)
    formData.append('titleEn', currentMenuItem.titleEn || '')
    formData.append('sortOrder', currentMenuItem.sortOrder)
    formData.append('isActive', currentMenuItem.isActive)
    
    const response = await fetch(url, {
      method: 'POST',
      body: formData
    })
    
    const result = await response.json()
    
    if (result.success) {
      ElMessage({
        type: 'success',
        message: '更新成功',
      })
      editMenuItemDialogVisible.value = false
      // 重新加载三级分类数据
      loadMenuItems(selectedSubcategory.value.id, menuItemsCurrentPage.value)
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
    menuItemsLoading.value = false
  }
}
</script>

<style scoped>
.menu-subcategory-list-container {
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

.menu-subcategory-table {
  width: 100% !important;
  font-size: 14px;
}

.menu-subcategory-table :deep(.el-table__header th) {
  background-color: #f5f7fa;
  color: #606266;
  font-weight: bold;
}

.menu-subcategory-table :deep(.el-table__row:hover) {
  background-color: #f5f7fa;
}

.menu-subcategory-table :deep(.el-table__row:hover td) {
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
