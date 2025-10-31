<template>
  <div class="supplier-sidebar">
    <el-menu
      :default-active="activeIndex"
      class="nav-menu"
      @select="handleSelect"
      :unique-opened="true"
      background-color="#8268BF"
      text-color="#ffffff"
      active-text-color="#FFD700"
    >
      <!-- 主页 -->
      <el-menu-item index="dashboard">
        <el-icon><House /></el-icon>
        <span>主页</span>
      </el-menu-item>
      
      <!-- 个人信息 -->
      <el-menu-item index="profile-edit" v-if="hasSupplierSuperRole">
        <el-icon><User /></el-icon>
        <span>个人信息</span>
      </el-menu-item>
      <!-- 子账号管理 -->
      <el-sub-menu index="sub-account" v-if="hasSupplierSuperRole">
        <template #title>
          <el-icon><UserFilled /></el-icon>
          <span>子账号管理</span>
        </template>
        <el-menu-item index="sub-account-list">
          <span>子账号列表</span>
        </el-menu-item>
      </el-sub-menu>
      <!-- 商品管理 -->
      <el-sub-menu index="product" v-if="hasSupplierSuperRole || hasSupplierProductRole">
        <template #title>
          <el-icon><Box /></el-icon>
          <span>商品管理</span>
        </template>
        <el-menu-item index="product-list">
          <span>商品列表</span>
        </el-menu-item>
        <el-menu-item index="product-add">
          <span>添加商品</span>
        </el-menu-item>
      </el-sub-menu>
      <!-- 财务与VIP -->
      <el-sub-menu index="finance-vip" v-if="hasSupplierSuperRole || hasSupplierBalanceRole">
        <template #title>
          <el-icon><List /></el-icon>
          <span>财务与VIP</span>
        </template>
        <el-menu-item index="balance-vip">
          <span>余额&VIP</span>
        </el-menu-item>
        <el-menu-item index="balance-history-list">
          <span>余额记录</span>
        </el-menu-item>
        <el-menu-item index="withdrawal-list">
          <span>提现申请</span>
        </el-menu-item>
      </el-sub-menu>
      
    </el-menu>
  </div>
</template>

<script>
import { 
  House, 
  Box, 
  Document, 
  List, 
  TrendCharts, 
  User,
  UserFilled
} from '@element-plus/icons-vue'
import {
  ElMenu,
  ElMenuItem,
  ElSubMenu,
  ElIcon
} from 'element-plus'

export default {
  name: 'SupplierSidebar',
  components: {
    House,
    Box,
    Document,
    List,
    TrendCharts,
    User,
    UserFilled,
    ElMenu,
    ElMenuItem,
    ElSubMenu,
    ElIcon
  },
  props: {
    // 添加供应商超级角色属性
    hasSupplierSuperRole: {
      type: Boolean,
      default: false
    },
    // 添加供应商余额角色属性
    hasSupplierBalanceRole: {
      type: Boolean,
      default: false
    },
    // 添加供应商商品角色属性
    hasSupplierProductRole: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      activeIndex: 'dashboard'
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
.supplier-sidebar {
  width: 200px;
  background: #8268BF;
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
  background-color: #6a5a9e !important;
}

.nav-menu :deep(.el-menu-item:hover) {
  background-color: #6a5a9e !important;
}

/* 激活状态的菜单项文字颜色为金色 */
.nav-menu :deep(.el-menu-item.is-active) {
  color: #FFD700 !important;
}

/* 激活状态的子菜单标题文字颜色为金色 */
.nav-menu :deep(.el-sub-menu.is-active .el-sub-menu__title) {
  color: #FFD700 !important;
}

/* 展开状态的子菜单标题文字颜色为金色 */
.nav-menu :deep(.el-sub-menu.is-opened .el-sub-menu__title) {
  color: #FFD700 !important;
}

/* 移动端适配 */
@media (max-width: 768px) {
  .supplier-sidebar {
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