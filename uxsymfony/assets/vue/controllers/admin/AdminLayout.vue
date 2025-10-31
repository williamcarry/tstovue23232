<template>
  <el-container class="admin-layout">
    <!-- 顶部 Banner -->
    <el-header class="admin-header">
      <div class="header-content">
        <div class="logo">
          <h1>{{ headerTitle }}</h1>
        </div>
        <div class="user-info" @click="editProfile">
          <span>欢迎，{{ userName }}!</span>
          <el-button type="text" @click.stop="logout">退出</el-button>
        </div>
      </div>
    </el-header>
    
    <admin-sidebar 
      @navigate="handleNavigation" 
      :is-super-admin="isCurrentUserSuperAdmin"
      :user-roles="currentAdmin.roles || []"
    />
    
    <el-container class="content-container">
      <el-header class="tags-view">
        <el-tag
          v-for="tag in tags"
          :key="tag.key"
          :type="activeTag === tag.key ? 'success' : 'info'"
          :closable="tag.key !== 'dashboard'"
          @click="switchToTab(tag.key)"
          @close="closeTab(tag.key)"
          class="tag-item"
        >
          {{ tag.title }}
        </el-tag>
      </el-header>
      
      <el-main class="content-wrapper">
        <!-- 主页内容 -->
        <div :class="['content-tab', { 'active': activeTag === 'dashboard' }]" id="dashboard-tab">
          <dashboard 
            :dashboard-title="'总后台'" 
            :user-name="userName"
          />
        </div>
        
        <!-- 管理员列表 -->
        <div :class="['content-tab', { 'active': activeTag === 'user-list' }]" id="user-list-tab">
          <admin-list 
            :admins="adminListData"
            :is-current-user-super-admin="isCurrentUserSuperAdmin"
            :roles="adminRoleListData"
            @reload-admin-list="reloadAdminListData"
          />
        </div>
        
        <!-- 添加管理员 -->
        <div :class="['content-tab', { 'active': activeTag === 'user-create' }]" id="user-create-tab">
          <admin-profile-edit 
            :is-create-mode="true"
            :submit-url="adminCreateUrl"
          />
        </div>
        
        <!-- 角色管理 -->
        <div :class="['content-tab', { 'active': activeTag === 'user-roles' }]" id="user-roles-tab">
          <role-list 
            :roles="roleListData"
            :permissions="permissionListData"
            @reload-role-list="reloadRoleListData"
          />
        </div>
        <!-- 个人信息 -->
        <div :class="['content-tab', { 'active': activeTag === 'profile-edit' }]" id="profile-edit-tab">
          <admin-profile-edit 
            :admin="currentAdmin"
            :submit-url="adminProfileUrl"
            :is-create-mode="false"
          />
        </div> 
        <!-- 一级分类 -->
        <div :class="['content-tab', { 'active': activeTag === 'menu-category-list' }]" id="menu-category-list-tab">
          <menu-category-tab :admin-login-path="adminLoginPath" ref="menuCategoryTab" :auto-load="false" />
        </div>
        
        <!-- 二级分类 -->
        <div :class="['content-tab', { 'active': activeTag === 'menu-subcategory-list' }]" id="menu-subcategory-list-tab">
          <menu-subcategory-tab :admin-login-path="adminLoginPath" ref="menuSubcategoryTab" :auto-load="false" />
        </div>
        
        <!-- 三级分类 -->
        <div :class="['content-tab', { 'active': activeTag === 'menu-item-list' }]" id="menu-item-list-tab">
          <menu-item-tab :admin-login-path="adminLoginPath" ref="menuItemTab" :auto-load="false" />
        </div>
        
        <!-- 促销菜单 -->
        <div :class="['content-tab', { 'active': activeTag === 'menu-promotion-list' }]" id="menu-promotion-list-tab">
          <promotion-menu-tab :admin-login-path="adminLoginPath" ref="promotionMenuTab" :auto-load="false" />
        </div>
        
        <!-- 网站参数 -->
        <div :class="['content-tab', { 'active': activeTag === 'site-config-list' }]" id="site-config-list-tab">
          <site-config-manager :admin-login-path="adminLoginPath" ref="siteConfigManager" :auto-load="false" />
        </div>
        
        <!-- 供应商列表 -->
        <div :class="['content-tab', { 'active': activeTag === 'supplier-list' }]" id="supplier-list-tab">
          <supplier-list :admin-login-path="adminLoginPath" ref="supplierList" :auto-load="false" />
        </div>
        
        <!-- 添加供应商 -->
        <div :class="['content-tab', { 'active': activeTag === 'supplier-create' }]" id="supplier-create-tab">
          <supplier-create :admin-login-path="adminLoginPath" @supplier-created="reloadSupplierList" />
        </div>
        
        <!-- 查看供应商 -->
        <div :class="['content-tab', { 'active': activeTag === 'supplier-view' }]" id="supplier-view-tab">
          <supplier-view-tab :admin-login-path="adminLoginPath" :supplier-id="currentSupplierId" ref="supplierViewTab" :auto-load="false" />
        </div>
        
        <!-- 编辑供应商 -->
        <div :class="['content-tab', { 'active': activeTag === 'supplier-edit' }]" id="supplier-edit-tab">
          <supplier-edit-tab :admin-login-path="adminLoginPath" :supplier-id="currentSupplierId" ref="supplierEditTab" :auto-load="false" />
        </div>
        
        <!-- 商品列表 -->
        <div :class="['content-tab', { 'active': activeTag === 'product-list' }]" id="product-list-tab">
          <product-list :admin-login-path="adminLoginPath" ref="productList" :auto-load="false" />
        </div>
        
        <!-- 查看商品 -->
        <div :class="['content-tab', { 'active': activeTag === 'product-view' }]" id="product-view-tab">
          <product-view :admin-login-path="adminLoginPath" :product-id="currentProductId" ref="productView" :auto-load="false" />
        </div>
        
        <!-- 会员列表 -->
        <div :class="['content-tab', { 'active': activeTag === 'member-list' }]" id="member-list-tab">
          <member-list :admin-login-path="adminLoginPath" ref="memberList" :auto-load="false" />
        </div>
        
        <!-- 添加会员 -->
        <div :class="['content-tab', { 'active': activeTag === 'member-create' }]" id="member-create-tab">
          <member-create :admin-login-path="adminLoginPath" @member-created="reloadMemberList" />
        </div>
        
        <!-- 物流公司 -->
        <div :class="['content-tab', { 'active': activeTag === 'logistics-company-list' }]" id="logistics-company-list-tab">
          <logistics-company-list :admin-login-path="adminLoginPath" ref="logisticsCompanyList" :auto-load="false" />
        </div>
        
        <!-- 添加物流公司 -->
        <div :class="['content-tab', { 'active': activeTag === 'logistics-company-create' }]" id="logistics-company-create-tab">
          <logistics-company-create :admin-login-path="adminLoginPath" @logistics-company-created="reloadLogisticsCompanyList" />
        </div>
        
        <!-- 编辑物流公司 -->
        <div :class="['content-tab', { 'active': activeTag === 'logistics-company-edit' }]" id="logistics-company-edit-tab">
          <logistics-company-edit :admin-login-path="adminLoginPath" :logistics-company-id="currentLogisticsCompanyId" ref="logisticsCompanyEdit" :auto-load="false" />
        </div>
        
        <!-- 余额变化记录 -->
        <div :class="['content-tab', { 'active': activeTag === 'balance-history-list' }]" id="balance-history-list-tab">
          <balance-history-list :admin-login-path="adminLoginPath" ref="balanceHistoryList" :auto-load="false" />
        </div>
        
        <!-- 提现申请 -->
        <div :class="['content-tab', { 'active': activeTag === 'withdrawal-list' }]" id="withdrawal-list-tab">
          <withdrawal-list :admin-login-path="adminLoginPath" ref="withdrawalList" :auto-load="false" />
        </div>
      </el-main>
    </el-container>
  </el-container>
