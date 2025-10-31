<template>
  <div class="sub-account-container">
    <!-- 页面头部 -->
    <div class="page-header">
      <h2 class="page-title">
        <el-icon class="title-icon"><User /></el-icon>
        子账号管理
      </h2>
      <div class="header-actions">
        <el-button type="primary" @click="addSubAccount" :icon="Plus">
          添加子账号
        </el-button>
      </div>
    </div>
    
    <!-- 子账号列表卡片 -->
    <el-card class="sub-account-card">
      <template #header>
        <div class="card-header">
          <div class="card-header-title">
            <el-icon class="card-icon"><List /></el-icon>
            子账号列表
          </div>
          <div class="card-header-info">
            共 {{ pagination.totalItems }} 个子账号
          </div>
        </div>
      </template>
      
      <el-table 
        :data="subAccounts" 
        class="sub-account-table" 
        v-loading="loading"
        :fit="true"
        :border="false"
        :header-cell-style="{ textAlign: 'center' }"
        :cell-style="{ textAlign: 'center' }"
        :stripe="true"
        empty-text="暂无子账号数据"
      >
        <el-table-column label="序号" width="60" fixed>
          <template #default="scope">
            {{ (pagination.currentPage - 1) * pagination.itemsPerPage + scope.$index + 1 }}
          </template>
        </el-table-column>
        <el-table-column prop="username" label="用户名" min-width="120" show-overflow-tooltip>
          <template #default="scope">
            <div class="username-cell">
              <el-icon class="user-icon"><User /></el-icon>
              <span class="username-text">{{ scope.row.username }}</span>
            </div>
          </template>
        </el-table-column>
        <el-table-column prop="mark" label="标记" min-width="120" show-overflow-tooltip />
        <el-table-column prop="email" label="邮箱" min-width="180" show-overflow-tooltip />
        <el-table-column label="系统角色" min-width="200">
          <template #default="scope">
            <div class="roles-container">
              <template v-for="role in getRoleNames(scope.row.roles)" :key="role">
                <el-tag 
                  v-if="role !== '无特殊角色'"
                  class="role-tag"
                  type="primary"
                  effect="dark"
                >
                  {{ role }}
                </el-tag>
              </template>
              <span v-if="!scope.row.roles || getRoleNames(scope.row.roles).length === 0" class="no-roles">无角色</span>
            </div>
          </template>
        </el-table-column>
        <el-table-column label="状态" min-width="100">
          <template #default="scope">
            <el-tag 
              class="status-tag"
              :type="scope.row.isActive ? 'success' : 'danger'"
              effect="dark"
            >
              {{ scope.row.isActive ? '启用' : '禁用' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="createdAt" label="创建时间" min-width="160" sortable>
          <template #default="scope">
            <div class="time-cell">
              <div class="date">{{ formatDate(scope.row.createdAt) }}</div>
              <div class="time">{{ formatTime(scope.row.createdAt) }}</div>
            </div>
          </template>
        </el-table-column>
        <el-table-column prop="lastLoginAt" label="最后登录" min-width="160" sortable>
          <template #default="scope">
            <div class="time-cell">
              <div class="date">{{ scope.row.lastLoginAt ? formatDate(scope.row.lastLoginAt) : '-' }}</div>
              <div class="time">{{ scope.row.lastLoginAt ? formatTime(scope.row.lastLoginAt) : '-' }}</div>
            </div>
          </template>
        </el-table-column>
        <el-table-column label="操作" min-width="180" fixed="right">
          <template #default="scope">
            <div class="action-buttons">
              <el-button size="small" @click="editSubAccount(scope.row)" :icon="Edit">
                编辑
              </el-button>
              <el-button 
                size="small" 
                :type="scope.row.isActive ? 'warning' : 'success'" 
                @click="toggleSubAccountStatus(scope.row)"
              >
                {{ scope.row.isActive ? '禁用' : '启用' }}
              </el-button>
              <el-button size="small" type="danger" @click="deleteSubAccount(scope.row)" :icon="Delete">
                删除
              </el-button>
            </div>
          </template>
        </el-table-column>
      </el-table>
      
      <!-- 分页控件 -->
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
    
    <!-- 添加/编辑子账号对话框 -->
    <el-dialog 
      v-model="dialogVisible" 
      :title="dialogTitle" 
      width="600px"
      class="sub-account-dialog"
    >
      <div class="dialog-hint" v-if="!currentSubAccount.id">
        <el-alert
          title="提示：每个供应商最多可添加10个子账号"
          type="info"
          :closable="false"
          show-icon
        />
      </div>
      <el-form 
        :model="currentSubAccount" 
        label-width="100px" 
        class="sub-account-form"
        label-position="left"
      >
        <el-row :gutter="20">
          <el-col :span="24">
            <el-form-item label="用户名">
              <el-input 
                v-model="currentSubAccount.username" 
                :disabled="!!currentSubAccount.id" 
                placeholder="请输入用户名"
              />
            </el-form-item>
          </el-col>
          <el-col :span="24">
            <el-form-item label="邮箱">
              <el-input 
                v-model="currentSubAccount.email" 
                placeholder="请输入邮箱地址"
              />
            </el-form-item>
          </el-col>
          <el-col :span="24">
            <el-form-item label="标记">
              <el-input 
                v-model="currentSubAccount.mark" 
                placeholder="请输入标记/备注"
              />
            </el-form-item>
          </el-col>
          <el-col :span="24">
            <el-form-item label="角色设置">
              <el-select 
                v-model="currentSubAccount.roles" 
                multiple 
                placeholder="请选择角色"
                style="width: 100%"
              >
                <el-option
                  v-for="role in filteredRoles"
                  :key="role.id"
                  :label="role.description || role.name"
                  :value="role.id"
                />
              </el-select>
              <div class="roles-info" v-if="currentSubAccount.existingRoles && currentSubAccount.existingRoles.length > 0">
                <div class="roles-label">已设置角色：</div>
                <div class="roles-tags">
                  <el-tag 
                    v-for="role in getExistingRoleNames(currentSubAccount.existingRoles)" 
                    :key="role" 
                    class="role-tag"
                    type="info"
                    effect="dark"
                  >
                    {{ role }}
                  </el-tag>
                </div>
              </div>
            </el-form-item>
          </el-col>
          <el-col :span="24">
            <el-form-item label="密码" :required="!currentSubAccount.id">
              <el-input 
                v-model="currentSubAccount.password" 
                type="password"
                :placeholder="currentSubAccount.id ? '留空则不修改密码' : '请输入密码'" 
              />
              <div class="password-hint">密码必须至少包含8个字符</div>
            </el-form-item>
          </el-col>
          <el-col :span="24" v-if="!currentSubAccount.id || currentSubAccount.password">
            <el-form-item label="确认密码" :required="!currentSubAccount.id">
              <el-input 
                v-model="currentSubAccount.confirmPassword" 
                type="password" 
                placeholder="请再次输入密码" 
              />
            </el-form-item>
          </el-col>
          <el-col :span="24">
            <el-form-item label="状态">
              <el-switch 
                v-model="currentSubAccount.isActive" 
                active-text="启用" 
                inactive-text="禁用"
              />
            </el-form-item>
          </el-col>
        </el-row>
      </el-form>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="dialogVisible = false">取消</el-button>
          <el-button type="primary" @click="saveSubAccount">保存</el-button>
        </span>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, defineExpose, defineProps, computed } from 'vue'
import {
  ElCard,
  ElTable,
  ElTableColumn,
  ElButton,
  ElDialog,
  ElForm,
  ElFormItem,
  ElInput,
  ElSelect,
  ElOption,
  ElSwitch,
  ElTag,
  ElPagination,
  ElMessageBox,
  ElMessage,
  ElRow,
  ElCol,
  ElIcon,
  ElAlert
} from 'element-plus'
import {
  User,
  Plus,
  List,
  Edit,
  Delete
} from '@element-plus/icons-vue'

// 定义 props
const props = defineProps({
  autoLoad: {
    type: Boolean,
    default: false
  },
  supplierLoginPath: {
    type: String,
    default: ''
  }
})

// 定义响应式数据
const loading = ref(false)
const subAccounts = ref([])
const dialogVisible = ref(false)
const dialogTitle = ref('')
const availableRoles = ref([])

// 分页数据
const pagination = ref({
  currentPage: 1,
  totalPages: 1,
  totalItems: 0,
  itemsPerPage: 20
})

const currentSubAccount = reactive({
  id: null,
  username: '',
  mark: '',
  email: '',
  roles: [],
  existingRoles: [], // 存储已有的角色
  password: '',
  confirmPassword: '',
  isActive: true
})

// 计算属性：过滤掉 ROLE_SUPPLIER 和 ROLE_SUPPLIER_SUB_ACCOUNT 角色
const filteredRoles = computed(() => {
  return availableRoles.value.filter(role => 
    role.name !== 'ROLE_SUPPLIER' && 
    role.name !== 'ROLE_SUPPLIER_SUB_ACCOUNT'
  )
})

// 加载子账号数据
const loadSubAccountData = async (page = 1, limit = 20) => {
  loading.value = true
  try {
    const params = new URLSearchParams({
      page: page,
      limit: limit
    })
    
    const loginPath = props.supplierLoginPath || window.supplierLoginPath || ''
    const url = `/supplier${loginPath}/sub-account/list/data?${params}`
    
    const response = await fetch(url, {
      method: 'GET',
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    
    const result = await response.json()
    
    if (result.success) {
      subAccounts.value = result.data.subAccounts || []
      pagination.value = result.data.pagination || pagination.value
      availableRoles.value = result.data.roles || []
    } else {
      ElMessage.error(result.message || '加载数据失败')
    }
  } catch (error) {
    console.error('加载子账号数据失败:', error)
    ElMessage.error('加载数据时发生错误')
  } finally {
    loading.value = false
  }
}

// 处理每页条数变化
const handleSizeChange = (val) => {
  pagination.value.itemsPerPage = val
  loadSubAccountData(pagination.value.currentPage, val)
}

// 处理当前页变化
const handleCurrentChange = (val) => {
  pagination.value.currentPage = val
  loadSubAccountData(val, pagination.value.itemsPerPage)
}

// 添加子账号
const addSubAccount = () => {
  dialogTitle.value = '添加子账号'
  Object.assign(currentSubAccount, {
    id: null,
    username: '',
    mark: '',
    email: '',
    roles: [],
    existingRoles: [],
    password: '',
    confirmPassword: '',
    isActive: true
  })
  dialogVisible.value = true
}

// 编辑子账号
const editSubAccount = (subAccount) => {
  dialogTitle.value = '编辑子账号'
  
  // 分离已有的角色和可选择的角色
  const existingRoles = [];
  const selectableRoles = [];
  
  if (subAccount.roles && Array.isArray(subAccount.roles)) {
    subAccount.roles.forEach(roleId => {
      // 隐藏 ROLE_SUPPLIER 和 ROLE_SUPPLIER_SUB_ACCOUNT 角色
      if (roleId !== 'ROLE_SUPPLIER' && roleId !== 'ROLE_SUPPLIER_SUB_ACCOUNT') {
        // 在可用角色中查找匹配的角色
        const role = availableRoles.value.find(r => r.name === roleId)
        if (role) {
          selectableRoles.push(role.id)
        } else {
          // 如果找不到对应的角色，可能是已删除的角色，但仍显示其描述
          existingRoles.push(roleId)
        }
      }
    })
  }
  
  Object.assign(currentSubAccount, {
    ...subAccount,
    roles: selectableRoles,
    existingRoles: existingRoles, // 存储已有的角色
    password: '',
    confirmPassword: ''
  })
  dialogVisible.value = true
}

// 切换子账号状态
const toggleSubAccountStatus = (subAccount) => {
  const action = subAccount.isActive ? '禁用' : '启用'
  ElMessageBox.confirm(
    `确定要${action}子账号 ${subAccount.username} 吗？`,
    `确认${action}`,
    {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    }
  ).then(async () => {
    try {
      const loginPath = props.supplierLoginPath || window.supplierLoginPath || ''
      const response = await fetch(`/supplier${loginPath}/sub-account/toggle-status/${subAccount.id}`, {
        method: 'POST',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Content-Type': 'application/json'
        }
      })
      
      const result = await response.json()
      
      if (result.success) {
        ElMessage.success(`${action}成功`)
        loadSubAccountData(pagination.value.currentPage, pagination.value.itemsPerPage)
      } else {
        ElMessage.error(result.error || `${action}失败`)
      }
    } catch (error) {
      ElMessage.error(`${action}过程中发生错误`)
    }
  }).catch(() => {
    ElMessage.info(`已取消${action}`)
  })
}

// 删除子账号
const deleteSubAccount = (subAccount) => {
  ElMessageBox.confirm(
    `确定要删除子账号 ${subAccount.username} 吗？此操作不可恢复！`,
    '确认删除',
    {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    }
  ).then(async () => {
    try {
      const loginPath = props.supplierLoginPath || window.supplierLoginPath || ''
      const response = await fetch(`/supplier${loginPath}/sub-account/delete/${subAccount.id}`, {
        method: 'POST',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Content-Type': 'application/json'
        }
      })
      
      const result = await response.json()
      
      if (result.success) {
        ElMessage.success('删除成功')
        loadSubAccountData(pagination.value.currentPage, pagination.value.itemsPerPage)
      } else {
        ElMessage.error(result.error || '删除失败')
      }
    } catch (error) {
      ElMessage.error('删除过程中发生错误')
    }
  }).catch(() => {
    ElMessage.info('已取消删除')
  })
}

// 保存子账号
const saveSubAccount = async () => {
  // 验证密码长度
  if (!currentSubAccount.id && (!currentSubAccount.password || currentSubAccount.password.length < 8)) {
    ElMessage.error('密码必须至少包含8个字符')
    return
  }
  
  if (currentSubAccount.id && currentSubAccount.password && currentSubAccount.password.length < 8) {
    ElMessage.error('密码必须至少包含8个字符')
    return
  }
  
  // 验证确认密码（去除首尾空格后比较）
  if ((!currentSubAccount.id || currentSubAccount.password)) {
    const pwd = (currentSubAccount.password || '').trim()
    const confirmPwd = (currentSubAccount.confirmPassword || '').trim()
    
    if (pwd !== confirmPwd) {
      console.log('密码不一致:', {
        password: pwd,
        confirmPassword: confirmPwd,
        passwordLength: pwd.length,
        confirmPasswordLength: confirmPwd.length
      })
      ElMessage.error('两次输入的密码不一致')
      return
    }
  }
  
  try {
    const loginPath = props.supplierLoginPath || window.supplierLoginPath || ''
    const url = currentSubAccount.id 
      ? `/supplier${loginPath}/sub-account/update/${currentSubAccount.id}`
      : `/supplier${loginPath}/sub-account/create`
    
    // 准备角色数据，确保包含 ROLE_SUPPLIER 和 ROLE_SUPPLIER_SUB_ACCOUNT
    const roleIds = [...currentSubAccount.roles]
    
    const response = await fetch(url, {
      method: 'POST',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        username: currentSubAccount.username,
        mark: currentSubAccount.mark,
        email: currentSubAccount.email,
        roles: roleIds,
        password: currentSubAccount.password,
        confirmPassword: currentSubAccount.confirmPassword,
        isActive: currentSubAccount.isActive
      })
    })
    
    const result = await response.json()
    
    if (result.success) {
      ElMessage.success(result.message || (currentSubAccount.id ? '更新成功' : '添加成功'))
      dialogVisible.value = false
      loadSubAccountData(pagination.value.currentPage, pagination.value.itemsPerPage)
    } else {
      ElMessage.error(result.error || (currentSubAccount.id ? '更新失败' : '添加失败'))
    }
  } catch (error) {
    ElMessage.error((currentSubAccount.id ? '更新' : '添加') + '过程中发生错误')
  }
}

