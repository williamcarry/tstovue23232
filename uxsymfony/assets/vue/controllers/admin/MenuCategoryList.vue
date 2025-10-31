<template>
  <div class="menu-category-list-container">
    <!-- 页面头部 -->
    <div class="page-header">
      <h2 class="page-title">
        <el-icon class="title-icon"><Layout /></el-icon>
        一级分类管理
      </h2>
      <div class="header-actions">
        <el-button type="primary" @click="showCreateDialog" :icon="Plus">
          添加一级分类
        </el-button>
      </div>
    </div>
    
    <!-- 一级分类表格 -->
    <el-card class="table-card">
      <template #header>
        <div class="table-header">
          <div class="table-header-title">
            <el-icon class="table-icon"><List /></el-icon>
            一级分类列表
          </div>
          <div class="table-header-info">
            共 {{ totalCategories }} 条记录
          </div>
        </div>
      </template>
      
      <el-table 
        :data="menuCategories" 
        class="menu-category-table" 
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
        <el-table-column label="中文名称" min-width="150">
          <template #default="scope">
            <el-button type="text" @click="showSubcategories(scope.row)">{{ scope.row.title }}</el-button>
          </template>
        </el-table-column>
        <el-table-column prop="titleEn" label="英文名称" min-width="150" show-overflow-tooltip />
        <el-table-column prop="icon" label="图标" min-width="100">
          <template #default="scope">
            <div class="icon-display">
              <component 
                :is="getIconComponent(scope.row.icon)" 
                :size="16" 
                class="table-icon"
              />
              <span class="icon-name">{{ scope.row.icon }}</span>
            </div>
          </template>
        </el-table-column>
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
            <el-button size="small" type="danger" @click="deleteCategory(scope.row)">删除</el-button>
          </template>
        </el-table-column>
      </el-table>
    </el-card>
    
    <!-- 分页 -->
    <div class="pagination-container">
      <el-pagination
        v-model:current-page="currentPage"
        :page-size="20"
        :total="totalCategories"
        :pager-count="5"
        layout="total, prev, pager, next, jumper"
        prev-text="上一页"
        next-text="下一页"
        background
        @current-change="handlePageChange"
      />
    </div>
    
    <!-- 添加/编辑一级分类对话框 -->
    <el-dialog v-model="dialogVisible" :title="dialogTitle">
      <el-form :model="currentCategory" label-width="100px">
        <el-form-item label="中文名称" required>
          <el-input v-model="currentCategory.title" />
        </el-form-item>
        <el-form-item label="英文名称">
          <el-input v-model="currentCategory.titleEn" />
        </el-form-item>
        <el-form-item label="图标">
          <div class="icon-selector">
            <el-input 
              v-model="currentCategory.icon" 
              placeholder="请选择图标" 
              readonly
              @click="iconSelectorVisible = true"
            >
              <template #append>
                <el-button :icon="Icons[currentCategory.icon] || Icons.Home" />
              </template>
            </el-input>
          </div>
        </el-form-item>
        <el-form-item label="排序">
          <el-input-number v-model="currentCategory.sortOrder" :min="0" />
        </el-form-item>
        <el-form-item label="状态">
          <el-switch
            v-model="currentCategory.isActive"
            active-text="启用"
            inactive-text="禁用"
          />
        </el-form-item>
      </el-form>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="dialogVisible = false">取消</el-button>
          <el-button type="primary" @click="saveCategory">保存</el-button>
        </span>
      </template>
    </el-dialog>
    
    <!-- 二级分类弹窗 -->
    <el-dialog v-model="subcategoriesDialogVisible" :title="`二级分类 - ${selectedCategory.title}`" width="80%">
      <el-table :data="subcategories" style="width: 100%" v-loading="subcategoriesLoading">
        <el-table-column label="序号" width="80">
          <template #default="scope">
            {{ (subcategoriesCurrentPage - 1) * 20 + scope.$index + 1 }}
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
            <el-button size="small" @click="editSubcategory(scope.row)">编辑</el-button>
            <el-button size="small" type="danger" @click="deleteSubcategory(scope.row)">删除</el-button>
          </template>
        </el-table-column>
      </el-table>
      
      <!-- 二级分类分页 -->
      <div class="pagination-container">
        <el-pagination
          v-model:current-page="subcategoriesCurrentPage"
          :page-size="20"
          :total="subcategoriesTotal"
          :pager-count="5"
          layout="total, prev, pager, next, jumper"
          prev-text="上一页"
          next-text="下一页"
          background
          @current-change="handleSubcategoriesPageChange"
        />
      </div>
      
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="subcategoriesDialogVisible = false">关闭</el-button>
        </span>
      </template>
    </el-dialog>
    
    <!-- 编辑二级分类对话框 -->
    <el-dialog v-model="editSubcategoryDialogVisible" :title="editSubcategoryDialogTitle">
      <el-form :model="currentSubcategory" label-width="100px">
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
          <el-button @click="editSubcategoryDialogVisible = false">取消</el-button>
          <el-button type="primary" @click="saveSubcategory">保存</el-button>
        </span>
      </template>
    </el-dialog>
    
    <!-- 图标选择弹窗 -->
    <el-dialog 
      v-model="iconSelectorVisible" 
      title="选择图标" 
      width="1000px"
      class="icon-selector-dialog"
    >
      <div class="icon-grid">
        <div 
          v-for="icon in iconOptions" 
          :key="icon.value"
          class="icon-grid-item"
          :class="{ 'selected': currentCategory.icon === icon.value }"
          @click="selectIcon(icon.value)"
        >
          <component 
            :is="icon.component" 
            :size="24" 
            class="icon-grid-preview"
          />
          <span class="icon-grid-label">{{ icon.label }}</span>
        </div>
      </div>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="iconSelectorVisible = false">取消</el-button>
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
  ElMessageBox,
  ElMessage,
  ElLoading,
  ElSelect,
  ElOption,
  ElIcon
} from 'element-plus'

