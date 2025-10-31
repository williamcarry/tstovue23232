<template>
  <div class="promotion-menu-tab-container" v-loading="loading">
    <div v-if="error" class="error-message">
      {{ error }}
    </div>
    <div v-else>
      <promotion-menu-list 
        :promotion-menus="promotionMenus"
        :menu-categories="menuCategories"
        :current-page="currentPage"
        :total-pages="totalPages"
        :total-menus="totalMenus"
        :admin-login-path="adminLoginPath"
        @page-change="handlePageChange"
      />
    </div>
  </div>
</template>

<script>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import PromotionMenuList from './PromotionMenuList.vue'
import {
  ElLoading
} from 'element-plus'

export default {
  name: 'PromotionMenuTab',
  components: {
    PromotionMenuList,
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
      promotionMenus: [],
      menuCategories: [],
      currentPage: 1,
      totalPages: 1,
      totalMenus: 0
    }
  },
  methods: {
    async loadPromotionData(page = 1) {
      this.loading = true
      this.error = null
      
      try {
        // 使用从父组件传递过来的adminLoginPath
        const loginPath = this.adminLoginPath || window.adminLoginPath || '';
        
        // 获取促销菜单数据
        const promotionResponse = await fetch(`/admin${loginPath}/menu/promotion/list/data?page=${page}`, {
          method: 'GET',
          headers: {
            'Content-Type': 'application/json',
          }
        })
        
        const promotionResult = await promotionResponse.json()
        
        // 获取一级分类数据（用于选择框）
        const categoryResponse = await fetch(`/admin${loginPath}/menu/category/list/data?page=1`, {
          method: 'GET',
          headers: {
            'Content-Type': 'application/json',
          }
        })
        
        const categoryResult = await categoryResponse.json()
        
        if (promotionResult.success && categoryResult.success) {
          this.promotionMenus = promotionResult.data.promotionMenus
          this.menuCategories = categoryResult.data.menuCategories
          this.currentPage = promotionResult.data.currentPage
          this.totalPages = promotionResult.data.totalPages
          this.totalMenus = promotionResult.data.totalMenus
        } else {
          this.error = promotionResult.message || categoryResult.message || '加载数据失败'
        }
      } catch (err) {
        this.error = '网络错误: ' + err.message
      } finally {
        this.loading = false
      }
    },
    
    handlePageChange(page) {
      this.loadPromotionData(page)
    },
    
    handlePromotionUpdated() {
      // 当分类更新时，重新加载当前页面的数据
      this.loadPromotionData(this.currentPage)
    },
    
    handlePageChanged(event) {
      // 当页面更改时，加载指定页面的数据
      this.loadPromotionData(event.detail.page)
    }
  },
  mounted() {
    // 只有当 autoLoad 为 true 时才自动加载数据
    if (this.autoLoad) {
      this.loadPromotionData()
    }
    
    // 监听自定义事件
    window.addEventListener('menu-promotion-updated', this.handlePromotionUpdated)
    window.addEventListener('menu-promotion-page-changed', this.handlePageChanged)
  },
  beforeUnmount() {
    // 清理事件监听器
    window.removeEventListener('menu-promotion-updated', this.handlePromotionUpdated)
    window.removeEventListener('menu-promotion-page-changed', this.handlePageChanged)
  }
}
</script>

<style scoped>
.promotion-menu-tab-container {
  padding: 0;
  background-color: #f5f7fa;
}

.error-message {
  color: #f56c6c;
  text-align: center;
  padding: 20px;
}
</style>