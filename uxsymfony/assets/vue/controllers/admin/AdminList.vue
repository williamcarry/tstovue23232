<template>
  <div class="admin-container">
    <!-- 页面头部 -->
    <div class="page-header">
      <h2 class="page-title">
        <el-icon class="title-icon"><User /></el-icon>
        管理员管理
      </h2>
      <div class="header-actions">
        <el-button type="primary" @click="addAdmin" :icon="Plus">
          添加管理员
        </el-button>
      </div>
    </div>
    
    <!-- 管理员列表卡片 -->
    <el-card class="admin-card">
      <template #header>
        <div class="card-header">
          <div class="card-header-title">
            <el-icon class="card-icon"><List /></el-icon>
            管理员列表
          </div>
          <div class="card-header-info">
            共 {{ admins.length }} 个管理员
          </div>
        </div>
      </template>
      
      <el-table 
        :data="admins" 
        class="admin-table" 
        :fit="true"
        :border="false"
        :header-cell-style="{ textAlign: 'center' }"
        :cell-style="{ textAlign: 'center' }"
        :stripe="true"
        empty-text="暂无管理员数据"
        :row-class-name="tableRowClassName"
      >
        <el-table-column label="序号" width="60" fixed>
          <template #default="scope">
            {{ scope.$index + 1 }}
          </template>
        </el-table-column>
        <el-table-column prop="username" label="用户名" min-width="150" show-overflow-tooltip>
          <template #default="scope">
            <div class="username-cell">
              <el-icon class="user-icon" :class="{ 'super-admin': scope.row.username === 'superadmin' }">
                <User />
              </el-icon>
              <span class="username-text">{{ scope.row.username }}</span>
              <el-tag 
                v-if="scope.row.username === 'superadmin'" 
                class="super-tag"
                type="danger"
                effect="dark"
                size="small"
              >
                超级管理员
              </el-tag>
            </div>
          </template>
        </el-table-column>
        <el-table-column prop="name" label="标记" min-width="150" show-overflow-tooltip />
        <el-table-column prop="email" label="邮箱" min-width="200" show-overflow-tooltip />
        <el-table-column label="系统角色" min-width="250">
          <template #default="scope">
            <div class="roles-container">
              <template v-if="scope.row.role">
                <el-tag
                  v-for="(role, index) in scope.row.role.split(', ')" 
                  :key="index" 
                  class="role-tag"
                  type="primary"
                  effect="dark"
                  size="small"
                >
                  {{ role }}
                </el-tag>
              </template>
              <span v-else class="no-roles">无角色</span>
            </div>
          </template>
        </el-table-column>
        <el-table-column label="操作" min-width="180" fixed="right">
          <template #default="scope">
            <div class="action-buttons">
              <!-- 当用户名不为superadmin时显示编辑按钮 -->
              <el-button 
                v-if="scope.row.username !== 'superadmin'" 
                size="small" 
                @click="editAdmin(scope.row)"
                :icon="Edit"
              >
                编辑
              </el-button>
              <!-- 当用户名不为superadmin时显示删除按钮 -->
              <el-button 
                v-if="scope.row.username !== 'superadmin'" 
                size="small" 
                type="danger" 
                @click="deleteAdmin(scope.row)"
                :icon="Delete"
              >
                删除
              </el-button>
            </div>
          </template>
        </el-table-column>
      </el-table>
    </el-card>
    
    <!-- 添加/编辑管理员对话框 -->
    <el-dialog 
      v-model="dialogVisible" 
      :title="dialogTitle"
      width="600px"
      class="admin-dialog"
    >
      <el-form 
        :model="currentAdmin" 
        label-width="80px"
        class="admin-form"
        label-position="left"
      >
        <el-row :gutter="20">
          <el-col :span="24">
            <el-form-item label="用户名">
              <el-input 
                v-model="currentAdmin.username" 
                :disabled="!!currentAdmin.id" 
                placeholder="请输入用户名"
              />
            </el-form-item>
          </el-col>
          <el-col :span="24">
            <el-form-item label="邮箱">
              <el-input 
                v-model="currentAdmin.email" 
                placeholder="请输入邮箱地址"
              />
            </el-form-item>
          </el-col>
          <el-col :span="24">
            <el-form-item label="标记">
              <el-input 
                v-model="currentAdmin.name" 
                placeholder="请输入标记/备注"
              />
            </el-form-item>
          </el-col>
          <el-col :span="24" v-if="isCurrentUserSuperAdmin">
            <el-form-item label="角色设置">
              <el-select 
                v-model="currentAdmin.systemRoles" 
                multiple 
                placeholder="请选择角色"
                style="width: 100%"
              >
                <el-option
                  v-for="role in systemRoleOptions"
                  :key="role.value"
                  :label="role.description"
                  :value="role.value"
                />
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :span="24">
            <el-form-item label="密码" :required="!currentAdmin.id">
              <el-input 
                v-model="currentAdmin.password" 
                type="password"
                :placeholder="currentAdmin.id ? '留空则不修改密码' : '请输入密码'"
              />
              <div class="password-hint">密码必须至少包含8个字符</div>
            </el-form-item>
          </el-col>
          <el-col :span="24" v-if="!currentAdmin.id || currentAdmin.password">
            <el-form-item label="确认密码" :required="!currentAdmin.id">
              <el-input 
                v-model="currentAdmin.confirmPassword" 
                type="password"
                placeholder="请再次输入密码"
              />
            </el-form-item>
          </el-col>
        </el-row>
      </el-form>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="dialogVisible = false">取消</el-button>
          <el-button type="primary" @click="saveAdmin">保存</el-button>
        </span>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, watch, computed } from 'vue'
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
  ElTag,
  ElSelect,
  ElOption,
  ElMessageBox,
  ElMessage,
  ElRow,
  ElCol,
  ElIcon
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
  admins: {
    type: Array,
    default: () => []
  },
  autoLoad: {
    type: Boolean,
    default: false
  },
  isCurrentUserSuperAdmin: {
    type: Boolean,
    default: false
  },
  roles: {
    type: Array,
    default: () => []
  }
});