// 动态导入 Lucide Vue 图标
import * as Icons from 'lucide-vue-next'
import { Layout, Plus, List } from '@element-plus/icons-vue'

// 定义 props
const props = defineProps({
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
  totalCategories: {
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
  console.log('MenuCategoryList component mounted with categories:', props.menuCategories);
});

// 定义响应式数据
const loading = ref(false)
const dialogVisible = ref(false)
const iconSelectorVisible = ref(false)
const dialogTitle = ref('')
const currentCategory = reactive({
  id: null,
  title: '',
  titleEn: '',
  icon: 'Home',
  sortOrder: 0,
  isActive: true
})
const currentPage = ref(props.currentPage)

// 图标选项列表（选择100个与分类相关的常用图标，去除重复项）
const iconOptions = [
  // 一级分类相关图标
  'Home', 'ShoppingCart', 'Package', 'Users', 'BarChart', 'Settings', 'Bell', 'Mail', 
  'Phone', 'MapPin', 'Clock', 'Star', 'Heart', 'Search', 'Menu', 'X', 'Plus', 'Minus', 
  'Edit', 'Trash', 'Eye', 'EyeOff', 'Lock', 'Unlock', 'User', 'Calendar', 'File', 
  'Folder', 'Image', 'Video', 'Music', 'Book', 'Gift', 'Tag', 'CreditCard', 
  'ShoppingBag', 'Truck', 'Award', 'Shield', 'Zap', 'Globe', 'Camera', 'Printer', 
  'Download', 'Upload', 'Share', 'Bookmark', 'Flag', 'AlertCircle', 'Check', 
  'AlertTriangle', 'Info', 'HelpCircle', 'ArrowLeft', 'ArrowRight', 'ChevronLeft', 
  'ChevronRight', 'Filter', 'Grid', 'List', 'Grid3X3', 'Layout', 'Palette', 'Type', 
  'Bold', 'Italic', 'Underline', 'AlignLeft', 'AlignCenter', 'AlignRight', 'Link', 
  'Scissors', 'Copy', 'Clipboard', 'Save', 'RefreshCw', 'RotateCw', 'ZoomIn', 'ZoomOut',
  
  // 二级分类相关图标
  'Layers', 'Server', 'Database', 'HardDrive', 'Cpu', 'Monitor', 'Smartphone', 'Tablet',
  'Tv', 'Radio', 'Headphones', 'Mic', 'Speaker', 'CameraOff', 'Film', 'Gamepad',
  'Watch', 'Sun', 'Moon', 'Cloud', 'CloudRain', 'CloudSnow', 'CloudLightning', 'Wind',
  'Droplets', 'Umbrella', 'Compass', 'Map', 'Navigation', 'Anchor', 'Mountain',
  'Flower', 'Leaf', 'Sprout', 'Carrot', 'Apple', 'Coffee', 'Wine',
  'Hamburger', 'Utensils', 'ChefHat', 'Pizza', 'Sandwich', 'Salad', 'Soup', 'Fish',
  
  // 三级分类相关图标
  'ShoppingBasket', 'Shirt', 'Backpack', 'Glasses', 'Wallet', 'Key',
  'Ticket', 'Medal', 'Trophy', 'Target', 'DollarSign', 'Euro', 'Currency',
  'Bitcoin', 'Banknote', 'Percent', 'PieChart', 'TrendingUp', 'TrendingDown',
  'Activity', 'HeartPulse', 'Stethoscope', 'Pill', 'Dna', 'Microscope', 'TestTube', 'Syringe',
  'Building', 'Store', 'Factory', 'Warehouse', 'School', 'GraduationCap', 'BookOpen', 'Library',
  'Plane', 'Train', 'Bus', 'Car', 'Bike', 'Ship', 'Rocket',
  'Fuel', 'Battery', 'BatteryCharging', 'BatteryFull', 'BatteryLow', 'Power', 'Plug',
  
  // 补充的图标
  'Laptop', 'Gamepad', 'Monitor', 'Speaker', 'Tv', 'Tablet', 'Smartphone', 'Printer',
  'Globe', 'Map', 'Navigation', 'Compass', 'MapPin', 'Mail', 'Phone', 'Calendar',
  'Clock', 'User', 'Users', 'Star', 'Heart', 'Camera', 'Image', 'Video', 'Music', 'Book',
  // 新增的5个图标
  'ShoppingCart', 'Package', 'Tag', 'Gift', 'Settings'
].filter((item, index, self) => self.indexOf(item) === index) // 去除重复项
// 进一步过滤掉用户指定要删除的图标
.filter(name => !['Dress', 'Pants', 'Sock', 'Hat', 'Jacket', 'Sneakers', 'HighHeels', 'Yen', 'Scooter', 'Outlet'].includes(name))
.map(name => ({
  label: name,
  value: name,
  component: Icons[name]
}))

// 获取图标组件的方法
const getIconComponent = (iconName) => {
  return Icons[iconName] || Icons.Home // 如果找不到图标，则返回默认的 Home 图标
}

// 选择图标的方法
const selectIcon = (iconName) => {
  currentCategory.icon = iconName
  iconSelectorVisible.value = false
}

// 二级分类相关数据
const subcategoriesDialogVisible = ref(false)
const subcategoriesLoading = ref(false)
const subcategories = ref([])
const selectedCategory = ref({})
const subcategoriesCurrentPage = ref(1)
const subcategoriesTotal = ref(0)

// 编辑二级分类相关数据
const editSubcategoryDialogVisible = ref(false)
const editSubcategoryDialogTitle = ref('')
const currentSubcategory = reactive({
  id: null,
  title: '',
  titleEn: '',
  sortOrder: 0,
  isActive: true
})

// 定义方法
const showCreateDialog = () => {
  dialogTitle.value = '添加一级分类'
  Object.assign(currentCategory, { 
    id: null, 
    title: '', 
    titleEn: '', 
    icon: 'Home', 
    sortOrder: 0, 
    isActive: true 
  })
  dialogVisible.value = true
}

const showEditDialog = (category) => {
  dialogTitle.value = '编辑一级分类'
  Object.assign(currentCategory, { ...category })
  dialogVisible.value = true
}

const showSubcategories = async (category) => {
  try {
    subcategoriesLoading.value = true
    selectedCategory.value = category
    subcategoriesCurrentPage.value = 1 // 重置到第一页
    
    // 加载二级分类数据
    await loadSubcategories(category.id, 1)
    subcategoriesDialogVisible.value = true
  } catch (error) {
    ElMessage({
      type: 'error',
      message: '加载二级分类失败: ' + error.message,
    })
  } finally {
    subcategoriesLoading.value = false
  }
}

const loadSubcategories = async (categoryId, page) => {
  try {
    // 使用从prop传入的adminLoginPath
    const loginPath = props.adminLoginPath || window.adminLoginPath || '';
    const response = await fetch(`/admin${loginPath}/menu/subcategory/list/data?categoryId=${categoryId}&page=${page}`, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
      }
    })
    
    const result = await response.json()
    
    if (result.success) {
      subcategories.value = result.data.menuSubcategories
      subcategoriesCurrentPage.value = result.data.currentPage
      subcategoriesTotal.value = result.data.totalSubcategories
    } else {
      ElMessage({
        type: 'error',
        message: result.message || '加载二级分类失败',
      })
    }
  } catch (error) {
    ElMessage({
      type: 'error',
      message: '加载二级分类失败: ' + error.message,
    })
  }
}