</template>

<script>
import AdminSidebar from './AdminSidebar.vue'
import Dashboard from './Dashboard.vue'
import AdminList from './AdminList.vue'
import RoleList from './RoleList.vue'
import AdminProfileEdit from './AdminProfileEdit.vue'
import MenuCategoryTab from './MenuCategoryTab.vue'
import MenuSubcategoryTab from './MenuSubcategoryTab.vue'
import MenuItemTab from './MenuItemTab.vue'
import PromotionMenuTab from './PromotionMenuTab.vue'
import SupplierList from './SupplierList.vue'
import SupplierCreate from './SupplierCreate.vue'
import ProductList from './ProductList.vue'
import ProductView from './ProductView.vue'
import SupplierViewTab from './SupplierViewTab.vue'
import SupplierEditTab from './SupplierEditTab.vue'
import MemberList from './MemberList.vue'
import MemberCreate from './MemberCreate.vue'
import SiteConfigManager from './SiteConfigManager.vue'
import BalanceHistoryList from './BalanceHistoryList.vue'
import WithdrawalList from './WithdrawalList.vue'
import LogisticsCompanyList from './LogisticsCompanyList.vue'
import LogisticsCompanyCreate from './LogisticsCompanyCreate.vue'
import LogisticsCompanyEdit from './LogisticsCompanyEdit.vue'
import {
  ElContainer,
  ElHeader,
  ElMain,
  ElTag,
  ElButton
} from 'element-plus'

