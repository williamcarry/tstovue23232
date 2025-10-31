<template>
  <div class="menu-item-tab-container" v-loading="loading">
    <div v-if="error" class="error-message">
      {{ error }}
    </div>
    <div v-else>
      <menu-item-list 
        :menu-items="menuItems"
        :menu-subcategories="menuSubcategories"
        :current-page="currentPage"
        :total-pages="totalPages"
        :total-items="totalItems"
        :admin-login-path="adminLoginPath"
        @page-change="handlePageChange"
      />
    </div>
  </div>
</template>

<script>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import MenuItemList from './MenuItemList.vue'
import {
  ElLoading
} from 'element-plus'

export default {
  name: 'MenuItemTab',
  components: {
    MenuItemList,
    ElLoading
  },
  props: {
    adminLoginPath: {
      type: String,
      default: ''
    },
    // 添加一个属性来控制是否自动加载数据
    autoLoad: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      loading: false,
      error: null,
      menuItems: [],
      menuSubcategories: [],
      currentPage: 1,
      totalPages: 1,
      totalItems: 0
    }
  },
  methods: {
    async loadItemData(page = 1) {
      this.loading = true
      this.error = null
      
      try {
        // 使用从父组件传递过来的adminLoginPath
        const loginPath = this.adminLoginPath || window.adminLoginPath || '';
        
        // 获取三级分类数据
        const itemResponse = await fetch(`/admin${loginPath}/menu/item/list/data?page=${page}`, {
          method: 'GET',
          headers: {
            'Content-Type': 'application/json',
          }
        })
        
        const itemResult = await itemResponse.json()
        
        // 获取二级分类数据（用于选择框）
        const subcategoryResponse = await fetch(`/admin${loginPath}/menu/subcategory/list/data?page=1`, {
          method: 'GET',
          headers: {
            'Content-Type': 'application/json',
          }
        })
        
        const subcategoryResult = await subcategoryResponse.json()
        
        if (itemResult.success && subcategoryResult.success) {
          this.menuItems = itemResult.data.menuItems
          this.menuSubcategories = subcategoryResult.data.menuSubcategories
          this.currentPage = itemResult.data.currentPage
          this.totalPages = itemResult.data.totalPages
          this.totalItems = itemResult.data.totalItems
        } else {
          this.error = itemResult.message || subcategoryResult.message || '加载数据失败'
        }
      } catch (err) {
        this.error = '网络错误: ' + err.message
      } finally {
        this.loading = false
      }
    },
    
    handlePageChange(page) {
      this.loadItemData(page)
    },
    
    handleItemUpdated() {
      // 当分类更新时，重新加载当前页面的数据
      this.loadItemData(this.currentPage)
    },
    
    handlePageChanged(event) {
      // 当页面更改时，加载指定页面的数据
      this.loadItemData(event.detail.page)
    }
  },
  mounted() {
    // 只有当 autoLoad 为 true 时才自动加载数据
    if (this.autoLoad) {
      this.loadItemData()
    }
    
    // 监听自定义事件
    window.addEventListener('menu-item-updated', this.handleItemUpdated)
    window.addEventListener('menu-item-page-changed', this.handlePageChanged)
  },
  beforeUnmount() {
    // 清理事件监听器
    window.removeEventListener('menu-item-updated', this.handleItemUpdated)
    window.removeEventListener('menu-item-page-changed', this.handlePageChanged)
  }
}
</script>

<style scoped>
.menu-item-tab-container {
  padding: 0;
  background-color: #f5f7fa;
}

.error-message {
  color: #f56c6c;
  text-align: center;
  padding: 20px;
}
</style>