// 获取角色名称（显示描述）
const getRoleNames = (roleIds) => {
  if (!roleIds || !Array.isArray(roleIds) || roleIds.length === 0) return []
  
  // 过滤掉基础角色
  const filteredRoleIds = roleIds.filter(id => 
    id !== 'ROLE_SUPPLIER' && id !== 'ROLE_SUPPLIER_SUB_ACCOUNT'
  )
  
  if (filteredRoleIds.length === 0) return []
  
  return filteredRoleIds.map(roleId => {
    // 在可用角色中查找匹配的角色名称
    const role = availableRoles.value.find(r => r.name === roleId)
    if (role) {
      // 优先使用描述，如果没有描述则使用名称
      return role.description || role.name
    }
    
    // 如果找不到对应的角色，返回原始ID
    return roleId
  })
}

// 获取已设置角色的名称（显示描述）
const getExistingRoleNames = (roleIds) => {
  if (!roleIds || !Array.isArray(roleIds) || roleIds.length === 0) return []
  
  return roleIds.map(roleId => {
    // 在可用角色中查找匹配的角色名称
    const role = availableRoles.value.find(r => r.name === roleId)
    if (role) {
      // 优先使用描述，如果没有描述则使用名称
      return role.description || role.name
    }
    
    // 如果找不到对应的角色，返回原始ID
    return roleId
  })
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

// 暴露方法供父组件调用（按需加载）
defineExpose({
  loadSubAccountData
})
</script>

<style scoped>
.sub-account-container {
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

.sub-account-card {
  width: 100%;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 15px;
}

.card-header-title {
  font-size: 18px;
  font-weight: bold;
  display: flex;
  align-items: center;
}

.card-icon {
  margin-right: 10px;
  vertical-align: middle;
}

.card-header-info {
  font-size: 14px;
  color: #909399;
}

.sub-account-table {
  width: 100% !important;
  font-size: 14px;
}

.sub-account-table :deep(.el-table__header th) {
  background-color: #f5f7fa;
  color: #606266;
  font-weight: bold;
}

.sub-account-table :deep(.el-table__row:hover) {
  background-color: #f5f7fa;
}

.sub-account-table :deep(.el-table__row:hover td) {
  background-color: #f5f7fa !important;
}

.username-cell {
  display: flex;
  align-items: center;
  gap: 8px;
}

.user-icon {
  color: #409eff;
}

.username-text {
  font-weight: 500;
}

.roles-container {
  display: flex;
  flex-wrap: wrap;
  gap: 5px;
  justify-content: center;
  align-items: center;
  min-height: 24px;
}

.role-tag {
  font-size: 12px;
}

.no-roles {
  color: #909399;
  font-size: 12px;
}

.status-tag {
  font-size: 12px;
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

.action-buttons {
  display: flex;
  gap: 5px;
  flex-wrap: nowrap;
  justify-content: center;
}

.action-buttons :deep(.el-button) {
  padding: 6px 10px;
}

.pagination-container {
  margin-top: 20px;
  display: flex;
  justify-content: center;
  width: 100%;
}

.sub-account-dialog :deep(.el-dialog__body) {
  padding: 20px;
}

.dialog-hint {
  margin-bottom: 20px;
}

.sub-account-form :deep(.el-form-item) {
  margin-bottom: 20px;
}

.sub-account-form :deep(.el-form-item__label) {
  font-weight: 500;
}

.password-hint {
  font-size: 12px;
  color: #999;
  margin-top: 5px;
}

.roles-info {
  margin-top: 10px;
}

.roles-label {
  font-size: 12px;
  color: #666;
  margin-bottom: 5px;
}

.roles-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 5px;
}
</style>