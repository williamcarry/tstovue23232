<template>
  <div class="admin-sidebar">
    <el-menu
      :default-active="activeIndex"
      class="nav-menu"
      @select="handleSelect"
      :unique-opened="true"
      background-color="#34495e"
      text-color="#ecf0f1"
      active-text-color="#1abc9c"
    >
      <!-- 主页 -->
      <el-menu-item index="dashboard">
        <el-icon><House /></el-icon>
        <span>主页</span>
      </el-menu-item>
      <!-- 个人信息 -->
      <el-menu-item index="profile-edit">
        <el-icon><UserFilled /></el-icon>
        <span>个人信息</span>
      </el-menu-item>
      <!-- 管理员管理（仅超级管理员可见） -->
      <el-sub-menu index="admin-management" v-if="isSuperAdmin">
        <template #title>
          <el-icon><User /></el-icon>
          <span>管理员管理</span>
        </template>
        <el-menu-item index="user-list">管理员列表</el-menu-item>
        <el-menu-item index="user-roles">角色管理</el-menu-item>
      </el-sub-menu>
      
      <!-- 供应商管理 -->
      <el-sub-menu index="supplier-management" v-if="hasSupplierManagementPermission">
        <template #title>
          <el-icon><User /></el-icon>
          <span>供应商管理</span>
        </template>
        <el-menu-item index="supplier-list">供应商列表</el-menu-item>
        <el-menu-item index="supplier-create">添加供应商</el-menu-item>
      </el-sub-menu> 
      <!-- 会员管理 -->
      <el-sub-menu index="member-management" v-if="hasMemberManagementPermission">
        <template #title>
          <el-icon><User /></el-icon>
          <span>会员管理</span>
        </template>
        <el-menu-item index="member-list">会员列表</el-menu-item>
        <el-menu-item index="member-create">添加会员</el-menu-item>
      </el-sub-menu>
      <!-- 商品管理 -->
      <el-sub-menu index="product-management" v-if="hasProductManagementPermission">
        <template #title>
          <el-icon><Box /></el-icon>
          <span>商品管理</span>
        </template>
        <el-menu-item index="product-list">商品列表</el-menu-item>
      </el-sub-menu>
      <!-- 财务相关 -->
      <el-sub-menu index="finance-management" v-if="hasFinanceManagementPermission">
        <template #title>
          <el-icon><Box /></el-icon>
          <span>财务相关</span>
        </template>
        <el-menu-item index="balance-history-list">余额记录</el-menu-item>
        <el-menu-item index="withdrawal-list">提现申请</el-menu-item>
      </el-sub-menu>
      <!-- 分隔线 -->
      <div class="menu-divider"></div>
      <!-- 网站菜单 -->
      <el-sub-menu index="website-menu" v-if="hasWebsiteMenuPermission">
        <template #title>
          <el-icon><Menu /></el-icon>
          <span>网站设计</span>
        </template>
        <el-menu-item index="site-config-list">网站参数</el-menu-item>
        <el-menu-item index="logistics-company-list">物流公司</el-menu-item>
        <!-- 主页侧边栏子菜单 -->
        <el-sub-menu index="sidebar-menu">
          <template #title>
            <span>网站主页</span>
          </template>
          <el-menu-item index="menu-category-list">一级菜单</el-menu-item>
          <el-menu-item index="menu-subcategory-list">二级菜单</el-menu-item>
          <el-menu-item index="menu-item-list">三级菜单</el-menu-item>
          <el-menu-item index="menu-promotion-list">菜单图片</el-menu-item>
        </el-sub-menu>
      </el-sub-menu>
    </el-menu>
  </div>
</template>

<script>
import { 
  House, 
  User, 
  Box, 
  Document, 
  UserFilled, 
  Setting,
  Menu
} from '@element-plus/icons-vue'
import {
  ElMenu,
  ElMenuItem,
  ElSubMenu,
  ElIcon
} from 'element-plus'

export default {
  name: 'AdminSidebar',
  components: {
    House,
    User,
    Box,
    Document,
    UserFilled,
    Setting,
    Menu,
    ElMenu,
    ElMenuItem,
    ElSubMenu,
    ElIcon
  },
  props: {
    isSuperAdmin: {
      type: Boolean,
      default: false
    },
    userRoles: {
      type: Array,
      default: () => []
    }
  },
  data() {
    return {
      activeIndex: 'dashboard'
    }
  },
  computed: {
    // 检查用户是否具有会员管理权限
    hasMemberManagementPermission() {
      return this.isSuperAdmin || this.userRoles.includes('ROLE_ADMIN_MEMBER');
    },
    // 检查用户是否具有供应商管理权限
    hasSupplierManagementPermission() {
      return this.isSuperAdmin || this.userRoles.includes('ROLE_ADMIN_SUPPLIER');
    },
    // 检查用户是否具有商品管理权限
    hasProductManagementPermission() {
      return this.isSuperAdmin || this.userRoles.includes('ROLE_ADMIN_PRODUCT');
    },
    // 检查用户是否具有网站菜单管理权限
    hasWebsiteMenuPermission() {
      return this.isSuperAdmin || this.userRoles.includes('ROLE_ADMIN_SITEMENU');
    },
    // 检查用户是否具有财务管理权限
    hasFinanceManagementPermission() {
      return this.isSuperAdmin || this.userRoles.includes('ROLE_ADMIN_BALANCE');
    }
  },
  methods: {
    handleSelect(key, keyPath) {
      // 触发自定义事件，让父组件处理导航逻辑
      this.$emit('navigate', key, keyPath);
      
      // 更新激活状态
      this.activeIndex = key;
    }
  },
  mounted() {
    // 监听来自其他组件的导航事件
    window.addEventListener('navigate-to', (event) => {
      this.activeIndex = event.detail.key;
    });
  }
}
</script>

<style scoped>
.admin-sidebar {
  width: 200px;
  background: #34495e;
  color: white;
  overflow-y: auto;
  font-size: 14px;
  flex-shrink: 0;
  position: fixed;
  top: 60px;
  left: 0;
  bottom: 0;
  height: calc(100vh - 60px);
}

.nav-menu {
  border-right: none;
}

.nav-menu :deep(.el-sub-menu__title:hover) {
  background-color: #2c3e50 !important;
}

.nav-menu :deep(.el-menu-item:hover) {
  background-color: #2c3e50 !important;
}

/* 分隔线样式 */
.menu-divider {
  height: 1px;
  background-color: #4a4a4a;
  margin: 10px 15px;
}

/* 移动端适配 */
@media (max-width: 768px) {
  .admin-sidebar {
    width: 60px;
  }
  
  :deep(.el-menu--collapse) {
    width: 60px;
  }
  
  :deep(.el-menu--collapse .el-sub-menu__title span),
  :deep(.el-menu--collapse .el-menu-item span) {
    display: none;
  }
  
  :deep(.el-menu--collapse .el-sub-menu__title .el-sub-menu__icon-arrow),
  :deep(.el-menu--collapse .el-menu-item .el-icon) {
    margin-right: 0;
  }
}
</style>