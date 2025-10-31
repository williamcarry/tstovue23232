<template>
  <div class="member-list">
    <!-- 页面头部 -->
    <div class="page-header">
      <h2 class="page-title">
        <el-icon class="title-icon"><User /></el-icon>
        会员列表
      </h2>
      <div class="header-actions">
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
          <el-col :span="5" :xs="24">
            <el-form-item label="关键词" class="right-aligned-label">
              <el-input v-model="searchForm.keyword" placeholder="用户名/邮箱/昵称/姓名/手机号" />
            </el-form-item>
          </el-col>
          <el-col :span="5" :xs="24">
            <el-form-item label="账号状态" class="right-aligned-label">
              <el-select v-model="searchForm.isActive" placeholder="请选择状态" clearable>
                <el-option label="全部" value="" />
                <el-option label="激活" value="1" />
                <el-option label="禁用" value="0" />
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :span="4" :xs="24" class="action-col">
            <el-form-item label=" ">
              <div class="form-actions">
                <el-button type="primary" @click="searchMembers" :icon="Search">
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
    
    <!-- 会员表格 -->
    <el-card class="table-card">
      <template #header>
        <div class="table-header">
          <div class="table-header-title">
            <el-icon class="table-icon"><List /></el-icon>
            会员列表
          </div>
          <div class="table-header-info">
            共 {{ pagination.totalItems }} 条记录
          </div>
        </div>
      </template>
      
      <el-table 
        :data="members" 
        class="member-table" 
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
        <el-table-column prop="username" label="用户名" min-width="120" show-overflow-tooltip />
        <el-table-column prop="email" label="邮箱" min-width="180" show-overflow-tooltip />
        <el-table-column prop="nickname" label="昵称" min-width="120" show-overflow-tooltip />
        <el-table-column prop="realName" label="真实姓名" min-width="120" show-overflow-tooltip />
        <el-table-column prop="mobile" label="手机号" min-width="120" show-overflow-tooltip />
        <el-table-column prop="vipLevelName" label="VIP等级" min-width="100">
          <template #default="scope">
            <el-tag :type="scope.row.vipLevel === 0 ? 'info' : 'success'">
              {{ scope.row.vipLevelName }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="balance" label="账户余额" min-width="100" />
        <el-table-column prop="isActive" label="账号状态" min-width="100">
          <template #default="scope">
            <el-tag :type="scope.row.isActive ? 'success' : 'danger'">
              {{ scope.row.isActive ? '激活' : '禁用' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="createdAt" label="注册时间" min-width="160" />
        <el-table-column label="操作" min-width="200" fixed="right">
          <template #default="scope">
            <el-button size="small" @click="viewMember(scope.row)">查看</el-button>
            <el-button size="small" @click="editMember(scope.row)">编辑</el-button>
            <el-button size="small" type="warning" @click="showChangePasswordDialog(scope.row)">更改密码</el-button>
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
    
    <!-- 查看对话框 -->
    <el-dialog v-model="viewDialogVisible" title="查看会员" width="800px">
      <el-form :model="currentMember" label-width="120px">
        <!-- 账号信息 -->
        <el-divider content-position="left">账号信息</el-divider>
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="会员ID">
              <el-input v-model="currentMember.id" disabled />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="用户名">
              <el-input v-model="currentMember.username" disabled />
            </el-form-item>
          </el-col>
        </el-row>
        
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="邮箱">
              <el-input v-model="currentMember.email" disabled />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="手机号">
              <el-input v-model="currentMember.mobile" disabled />
            </el-form-item>
          </el-col>
        </el-row>
        
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="昵称">
              <el-input v-model="currentMember.nickname" disabled />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="真实姓名">
              <el-input v-model="currentMember.realName" disabled />
            </el-form-item>
          </el-col>
        </el-row>
        
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="性别">
              <el-select v-model="currentMember.gender" disabled>
                <el-option label="未知" :value="0" />
                <el-option label="男" :value="1" />
                <el-option label="女" :value="2" />
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="身份证号">
              <el-input v-model="currentMember.individualIdCard" disabled />
            </el-form-item>
          </el-col>
        </el-row>
        
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="注册时间">
              <el-input v-model="currentMember.createdAt" disabled />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="最后登录时间">
              <el-input v-model="currentMember.lastLoginAt" disabled />
            </el-form-item>
          </el-col>
        </el-row>
        
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="最后更新时间">
              <el-input v-model="currentMember.updatedAt" disabled />
            </el-form-item>
          </el-col>
        </el-row>
        
        <!-- VIP信息 -->
        <el-divider content-position="left">VIP信息</el-divider>
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="VIP等级">
              <el-input v-model="currentMember.vipLevelName" disabled />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="账户余额">
              <el-input v-model="currentMember.balance" disabled />
            </el-form-item>
          </el-col>
        </el-row>
        
        <!-- 证件信息 -->
        <el-divider content-position="left">证件信息</el-divider>
        <el-row :gutter="20">
          <el-col :span="12">
            <div class="upload-container">
              <div class="upload-section">
                <div class="upload-label">身份证正面</div>
                <div v-if="currentMember.individualIdFrontUrl" class="existing-image-preview" @click="showImageViewer([currentMember.individualIdFrontUrl])">
                  <img :src="currentMember.individualIdFrontUrl" alt="身份证正面" class="existing-preview-image" />
                  <div class="image-overlay">
                    <el-icon><ZoomIn /></el-icon>
                  </div>
                </div>
                <div v-else class="no-image">无身份证正面</div>
              </div>
            </div>
          </el-col>
          <el-col :span="12">
            <div class="upload-container">
              <div class="upload-section">
                <div class="upload-label">身份证反面</div>
                <div v-if="currentMember.individualIdBackUrl" class="existing-image-preview" @click="showImageViewer([currentMember.individualIdBackUrl])">
                  <img :src="currentMember.individualIdBackUrl" alt="身份证反面" class="existing-preview-image" />
                  <div class="image-overlay">
                    <el-icon><ZoomIn /></el-icon>
                  </div>
                </div>
                <div v-else class="no-image">无身份证反面</div>
              </div>
            </div>
          </el-col>
        </el-row>
      </el-form>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="viewDialogVisible = false">关闭</el-button>
        </span>
      </template>
    </el-dialog>
    
    <!-- 编辑对话框 -->
    <el-dialog v-model="editDialogVisible" title="编辑会员" width="800px">
      <el-form :model="currentMember" :rules="rules" ref="editFormRef" label-width="120px">
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="用户名">
              <el-input v-model="currentMember.username" disabled />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="邮箱" prop="email">
              <el-input v-model="currentMember.email" />
            </el-form-item>
          </el-col>
        </el-row>
        
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="昵称">
              <el-input v-model="currentMember.nickname" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="真实姓名">
              <el-input v-model="currentMember.realName" />
            </el-form-item>
          </el-col>
        </el-row>
        
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="手机号">
              <el-input v-model="currentMember.mobile" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="性别">
              <el-select v-model="currentMember.gender">
                <el-option label="未知" :value="0" />
                <el-option label="男" :value="1" />
                <el-option label="女" :value="2" />
              </el-select>
            </el-form-item>
          </el-col>
        </el-row>
        
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="账号状态">
              <el-select v-model="currentMember.isActive">
                <el-option label="激活" :value="1" />
                <el-option label="禁用" :value="0" />
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="VIP等级">
              <el-select v-model="currentMember.vipLevel">
                <el-option 
                  v-for="level in vipLevels" 
                  :key="level.value" 
                  :label="level.label" 
                  :value="level.value" 
                />
              </el-select>
            </el-form-item>
          </el-col>
        </el-row>
      </el-form>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="editDialogVisible = false">取消</el-button>
          <el-button type="primary" @click="saveMember">保存</el-button>
        </span>
      </template>
    </el-dialog>
    
    <!-- 修改密码对话框 -->
    <el-dialog v-model="changePasswordDialogVisible" title="修改会员密码" width="500px">
      <el-form :model="passwordForm" label-width="100px">
        <el-form-item label="用户名">
          <el-input v-model="passwordForm.username" disabled />
        </el-form-item>
        <el-form-item label="新密码" prop="newPassword" :rules="[{ required: true, message: '请输入新密码', trigger: 'blur' }]">
          <el-input v-model="passwordForm.newPassword" type="password" show-password />
          <div class="password-hint">密码必须至少8位数</div>
        </el-form-item>
        <el-form-item label="确认密码" prop="confirmPassword" :rules="[
          { required: true, message: '请确认新密码', trigger: 'blur' },
          { validator: validateConfirmPassword, trigger: 'blur' }
        ]">
          <el-input v-model="passwordForm.confirmPassword" type="password" show-password />
        </el-form-item>
      </el-form>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="changePasswordDialogVisible = false">取消</el-button>
          <el-button type="primary" @click="changePassword" :loading="passwordChanging">确定</el-button>
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
import { ref, onMounted, defineExpose, reactive } from 'vue'
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
  ElMessageBox,
  ElMessage,
  ElPagination,
  ElDivider,
  ElRow,
  ElCol,
  ElIcon,
  ElImageViewer
} from 'element-plus'
import { ZoomIn, User, Search, Refresh, RefreshLeft, List } from '@element-plus/icons-vue'

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
const members = ref([])
const loading = ref(false)
const viewDialogVisible = ref(false)
const editDialogVisible = ref(false)
const currentMember = ref({})
const pagination = ref({
  currentPage: 1,
  totalPages: 1,
  totalItems: 0,
  itemsPerPage: 20
})

// 图片查看器相关
const imageViewerVisible = ref(false)
const imageViewerUrls = ref([])

// 搜索表单
const searchForm = ref({
  keyword: '',
  isActive: ''
})

// 显示图片查看器
const showImageViewer = (urls) => {
  imageViewerUrls.value = urls
  imageViewerVisible.value = true
}

// 关闭图片查看器
const closeImageViewer = () => {
  imageViewerVisible.value = false
  imageViewerUrls.value = []
}

// 获取文件签名URL
const getSignedUrl = async (key) => {
  if (!key || key.startsWith('http')) {
    return key
  }
  
  try {
    const loginPath = props.adminLoginPath || window.adminLoginPath || ''
    const signedUrlEndpoint = `/admin${loginPath}/member/file/signed-url`
    
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

// 为会员数据添加图片URL
const addImageUrlsToMember = async (member) => {
  const memberWithUrls = { ...member }
  
  // 为身份证正面获取签名URL
  if (memberWithUrls.individualIdFront) {
    memberWithUrls.individualIdFrontUrl = await getSignedUrl(memberWithUrls.individualIdFront)
  }
  
  // 为身份证反面获取签名URL
  if (memberWithUrls.individualIdBack) {
    memberWithUrls.individualIdBackUrl = await getSignedUrl(memberWithUrls.individualIdBack)
  }
  
  return memberWithUrls
}

// 加载会员数据
const loadMemberData = async (page = 1, limit = 20) => {
  loading.value = true
  try {
    const params = new URLSearchParams({
      page: page,
      limit: limit,
      keyword: searchForm.value.keyword,
      isActive: searchForm.value.isActive
    })
    
    const url = `/admin${props.adminLoginPath}/member/list/tab?${params}`
    
    const response = await fetch(url, {
      method: 'GET',
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    
    const result = await response.json()
    
    if (result.success) {
      members.value = result.data
      pagination.value = result.pagination
    } else {
      ElMessage.error(result.message || '加载数据失败')
    }
  } catch (error) {
    ElMessage.error('加载数据失败: ' + error.message)
  } finally {
    loading.value = false
  }
}

// 刷新数据
const refreshData = () => {
  loadMemberData(pagination.value.currentPage, pagination.value.itemsPerPage)
}

// 搜索会员
const searchMembers = () => {
  pagination.value.currentPage = 1
  loadMemberData(1, pagination.value.itemsPerPage)
}

// 重置搜索
const resetSearch = () => {
  searchForm.value = {
    keyword: '',
    isActive: ''
  }
  pagination.value.currentPage = 1
  loadMemberData(1, pagination.value.itemsPerPage)
}

// 查看会员
const viewMember = async (member) => {
  // 为查看的会员获取图片签名URL
  currentMember.value = await addImageUrlsToMember({ ...member })
  viewDialogVisible.value = true
}

// 编辑会员
const editMember = async (member) => {
  // 为编辑的会员获取图片签名URL
  currentMember.value = await addImageUrlsToMember({ ...member })
  editDialogVisible.value = true
}

// 会员更新回调
const onMemberUpdated = (updatedMember) => {
  ElMessage.success('会员更新成功')
  editDialogVisible.value = false
  refreshData()
}

// 处理分页大小变化
const handleSizeChange = (val) => {
  pagination.value.itemsPerPage = val
  loadMemberData(pagination.value.currentPage, val)
}

// 处理当前页变化
const handleCurrentChange = (val) => {
  pagination.value.currentPage = val
  loadMemberData(val, pagination.value.itemsPerPage)
}

// 组件挂载时加载数据
onMounted(() => {
  // 只有当 autoLoad 为 true 时才自动加载数据
  if (props.autoLoad) {
    loadMemberData()
  }
})

// 暴露方法给父组件调用
defineExpose({
  loadMemberData
})

// 修改密码相关
const changePasswordDialogVisible = ref(false)
const passwordChanging = ref(false)
const passwordForm = reactive({
  id: null,
  username: '',
  newPassword: '',
  confirmPassword: ''
})

// 显示修改密码对话框
const showChangePasswordDialog = (member) => {
  passwordForm.id = member.id
  passwordForm.username = member.username
  passwordForm.newPassword = ''
  passwordForm.confirmPassword = ''
  changePasswordDialogVisible.value = true
}

// 验证确认密码
const validateConfirmPassword = (rule, value, callback) => {
  if (value === '') {
    callback(new Error('请再次输入密码'))
  } else if (value !== passwordForm.newPassword) {
    callback(new Error('两次输入的密码不一致'))
  } else {
    callback()
  }
}

// 修改密码
const changePassword = async () => {
  if (!passwordForm.newPassword || !passwordForm.confirmPassword) {
    ElMessage.error('请输入密码')
    return
  }
  
  if (passwordForm.newPassword !== passwordForm.confirmPassword) {
    ElMessage.error('两次输入的密码不一致')
    return
  }
  
  if (passwordForm.newPassword.length < 8) {
    ElMessage.error('密码长度必须至少8位')
    return
  }
  
  passwordChanging.value = true
  try {
    const response = await fetch(`/admin${props.adminLoginPath}/member/${passwordForm.id}/update`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify({
        password: passwordForm.newPassword
      })
    })
    
    const result = await response.json()
    
    if (result.success) {
      ElMessage.success(result.message || '密码修改成功')
      changePasswordDialogVisible.value = false
    } else {
      ElMessage.error(result.error || '密码修改失败')
    }
  } catch (error) {
    ElMessage.error('密码修改失败: ' + error.message)
  } finally {
    passwordChanging.value = false
  }
}

</script>

<style scoped>
.member-list {
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
  width: 100px;
  margin-right: 10px;
  margin-bottom: 0;
  text-align: right;
  flex-shrink: 0;
  display: flex;
  justify-content: flex-end;
}

.search-form :deep(.el-form-item__content) {
  width: calc(100% - 90px);
  display: flex;
  align-items: center;
  min-width: 100px;
}

.right-aligned-label :deep(.el-form-item__label) {
  text-align: right !important;
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

.member-table {
  width: 100% !important;
  font-size: 14px;
}

.member-table :deep(.el-table__header th) {
  background-color: #f5f7fa;
  color: #606266;
  font-weight: bold;
}

.member-table :deep(.el-table__row:hover) {
  background-color: #f5f7fa;
}

.member-table :deep(.el-table__row:hover td) {
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

.password-hint {
  font-size: 12px;
  color: #909399;
  margin-top: 5px;
}

.upload-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  width: 100%;
  padding: 15px;
  background-color: #f8f9fa;
  border-radius: 8px;
  transition: all 0.3s ease;
}

.upload-container:hover {
  background-color: #e9ecef;
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.upload-section {
  display: flex;
  flex-direction: column;
  align-items: center;
  width: 100%;
}

.upload-label {
  text-align: center;
  margin-bottom: 10px;
  font-size: 14px;
  font-weight: 500;
  color: #495057;
}

.existing-image-preview {
  width: 200px;
  height: 150px;
  border: 2px dashed #d9d9d9;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  background-color: #ffffff;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
  cursor: pointer;
  position: relative;
  transition: all 0.3s ease;
}

.existing-image-preview:hover {
  border-color: #409eff;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.existing-preview-image {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
  transition: transform 0.3s ease;
}

.existing-preview-image:hover {
  transform: scale(1.05);
}

.image-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity 0.3s ease;
}

.existing-image-preview:hover .image-overlay {
  opacity: 1;
}

.image-overlay .el-icon {
  color: white;
  font-size: 24px;
}

.no-image {
  color: #909399;
  font-size: 14px;
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