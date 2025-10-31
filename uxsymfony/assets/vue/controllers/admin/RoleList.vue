<template>
  <div class="role-list-container">
    <!-- 页面头部 -->
    <div class="page-header">
      <h2 class="page-title">
        <el-icon class="title-icon"><User /></el-icon>
        角色列表
      </h2>
      <div class="header-actions">
        <el-button type="primary" @click="addRole" :icon="Plus">
          添加角色
        </el-button>
      </div>
    </div>
    
    <!-- 角色表格 -->
    <el-card class="table-card">
      <template #header>
        <div class="table-header">
          <div class="table-header-title">
            <el-icon class="table-icon"><List /></el-icon>
            角色列表
          </div>
          <div class="table-header-info">
            共 {{ roles.length }} 条记录
          </div>
        </div>
      </template>
      
      <el-table :data="roles" class="role-table" :fit="true" :border="true" :stripe="true">
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column prop="name" label="角色名称" min-width="300" show-overflow-tooltip />
        <el-table-column prop="description" label="描述" min-width="200" show-overflow-tooltip />
        <el-table-column prop="type" label="类型" width="150">
          <template #default="scope">
            <el-tag 
              :type="scope.row.type === '管理员后台权限' ? 'primary' : 'success'"
              effect="dark"
            >
              {{ scope.row.type }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="permissionCount" label="权限数量" width="100" />
        <el-table-column label="操作" width="150" fixed="right">
          <template #default="scope">
            <el-button size="small" type="danger" @click="deleteRole(scope.row)">删除</el-button>
          </template>
        </el-table-column>
      </el-table>
    </el-card>
    
    <!-- 添加角色对话框（只保留添加功能） -->
    <el-dialog v-model="dialogVisible" :title="dialogTitle" width="500px">
      <el-form :model="currentRole" label-width="80px">
        <el-form-item label="角色名称">
          <el-input v-model="currentRole.name" @input="formatRoleName" />
        </el-form-item>
        <el-form-item label="描述">
          <el-input v-model="currentRole.description" type="textarea" />
        </el-form-item>
        <el-form-item label="类型">
          <el-select v-model="currentRole.type" placeholder="请选择类型">
            <el-option label="管理员后台权限" value="管理员后台权限" />
            <el-option label="供应商后台权限" value="供应商后台权限" />
          </el-select>
        </el-form-item>
      </el-form>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="dialogVisible = false">取消</el-button>
          <el-button type="primary" @click="saveRole">保存</el-button>
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
  ElSelect,
  ElOption,
  ElMessageBox,
  ElMessage,
  ElIcon
} from 'element-plus'

import { User, Plus, List } from '@element-plus/icons-vue'

// 定义 props
const props = defineProps({
  roles: {
    type: Array,
    default: () => []
  }
});

// 定义 emits
const emit = defineEmits(['reload-role-list']);

// 定义响应式数据
const dialogVisible = ref(false)
const dialogTitle = ref('')
const currentRole = reactive({
  id: null,
  name: '',
  description: '',
  type: '管理员后台权限'
})

// 定义方法
const addRole = () => {
  dialogTitle.value = '添加角色'
  Object.assign(currentRole, { id: null, name: '', description: '', type: '管理员后台权限' })
  dialogVisible.value = true
}

// 格式化角色名称为大写
const formatRoleName = (value) => {
  currentRole.name = value.toUpperCase();
}

const deleteRole = (role) => {
  ElMessageBox.confirm(
    `确定要删除角色 ${role.name} 吗？`,
    '确认删除',
    {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning',
    }
  ).then(() => {
    // 调用后端API删除角色
    fetch(`/admin${window.adminLoginPath}/role/delete/${role.id}`, {
      method: 'POST',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Content-Type': 'application/json'
      }
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        ElMessage({
          type: 'success',
          message: '删除成功',
        });
        // 通知父组件重新加载角色列表
        emit('reload-role-list');
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

const saveRole = () => {
  // 添加新角色
  fetch(`/admin${window.adminLoginPath}/role/create`, {
    method: 'POST',
    headers: {
      'X-Requested-With': 'XMLHttpRequest',
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      name: currentRole.name,
      description: currentRole.description,
      type: currentRole.type
    })
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      ElMessage({
        type: 'success',
        message: '添加成功',
      });
      dialogVisible.value = false;
      // 通知父组件重新加载角色列表
      emit('reload-role-list');
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
</script>

<style scoped>
.role-list-container {
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

.role-table {
  width: 100% !important;
  font-size: 14px;
}

.role-table :deep(.el-table__header th) {
  background-color: #f5f7fa;
  color: #606266;
  font-weight: bold;
}

.role-table :deep(.el-table__row:hover) {
  background-color: #f5f7fa;
}

.role-table :deep(.el-table__row:hover td) {
  background-color: #f5f7fa !important;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 18px;
  font-weight: bold;
}

.dialog-footer {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}
</style>