export default {
  name: 'AdminLayout',
  components: {
    AdminSidebar,
    Dashboard,
    AdminList,
    RoleList,
    AdminProfileEdit,
    MenuCategoryTab,
    MenuSubcategoryTab,
    MenuItemTab,
    PromotionMenuTab,
    SupplierList,
    SupplierCreate,
    ProductList,
    ProductView,
    SupplierViewTab,
    SupplierEditTab,
    MemberList,
    MemberCreate,
    SiteConfigManager,
    BalanceHistoryList,
    WithdrawalList,
    LogisticsCompanyList,
    LogisticsCompanyCreate,
    LogisticsCompanyEdit,
    ElContainer,
    ElHeader,
    ElMain,
    ElTag,
    ElButton
  },
  props: {
    currentAdmin: {
      type: Object,
      default: () => ({
        username: '',
        email: '',
        mark: ''
      })
    },
    adminLoginPath: {
      type: String,
      default: ''
    },
    adminProfileUrl: {
      type: String,
      default: ''
    },
    adminCreateUrl: {
      type: String,
      default: ''
    }
  },
  data() {
    return {
      headerTitle: '总后台',
      activeTag: 'dashboard',
      userName: '', // 默认为空
      isCurrentUserSuperAdmin: false, // 是否为超级管理员
      tags: [
        { key: 'dashboard', title: '主页' }
      ],
      tagTitles: {
        'dashboard': '主页',
        'user-list': '管理员列表',
        'user-create': '添加管理员',
        'user-roles': '角色管理',
        'profile-edit': '个人信息',
        'supplier-list': '供应商列表',
        'supplier-create': '添加供应商',
        'supplier-view': '查看供应商',
        'supplier-edit': '编辑供应商',
        'product-list': '商品列表',
        'product-view': '查看商品',
        'member-list': '会员列表',
        'member-create': '添加会员',
        'menu-category-list': '一级分类',
        'menu-subcategory-list': '二级分类',
        'menu-item-list': '三级分类',
        'menu-promotion-list': '促销菜单',
        'site-config-list': '网站参数',
        'logistics-company-list': '物流公司',
        'logistics-company-create': '添加物流公司',
        'logistics-company-edit': '编辑物流公司',
        'balance-history-list': '余额变化记录',
        'withdrawal-list': '提现申请'
      },
      adminListData: [], // 添加管理员列表数据
      adminRoleListData: [], // 添加管理员角色列表数据
      roleListData: [], // 添加角色列表数据
      permissionListData: [], // 添加权限列表数据
      withdrawalList: null, // 提现申请列表引用
      currentProductId: null, // 当前查看的商品ID
      currentSupplierId: null, // 当前查看/编辑的供应商ID
      currentLogisticsCompanyId: null // 当前编辑的物流公司ID
    }
  },
  methods: {
    handleNavigation(key, keyPath) {
      console.log('Navigation triggered with key:', key); // 调试信息
      // 添加标签页
      this.addTab(key);
      
      // 切换到该标签页
      this.switchToTab(key);
      
      // 根据标签页类型加载相应数据
      this.loadTabData(key);
    },
    
    addTab(key) {
      console.log('Adding tab with key:', key); // 调试信息
      // 如果标签页已存在，直接返回
      if (this.tags.some(tag => tag.key === key)) {
        console.log('Tab already exists:', key); // 调试信息
        return;
      }
      
      // 添加新标签页
      this.tags.push({
        key: key,
        title: this.tagTitles[key] || key
      });
      
      console.log('Added new tab:', key, 'Updated tags:', this.tags); // 调试信息
      
      // 强制更新以确保视图刷新
      this.$forceUpdate();
    },
    
    // 根据标签页类型加载数据就是按需加载
    loadTabData(key) {
      switch (key) {
        case 'user-list':
          this.fetchAdminListData();
          break;
        case 'user-roles':
          this.fetchRoleListData();
          break;
        case 'menu-category-list':
          // 按需加载一级分类数据
          if (this.$refs.menuCategoryTab) {
            this.$refs.menuCategoryTab.loadCategoryData();
          }
          break;
        case 'menu-subcategory-list':
          // 按需加载二级分类数据
          if (this.$refs.menuSubcategoryTab) {
            this.$refs.menuSubcategoryTab.loadSubcategoryData();
          }
          break;
        case 'menu-item-list':
          // 按需加载三级分类数据
          if (this.$refs.menuItemTab) {
            this.$refs.menuItemTab.loadItemData();
          }
          break;
        case 'menu-promotion-list':
          // 按需加载促销菜单数据
          if (this.$refs.promotionMenuTab) {
            this.$refs.promotionMenuTab.loadPromotionData();
          }
          break;
        case 'site-config-list':
          // 按需加载网站参数数据
          if (this.$refs.siteConfigManager) {
            this.$refs.siteConfigManager.loadConfigData();
          }
          break;
        case 'logistics-company-list':
          // 按需加载物流公司列表数据
          if (this.$refs.logisticsCompanyList) {
            this.$refs.logisticsCompanyList.loadLogisticsCompanyData();
          }
          break;
        case 'logistics-company-edit':
          // 按需加载物流公司编辑数据
          if (this.$refs.logisticsCompanyEdit) {
            this.$refs.logisticsCompanyEdit.initData();
          }
          break;
        case 'supplier-list':
          // 按需加载供应商列表数据
          if (this.$refs.supplierList) {
            this.$refs.supplierList.loadSupplierData();
          }
          break;
        case 'member-list':
          // 按需加载会员列表数据
          if (this.$refs.memberList) {
            this.$refs.memberList.loadMemberData();
          }
          break;
        case 'balance-history-list':
          // 按需加载余额变化记录数据
          if (this.$refs.balanceHistoryList) {
            this.$refs.balanceHistoryList.loadBalanceHistoryData();
          }
          break;
        case 'withdrawal-list':
          // 按需加载提现申请数据
          if (this.$refs.withdrawalList) {
            this.$refs.withdrawalList.loadWithdrawalData();
          }
          break;
        case 'withdrawal-list':
          // 按需加载提现申请数据
          if (this.$refs.withdrawalList) {
            this.$refs.withdrawalList.loadWithdrawalData();
          }
          break;
        case 'product-list':
          // 按需加载商品列表数据
          if (this.$refs.productList) {
            this.$refs.productList.loadProductData();
            this.$refs.productList.loadStatistics();
          }
          break;
        case 'product-view':
          // 按需加载商品查看数据
          if (this.$refs.productView) {
            this.$refs.productView.initData();
          }
          break;
        case 'supplier-view':
          // 按需加载供应商查看数据
          if (this.$refs.supplierViewTab) {
            this.$refs.supplierViewTab.initData();
          }
          break;
        case 'supplier-edit':
          // 按需加载供应商编辑数据
          if (this.$refs.supplierEditTab) {
            this.$refs.supplierEditTab.initData();
          }
          break;
        // 可以在这里添加其他标签页的数据加载逻辑
        default:
          // 其他标签页不需要预加载数据
          break;
      }
    },
    
    // 获取管理员列表数据
    fetchAdminListData() {
      console.log('Fetching admin list data'); // 调试信息
      
      // 构造请求URL
      const url = `/admin${this.adminLoginPath}/user/list/tab`;
      
      // 发送AJAX请求获取数据
      fetch(url, {
        method: 'GET',
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      })
      .then(response => {
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
      })
      .then(data => {
        if (data.admins) {
          this.adminListData = data.admins;
          console.log('Admin list data updated:', this.adminListData);
        }
        if (data.roles) {
          this.adminRoleListData = data.roles;
          console.log('Admin role list data updated:', this.adminRoleListData);
        }
      })
      .catch(error => {
        console.error('Error fetching admin list data:', error);
      });
    },
    
    // 获取角色列表数据
    fetchRoleListData() {
      console.log('Fetching role list data'); // 调试信息
      
      // 构造请求URL
      const url = `/admin${this.adminLoginPath}/role/list/tab`;
      
      // 发送AJAX请求获取数据
      fetch(url, {
        method: 'GET',
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      })
      .then(response => {
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
      })
      .then(data => {
        if (data.roles) {
          this.roleListData = data.roles;
          console.log('Role list data updated:', this.roleListData);
        }
        if (data.permissions) {
          this.permissionListData = data.permissions;
          console.log('Permission list data updated:', this.permissionListData);
        }
      })
      .catch(error => {
        console.error('Error fetching role list data:', error);
      });
    },
    
    // 重新加载管理员列表数据（供子组件调用）
    reloadAdminListData() {
      this.fetchAdminListData();
    },
    
    // 重新加载角色列表数据（供子组件调用）
    reloadRoleListData() {
      this.fetchRoleListData();
    },
    
    // 重新加载供应商列表（供子组件调用）
    reloadSupplierList() {
      // 这里可以添加重新加载供应商列表的逻辑
      console.log('Supplier created, need to reload supplier list');
    },
    
    // 重新加载会员列表（供子组件调用）
    reloadMemberList() {
      // 这里可以添加重新加载会员列表的逻辑
      console.log('Member created, need to reload member list');
    },
    
    // 重新加载物流公司列表（供子组件调用）
    reloadLogisticsCompanyList() {
      // 这里可以添加重新加载物流公司列表的逻辑
      console.log('Logistics company created, need to reload logistics company list');
      if (this.$refs.logisticsCompanyList) {
        this.$refs.logisticsCompanyList.loadLogisticsCompanyData();
      }
    },
    
    switchToTab(key) {
      console.log('Switching to tab:', key); // 调试信息
      this.activeTag = key;
      console.log('Active tab is now:', this.activeTag); // 调试信息
      
      // 强制更新以确保视图刷新
      this.$forceUpdate();
    },
    
    closeTab(key) {
      // 不能关闭主页标签
      if (key === 'dashboard') {
        return;
      }
      
      // 找到要关闭的标签索引
      const index = this.tags.findIndex(tag => tag.key === key);
      if (index === -1) {
        return;
      }
      
      // 删除标签
      this.tags.splice(index, 1);
      
      // 如果关闭的是当前激活的标签，则切换到主页
      if (this.activeTag === key) {
        this.activeTag = 'dashboard';
      }
      
      // 强制更新以确保视图刷新
      this.$forceUpdate();
    },
    
    logout() {
      // 实现退出登录逻辑
      window.location.href = '/logout'; // 需要根据实际的退出登录路由调整
    },
    
    editProfile() {
      // 添加个人信息标签页并切换到该标签页
      this.addTab('profile-edit');
      this.switchToTab('profile-edit');
    },
    
    // 查看商品详情
    viewProduct(productId) {
      // 设置当前查看的商品ID
      this.currentProductId = productId;
      
      // 添加商品查看标签页并切换到该标签页
      this.addTab('product-view');
      this.switchToTab('product-view');
      
      // 按需加载商品数据
      this.$nextTick(() => {
        this.loadTabData('product-view');
      });
    },
    
    // 查看供应商详情
    viewSupplier(supplierId) {
      // 设置当前查看的供应商ID
      this.currentSupplierId = supplierId;
      
      // 添加供应商查看标签页并切换到该标签页
      this.addTab('supplier-view');
      this.switchToTab('supplier-view');
      
      // 按需加载供应商数据
      this.$nextTick(() => {
        this.loadTabData('supplier-view');
      });
    },
    
    // 编辑供应商
    editSupplier(supplierId) {
      // 设置当前编辑的供应商ID
      this.currentSupplierId = supplierId;
      
      // 添加供应商编辑标签页并切换到该标签页
      this.addTab('supplier-edit');
      this.switchToTab('supplier-edit');
      
      // 按需加载供应商数据
      this.$nextTick(() => {
        this.loadTabData('supplier-edit');
      });
    },
    
    // 编辑物流公司
    editLogisticsCompany(companyId) {
      // 设置当前编辑的物流公司ID
      this.currentLogisticsCompanyId = companyId;
      
      // 添加物流公司编辑标签页并切换到该标签页
      this.addTab('logistics-company-edit');
      this.switchToTab('logistics-company-edit');
      
      // 按需加载物流公司数据
      this.$nextTick(() => {
        this.loadTabData('logistics-company-edit');
      });
    }
  },
  mounted() {
    // 从props中获取当前管理员信息
    console.log('Current admin data from props:', this.currentAdmin); // 调试信息
    
    // 设置显示的用户名，明确使用username字段
    this.userName = this.currentAdmin.username || '';
    
    // 如果仍然没有用户名，则使用默认值
    if (!this.userName) {
      this.userName = '管理员';
    }
    
    // 检查当前用户是否为超级管理员
    if (this.currentAdmin && this.currentAdmin.roles) {
      this.isCurrentUserSuperAdmin = this.currentAdmin.roles.includes('ROLE_SUPER_ADMIN');
    }
    
    // 设置全局adminLoginPath变量
    if (this.adminLoginPath) {
      window.adminLoginPath = this.adminLoginPath;
    }
    
    console.log('Final username:', this.userName); // 调试信息
    console.log('Admin login path:', this.adminLoginPath); // 调试信息
    console.log('Is current user super admin:', this.isCurrentUserSuperAdmin); // 调试信息
    
    // 监听查看商品详情事件
    window.addEventListener('navigate-to-product-view', (event) => {
      const productId = event.detail.productId;
      this.viewProduct(productId);
    });
    
    // 监听查看供应商详情事件
    window.addEventListener('navigate-to-supplier-view', (event) => {
      const supplierId = event.detail.supplierId;
      this.viewSupplier(supplierId);
    });
    
    // 监听编辑供应商事件
    window.addEventListener('navigate-to-supplier-edit', (event) => {
      const supplierId = event.detail.supplierId;
      this.editSupplier(supplierId);
    });
    
    // 监听供应商保存成功事件
    window.addEventListener('supplier-save-success', () => {
      // 刷新供应商列表
      if (this.$refs.supplierList) {
        this.$refs.supplierList.loadSupplierData();
      }
      // 关闭编辑标签页
      this.closeTab('supplier-edit');
      // 切换到供应商列表标签页
      this.switchToTab('supplier-list');
    });
    
    // 监听编辑物流公司事件
    window.addEventListener('navigate-to-logistics-company-edit', (event) => {
      const companyId = event.detail.companyId;
      this.editLogisticsCompany(companyId);
    });
    
    // 监听关闭当前标签页事件
    window.addEventListener('close-current-tab', () => {
      // 关闭当前激活的标签页
      this.closeTab(this.activeTag);
    });
    
    // 监听导航到指定页面事件
    window.addEventListener('navigate-to', (event) => {
      const key = event.detail.key;
      // 切换到指定标签页
      this.switchToTab(key);
    });
  }
}
</script>

