<template>
  <div class="menu-category-tab-container" v-loading="loading">
    <div v-if="error" class="error-message">
      {{ error }}
    </div>
    <div v-else>
      <menu-category-list 
        :menu-categories="menuCategories"
        :current-page="currentPage"
        :total-pages="totalPages"
        :total-categories="totalCategories"
        :admin-login-path="adminLoginPath"
        @page-change="handlePageChange"
      />
    </div>
  </div>
</template>

<script>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import MenuCategoryList from './MenuCategoryList.vue'
import {
  ElLoading
} from 'element-plus'

export default {
  name: 'MenuCategoryTab',
  components: {
    MenuCategoryList,
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
      menuCategories: [],
      currentPage: 1,
      totalPages: 1,
      totalCategories: 0
    }
  },
  methods: {
    async loadCategoryData(page = 1) {
      this.loading = true
      this.error = null
      
      try {
        // 使用从父组件传递过来的adminLoginPath
        const loginPath = this.adminLoginPath || window.adminLoginPath || '';
        const response = await fetch(`/admin${loginPath}/menu/category/list/data?page=${page}`, {
          method: 'GET',
          headers: {
            'Content-Type': 'application/json',
          }
        })
        
        const result = await response.json()
        
        if (result.success) {
          this.menuCategories = result.data.menuCategories
          this.currentPage = result.data.currentPage
          this.totalPages = result.data.totalPages
          this.totalCategories = result.data.totalCategories
        } else {
          this.error = result.message || '加载数据失败'
        }
      } catch (err) {
        this.error = '网络错误: ' + err.message
      } finally {
        this.loading = false
      }
    },
    
    handlePageChange(page) {
      this.loadCategoryData(page)
    },
    
    handleCategoryUpdated() {
      // 当分类更新时，重新加载当前页面的数据
      this.loadCategoryData(this.currentPage)
    },
    
    handlePageChanged(event) {
      // 当页面更改时，加载指定页面的数据
      this.loadCategoryData(event.detail.page)
    }
  },
  mounted() {
    // 只有当 autoLoad 为 true 时才自动加载数据
    if (this.autoLoad) {
      this.loadCategoryData()
    }
    
    // 监听自定义事件
    window.addEventListener('menu-category-updated', this.handleCategoryUpdated)
    window.addEventListener('menu-category-page-changed', this.handlePageChanged)
  },
  beforeUnmount() {
    // 清理事件监听器
    window.removeEventListener('menu-category-updated', this.handleCategoryUpdated)
    window.removeEventListener('menu-category-page-changed', this.handlePageChanged)
  }
}
</script>

<style scoped>
.menu-category-tab-container {
  padding: 0;
  background-color: #f5f7fa;
}

.error-message {
  color: #f56c6c;
  text-align: center;
  padding: 20px;
}
</style>