// 定义 emits
const emit = defineEmits(['reload-admin-list']);

// 添加调试信息
onMounted(() => {
  console.log('AdminList component mounted with admins:', props.admins);
  console.log('AdminList component mounted with roles:', props.roles);
  // 只有当 autoLoad 为 true 时才自动加载数据
  if (props.autoLoad) {
    emit('reload-admin-list');
  }
});

// 监听admins属性的变化
watch(() => props.admins, (newAdmins) => {
  console.log('Admins prop updated:', newAdmins);
}, { immediate: true });

// 监听roles属性的变化
watch(() => props.roles, (newRoles) => {
  console.log('Roles prop updated:', newRoles);
}, { immediate: true });

// 定义响应式数据
const dialogVisible = ref(false)
const dialogTitle = ref('')
const availableRoles = ref(props.roles || [])
const currentAdmin = reactive({
  id: null,
  username: '',
  name: '',
  email: '',
  systemRoles: [], // 改为数组以支持多选
  roles: [],
  password: '',
  confirmPassword: ''
})

// 计算属性：系统角色选项
const systemRoleOptions = computed(() => {
  // 从availableRoles中提取type为admin的角色作为系统角色选项，排除系统默认角色
  const adminRoles = (props.roles || []).filter(role => role.type === 'admin' && 
    role.name !== 'ROLE_ADMIN' && role.name !== 'ROLE_SUPER_ADMIN')
    .map(role => ({
      description: role.description || role.name,
      value: role.description || role.name
    }));
  
  // 注意：不添加默认的"管理员"选项，因为ROLE_ADMIN是系统默认角色，不应出现在下拉框中
  // ROLE_SUPER_ADMIN角色只在明确选择"超级管理员"时才会被添加
  return adminRoles.filter(role => role.value !== 'ROLE_SUPER_ADMIN');
});

// 表格行样式
const tableRowClassName = ({ row }) => {
  // 确保 row.roles 是数组后再调用 includes 方法
  if (row.roles && Array.isArray(row.roles) && row.roles.includes('ROLE_SUPER_ADMIN')) {
    return 'super-admin-row';
  }
  return '';
};

// 定义方法
const addAdmin = () => {
  console.log('Add admin button clicked'); // 调试信息
  dialogTitle.value = '添加管理员'
  Object.assign(currentAdmin, { id: null, username: '', name: '', email: '', systemRoles: [], roles: [], password: '', confirmPassword: '' })
  dialogVisible.value = true
}

const editAdmin = (admin) => {
  console.log('Edit admin clicked:', admin); // 调试信息
  dialogTitle.value = '编辑管理员'
  
  // 转换系统角色为数组格式，排除系统默认角色
  let systemRoles = [];
  if (admin.roles && Array.isArray(admin.roles)) {
    // 获取所有admin类型的角色映射关系
    const roleMapping = {};
    (props.roles || []).filter(role => role.type === 'admin')
      .forEach(role => {
        roleMapping[role.name] = role.description || role.name;
      });
    
    // 默认角色映射
    const defaultRoleMapping = {
      'ROLE_ADMIN': '管理员',
      'ROLE_SUPER_ADMIN': '超级管理员'
    };
    
    // 合并默认映射和数据库映射
    const fullRoleMapping = { ...defaultRoleMapping, ...roleMapping };
    
    // 将管理员的实际角色转换为描述形式，排除系统默认角色
    // ROLE_SUPER_ADMIN角色不会显示在可编辑的角色列表中，因为它只在明确选择"超级管理员"时才会被添加
    systemRoles = admin.roles
      .filter(role => role !== 'ROLE_ADMIN' && role !== 'ROLE_SUPER_ADMIN') // 排除系统默认角色
      .map(role => {
        return fullRoleMapping[role] || role;
      });
  }
  
  Object.assign(currentAdmin, { 
    ...admin, 
    systemRoles: systemRoles,
    password: '', 
    confirmPassword: '' 
  })
  dialogVisible.value = true
}