<style scoped>
.admin-layout {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  width: 100vw;
  margin: 0;
  padding: 0;
  overflow: hidden;
}

.admin-header {
  background-color: #2c3e50;
  color: white;
  padding: 0 20px;
  height: 60px;
  line-height: 60px;
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 1000;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.logo h1 {
  margin: 0;
  font-size: 20px;
  font-weight: normal;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 15px;
  cursor: pointer; /* 手型光标 */
}

.user-info span {
  font-size: 14px;
  text-decoration: underline; /* 下划线 */
}

.user-info:hover span {
  color: #1abc9c; /* 悬停时的颜色变化 */
}

.content-container {
  flex: 1;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  margin-left: 200px;
  margin-top: 60px;
  height: calc(100vh - 60px);
}

.tags-view {
  background: #f5f5f5;
  border-bottom: 1px solid #ddd;
  padding: 0.5rem 1rem;
  font-size: 14px;
  flex-shrink: 0;
  position: fixed;
  top: 60px;
  left: 200px;
  right: 0;
  height: 40px;
  z-index: 999;
  overflow-x: auto;
  display: flex;
  align-items: center;
}

.tag-item {
  margin-right: 0.5rem;
  cursor: pointer;
}

.content-wrapper {
  flex: 1;
  overflow-y: auto;
  padding: 1rem;
  background: #ecf0f1;
  margin-top: 40px;
  height: calc(100vh - 100px);
}

.content-tab {
  display: none;
}

.content-tab.active {
  display: block;
}

/* 移动端适配 */
@media (max-width: 768px) {
  .content-container {
    margin-left: 60px;
  }
  
  .tags-view {
    left: 60px;
  }
}
</style>