const deleteCategory = (category) => {
  ElMessageBox.confirm(
    `确定要删除一级分类 "${category.title}" 吗？`,
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
      const response = await fetch(`/admin${loginPath}/menu/category/delete/${category.id}`, {
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
        // 重新加载数据
        window.dispatchEvent(new CustomEvent('menu-category-updated'))
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

const saveCategory = async () => {
  try {
    loading.value = true
    
    const url = currentCategory.id 
      ? `/admin${window.adminLoginPath}/menu/category/update/${currentCategory.id}`
      : `/admin${window.adminLoginPath}/menu/category/create`
      
    const method = currentCategory.id ? 'POST' : 'POST'
    
    const formData = new FormData()
    formData.append('title', currentCategory.title)
    formData.append('titleEn', currentCategory.titleEn || '')
    formData.append('icon', currentCategory.icon)
    formData.append('sortOrder', currentCategory.sortOrder)
    formData.append('isActive', currentCategory.isActive)
    
    const response = await fetch(url, {
      method: method,
      body: formData
    })
    
    const result = await response.json()
    
    if (result.success) {
      ElMessage({
        type: 'success',
        message: currentCategory.id ? '更新成功' : '添加成功',
      })
      dialogVisible.value = false
      // 重新加载数据
      window.dispatchEvent(new CustomEvent('menu-category-updated'))
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
  // 跳转到指定页面
  window.location.href = `/admin${window.adminLoginPath}/menu/category/list/tab?page=${page}`
}

// 二级分类相关方法
const handleSubcategoriesPageChange = (page) => {
  // 加载指定页面的二级分类数据
  loadSubcategories(selectedCategory.value.id, page)
}

const editSubcategory = (subcategory) => {
  editSubcategoryDialogTitle.value = '编辑二级分类'
  Object.assign(currentSubcategory, { ...subcategory })
  editSubcategoryDialogVisible.value = true
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
      subcategoriesLoading.value = true
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
        // 重新加载二级分类数据
        loadSubcategories(selectedCategory.value.id, subcategoriesCurrentPage.value)
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
      subcategoriesLoading.value = false
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
    subcategoriesLoading.value = true
    
    // 使用从prop传入的adminLoginPath
    const loginPath = props.adminLoginPath || window.adminLoginPath || '';
    const url = `/admin${loginPath}/menu/subcategory/update/${currentSubcategory.id}`
    
    const formData = new FormData()
    formData.append('categoryId', selectedCategory.value.id)
    formData.append('title', currentSubcategory.title)
    formData.append('titleEn', currentSubcategory.titleEn || '')
    formData.append('sortOrder', currentSubcategory.sortOrder)
    formData.append('isActive', currentSubcategory.isActive)
    
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
      editSubcategoryDialogVisible.value = false
      // 重新加载二级分类数据
      loadSubcategories(selectedCategory.value.id, subcategoriesCurrentPage.value)
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
    subcategoriesLoading.value = false
  }
}

</script>

<style scoped>
.menu-category-list-container {
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

.menu-category-table {
  width: 100% !important;
  font-size: 14px;
}

.menu-category-table :deep(.el-table__header th) {
  background-color: #f5f7fa;
  color: #606266;
  font-weight: bold;
}

.menu-category-table :deep(.el-table__row:hover) {
  background-color: #f5f7fa;
}

.menu-category-table :deep(.el-table__row:hover td) {
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

/* 图标选择器样式 */
.icon-option {
  display: flex;
  align-items: center;
  gap: 8px;
}

.icon-preview {
  flex-shrink: 0;
}

.icon-label {
  flex: 1;
}

/* 加宽的图标选择器下拉样式 */
.wide-icon-select {
  width: 400px !important;
}

.wide-icon-select .el-select-dropdown__item {
  height: auto;
  padding: 10px 15px;
}

.wide-icon-select .icon-option {
  display: flex;
  align-items: center;
  gap: 12px;
  font-size: 14px;
}

.wide-icon-select .icon-preview {
  width: 20px;
  height: 20px;
}

/* 表格图标显示样式 */
.icon-display {
  display: flex;
  align-items: center;
  gap: 8px;
}

.table-icon {
  flex-shrink: 0;
}

/* 图标网格样式 */
.icon-grid {
  display: grid;
  grid-template-columns: repeat(10, 1fr);
  gap: 10px;
  max-height: 400px;
  overflow-y: auto;
  padding: 10px;
}

.icon-grid-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 12px 4px;
  border: 1px solid #e4e7ed;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.3s;
  text-align: center;
  min-width: 0;
}

.icon-grid-item:hover {
  border-color: #409eff;
  background-color: #f5f7fa;
}

.icon-grid-item.selected {
  border-color: #409eff;
  background-color: #ecf5ff;
  box-shadow: 0 0 2px 1px #409eff;
}

.icon-grid-preview {
  margin-bottom: 8px;
}

.icon-grid-label {
  font-size: 12px;
  text-align: center;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  width: 100%;
  line-height: 1.2;
}

/* 图标选择器输入框样式 */
.icon-selector {
  width: 100%;
}

/* 图标选择器弹窗样式 */
.icon-selector-dialog .el-dialog__body {
  padding: 10px;
}

@media (max-width: 768px) {
  .icon-grid {
    grid-template-columns: repeat(5, 1fr);
  }
}
</style>