const deleteAdmin = (admin) => {
  console.log('Delete admin clicked:', admin); // 调试信息
  
  // 检查是否是超级管理员
  if (admin.role === '超级管理员') {
    ElMessage({
      type: 'error',
      message: '无法删除超级管理员账户'
    });
    return;
  }
  
  ElMessageBox.confirm(
    `确定要删除管理员 ${admin.name} 吗？`,
    '确认删除',
    {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning',
    }
  ).then(() => {
    // 调用后端API删除管理员
    fetch(`/admin${window.adminLoginPath}/user/delete/${admin.id}`, {
      method: 'POST',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        _token: window.csrfToken // 如果有CSRF保护的话
      })
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        ElMessage({
          type: 'success',
          message: '删除成功',
        });
        // 通知父组件重新加载管理员列表
        emit('reload-admin-list');
      } else {
        ElMessage({
          type: 'error',
          message: data.error || '删除失败',
        });
      }
    })
    .catch(error => {
      ElMessage({
        type: 'error',
        message: '删除过程中发生错误: ' + error.message,
      });
    });
  }).catch(() => {
    ElMessage({
      type: 'info',
      message: '已取消删除',
    });
  });
}

const saveAdmin = () => {
  console.log('Save admin clicked'); // 调试信息
  
  // 验证密码长度（如果提供了密码或者是在添加管理员）
  if (!currentAdmin.id && (!currentAdmin.password || currentAdmin.password.length < 8)) {
    ElMessage({
      type: 'error',
      message: '密码必须至少包含8个字符'
    });
    return;
  }
  
  if (currentAdmin.id && currentAdmin.password && currentAdmin.password.length < 8) {
    ElMessage({
      type: 'error',
      message: '密码必须至少包含8个字符'
    });
    return;
  }
  
  // 验证确认密码（添加管理员时必须验证，编辑时只有当密码不为空时才验证）
  if ((!currentAdmin.id || currentAdmin.password) && currentAdmin.password !== currentAdmin.confirmPassword) {
    ElMessage({
      type: 'error',
      message: '两次输入的密码不一致'
    });
    return;
  }
  
  // 处理系统角色，排除系统默认角色
  const rolesToSave = currentAdmin.systemRoles.filter(role => role !== '管理员' && role !== '供应商管理员');
  // 只有在明确选择"超级管理员"时才会在后端添加ROLE_SUPER_ADMIN权限
  
  if (currentAdmin.id) {
    // 更新现有管理员
    fetch(`/admin${window.adminLoginPath}/user/update/${currentAdmin.id}`, {
      method: 'POST',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        username: currentAdmin.username,
        name: currentAdmin.name,
        email: currentAdmin.email,
        role: rolesToSave,
        password: currentAdmin.password
      })
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        ElMessage({
          type: 'success',
          message: '更新成功',
        });
        dialogVisible.value = false;
        // 通知父组件重新加载管理员列表
        emit('reload-admin-list');
      } else {
        ElMessage({
          type: 'error',
          message: data.error || '更新失败',
        });
      }
    })
    .catch(error => {
      ElMessage({
        type: 'error',
        message: '更新过程中发生错误: ' + error.message,
      });
    });
  } else {
    // 添加新管理员
    fetch(`/admin${window.adminLoginPath}/user/create`, {
      method: 'POST',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        username: currentAdmin.username,
        name: currentAdmin.name,
        email: currentAdmin.email,
        role: rolesToSave,
        password: currentAdmin.password
      })
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        ElMessage({
          type: 'success',
          message: data.message || '添加成功',
        });
        dialogVisible.value = false;
        // 通知父组件重新加载管理员列表
        emit('reload-admin-list');
      } else {
        ElMessage({
          type: 'error',
          message: data.error || '添加失败',
        });
      }
    })
    .catch(error => {
      ElMessage({
        type: 'error',
        message: '添加过程中发生错误: ' + error.message,
      });
    });
  }
}
</script>

<style scoped>
.admin-container {
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

.admin-card {
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

.admin-table {
  width: 100% !important;
  font-size: 14px;
}

.admin-table :deep(.el-table__header th) {
  background-color: #f5f7fa;
  color: #606266;
  font-weight: bold;
}

.admin-table :deep(.el-table__row:hover) {
  background-color: #f5f7fa;
}

.admin-table :deep(.el-table__row:hover td) {
  background-color: #f5f7fa !important;
}

.super-admin-row {
  background-color: #fff0f0; /* 浅红色背景 */
}

.username-cell {
  display: flex;
  align-items: center;
  gap: 8px;
}

.user-icon {
  color: #409eff;
}

.user-icon.super-admin {
  color: #f56c6c;
}

.username-text {
  font-weight: 500;
}

.super-tag {
  font-size: 12px;
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

.action-buttons {
  display: flex;
  gap: 5px;
  flex-wrap: nowrap;
  justify-content: center;
}

.action-buttons :deep(.el-button) {
  padding: 6px 10px;
}

.admin-dialog :deep(.el-dialog__body) {
  padding: 20px;
}

.admin-form :deep(.el-form-item) {
  margin-bottom: 20px;
}

.admin-form :deep(.el-form-item__label) {
  font-weight: 500;
}

.password-hint {
  font-size: 12px;
  color: #999;
  margin-top: 5px;
}
</style>