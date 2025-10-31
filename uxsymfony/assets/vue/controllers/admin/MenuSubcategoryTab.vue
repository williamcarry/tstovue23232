<template>
  <div class="menu-subcategory-tab-container" v-loading="loading">
    <div v-if="error" class="error-message">
      {{ error }}
    </div>
    <div v-else>
      <menu-subcategory-list 
        :menu-subcategories="menuSubcategories"
        :menu-categories="menuCategories"
        :current-page="currentPage"
        :total-pages="totalPages"
        :total-subcategories="totalSubcategories"
        :admin-login-path="adminLoginPath"
        @page-change="handlePageChange"
      />
    </div>
  </div>
</template>

<script>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import MenuSubcategoryList from './MenuSubcategoryList.vue'
import {
  ElLoading
} from 'element-plus'

export default {
  name: 'MenuSubcategoryTab',
  components: {
    MenuSubcategoryList,
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
      menuSubcategories: [],
      menuCategories: [],
      currentPage: 1,
      totalPages: 1,
      totalSubcategories: 0
    }
  },
  methods: {
    async loadSubcategoryData(page = 1) {
      this.loading = true
      this.error = null
      
      try {
        // 使用从父组件传递过来的adminLoginPath
        const loginPath = this.adminLoginPath || window.adminLoginPath || '';
        
        // 获取二级分类数据
        const subcategoryResponse = await fetch(`/admin${loginPath}/menu/subcategory/list/data?page=${page}`, {
          method: 'GET',
          headers: {
            'Content-Type': 'application/json',
          }
        })
        
        const subcategoryResult = await subcategoryResponse.json()
        
        // 获取一级分类数据（用于选择框）
        const categoryResponse = await fetch(`/admin${loginPath}/menu/category/list/data?page=1`, {
          method: 'GET',
          headers: {
            'Content-Type': 'application/json',
          }
        })
        
        const categoryResult = await categoryResponse.json()
        
        if (subcategoryResult.success && categoryResult.success) {
          this.menuSubcategories = subcategoryResult.data.menuSubcategories
          this.menuCategories = categoryResult.data.menuCategories
          this.currentPage = subcategoryResult.data.currentPage
          this.totalPages = subcategoryResult.data.totalPages
          this.totalSubcategories = subcategoryResult.data.totalSubcategories
        } else {
          this.error = subcategoryResult.message || categoryResult.message || '加载数据失败'
        }
      } catch (err) {
        this.error = '网络错误: ' + err.message
      } finally {
        this.loading = false
      }
    },
    
    handlePageChange(page) {
      this.loadSubcategoryData(page)
    },
    
    handleSubcategoryUpdated() {
      // 当分类更新时，重新加载当前页面的数据
      this.loadSubcategoryData(this.currentPage)
    },
    
    handlePageChanged(event) {
      // 当页面更改时，加载指定页面的数据
      this.loadSubcategoryData(event.detail.page)
    }
  },
  mounted() {
    // 只有当 autoLoad 为 true 时才自动加载数据
    if (this.autoLoad) {
      this.loadSubcategoryData()
    }
    
    // 监听自定义事件
    window.addEventListener('menu-subcategory-updated', this.handleSubcategoryUpdated)
    window.addEventListener('menu-subcategory-page-changed', this.handlePageChanged)
  },
  beforeUnmount() {
    // 清理事件监听器
    window.removeEventListener('menu-subcategory-updated', this.handleSubcategoryUpdated)
    window.removeEventListener('menu-subcategory-page-changed', this.handlePageChanged)
  }
}
</script>

<style scoped>
.menu-subcategory-tab-container {
  padding: 0;
  background-color: #f5f7fa;
}

.error-message {
  color: #f56c6c;
  text-align: center;
  padding: 20px;
}
</style>