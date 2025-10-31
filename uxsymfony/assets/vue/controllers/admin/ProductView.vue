<template>
  <div class="product-view" v-loading="loading">
    <el-card class="box-card">
      <template #header>
        <div class="card-header">
          <span>查看商品</span>
        </div>
      </template>
      
      <el-form :model="formData" label-width="120px" :disabled="true">
        <!-- 基本信息 -->
        <el-divider content-position="left">基本信息</el-divider>
        
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="SKU">
              <el-input v-model="formData.sku" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="SPU">
              <el-input v-model="formData.spu" />
            </el-form-item>
          </el-col>
        </el-row>
        
        <el-form-item label="商品名称">
          <el-input v-model="formData.title" />
        </el-form-item>
        
        <el-form-item label="英文名称">
          <el-input v-model="formData.titleEn" />
        </el-form-item>
        
        <el-form-item label="商品主图">
          <div class="image-preview-list">
            <div v-for="(image, index) in mainImages" :key="`main-image-${index}`" class="image-item">
              <el-image
                :src="image.url"
                fit="cover"
                style="width: 178px; height: 178px; border-radius: 6px"
                :preview-src-list="mainImageUrls"
                preview-teleported
                @click="handleImagePreview(index)"
              >
                <template #error>
                  <div class="image-slot">
                    <el-icon><Picture /></el-icon>
                  </div>
                </template>
              </el-image>
            </div>
          </div>
        </el-form-item>
        
        <!-- 分类信息 -->
        <el-divider content-position="left">分类信息</el-divider>
        
        <el-row :gutter="20">
          <el-col :span="8">
            <el-form-item label="一级分类">
              <el-select v-model="formData.categoryId" placeholder="请选择分类">
                <el-option
                  v-for="category in categories"
                  :key="category.id"
                  :label="category.name"
                  :value="category.id"
                />
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :span="8">
            <el-form-item label="二级分类">
              <el-select v-model="formData.subcategoryId" placeholder="请选择子分类" :disabled="!formData.categoryId">
                <el-option
                  v-for="subcategory in subcategories"
                  :key="subcategory.id"
                  :label="subcategory.name"
                  :value="subcategory.id"
                />
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :span="8">
            <el-form-item label="三级分类">
              <el-select v-model="formData.itemId" placeholder="请选择商品项" :disabled="!formData.subcategoryId">
                <el-option
                  v-for="item in items"
                  :key="item.id"
                  :label="item.name"
                  :value="item.id"
                />
              </el-select>
            </el-form-item>
          </el-col>
        </el-row>
        
        <!-- 商品属性 -->
        <el-divider content-position="left">商品属性</el-divider>
        
        <el-form-item label="品牌">
          <el-input v-model="formData.brand" />
        </el-form-item>
        
        <el-row :gutter="20">
          <el-col :span="6">
            <el-form-item label="长度(cm)">
              <el-input :value="formatToTwoDecimals(formData.length)" />
            </el-form-item>
          </el-col>
          <el-col :span="6">
            <el-form-item label="宽度(cm)">
              <el-input :value="formatToTwoDecimals(formData.width)" />
            </el-form-item>
          </el-col>
          <el-col :span="6">
            <el-form-item label="高度(cm)">
              <el-input :value="formatToTwoDecimals(formData.height)" />
            </el-form-item>
          </el-col>
          <el-col :span="6">
            <el-form-item label="重量(g)">
              <el-input :value="formatToTwoDecimals(formData.weight)" />
            </el-form-item>
          </el-col>
        </el-row>
        
        <!-- 包装信息 -->
        <el-divider content-position="left">包装信息</el-divider>
        
        <el-row :gutter="20">
          <el-col :span="6">
            <el-form-item label="包装长度(cm)">
              <el-input :value="formatToTwoDecimals(formData.packageLength)" />
            </el-form-item>
          </el-col>
          <el-col :span="6">
            <el-form-item label="包装宽度(cm)">
              <el-input :value="formatToTwoDecimals(formData.packageWidth)" />
            </el-form-item>
          </el-col>
          <el-col :span="6">
            <el-form-item label="包装高度(cm)">
              <el-input :value="formatToTwoDecimals(formData.packageHeight)" />
            </el-form-item>
          </el-col>
          <el-col :span="6">
            <el-form-item label="包装重量(g)">
              <el-input :value="formatToTwoDecimals(formData.packageWeight)" />
            </el-form-item>
          </el-col>
        </el-row>
        
        <!-- 商品标签 -->
        <el-form-item label="商品标签">
          <el-select
            v-model="formData.tags"
            multiple
            filterable
            style="width: 100%"
          >
            <el-option
              v-for="tag in allTags"
              :key="tag"
              :label="tag"
              :value="tag"
            />
          </el-select>
        </el-form-item>
        
        <!-- 业务类型 -->
        <el-divider content-position="left">业务类型</el-divider>
        
        <el-form-item label="支持业务">
          <el-checkbox v-model="formData.supportDropship">一件代发</el-checkbox>
          <el-checkbox v-model="formData.supportWholesale">批发</el-checkbox>
          <el-checkbox v-model="formData.supportCircleBuy">圈货</el-checkbox>
          <el-checkbox v-model="formData.supportSelfPickup">自提</el-checkbox>
        </el-form-item>
        
        <!-- 库存设置 -->
        <el-divider content-position="left">库存设置</el-divider>
        
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="可售库存">
              <el-input :value="formatToInteger(formData.stock.availableStock)" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="库存预警线">
              <el-input :value="formatToInteger(formData.alertStockLine)" />
            </el-form-item>
          </el-col>
        </el-row>
        
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="仓库代码">
              <el-input v-model="formData.stock.warehouseCode" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="仓库名称">
              <el-input v-model="formData.stock.warehouseName" />
            </el-form-item>
          </el-col>
        </el-row>
        
        <el-form-item label="仓库地址">
          <el-input v-model="formData.stock.warehouseAddress" />
        </el-form-item>
        
        <!-- 价格设置 -->
        <el-divider content-position="left">价格设置</el-divider>
        
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="币种">
              <el-select v-model="formData.price.currency">
                <el-option label="人民币(CNY)" value="CNY" />
                <el-option label="美元(USD)" value="USD" />
                <el-option label="欧元(EUR)" value="EUR" />
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="业务类型">
              <el-select v-model="formData.price.businessType">
                <el-option label="一件代发" value="dropship" />
                <el-option label="批发" value="wholesale" />
              </el-select>
            </el-form-item>
          </el-col>
        </el-row>
        
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="原价">
              <el-input :value="formatToTwoDecimals(formData.price.originalPrice)" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="折扣率(%)">
              <el-input :value="formatToPercentage(formData.price.discountRate)">
                <template #append>%</template>
              </el-input>
            </el-form-item>
          </el-col>
        </el-row>
        
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="售价">
              <el-input :value="formatToTwoDecimals(formData.price.sellingPrice)" readonly />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="最小起订量">
              <el-input :value="formatToInteger(formData.price.minWholesaleQuantity)" />
            </el-form-item>
          </el-col>
        </el-row>
        
        <!-- 会员折扣设置 -->
        <el-form-item label="会员折扣">
          <div class="member-discount-container">
            <el-row :gutter="20">
              <el-col :span="8" v-for="level in vipLevels" :key="level.value">
                <el-form-item :label="level.label">
                  <el-input :value="formatToPercentage(formData.price.memberDiscount[level.value])">
                    <template #append>%</template>
                  </el-input>
                  <div class="member-price" v-if="memberPrices[level.value] !== null">
                    实际售价：¥{{ memberPrices[level.value] }}
                  </div>
                </el-form-item>
              </el-col>
            </el-row>
          </div>
        </el-form-item>
        
        <!-- 物流设置 -->
        <el-divider content-position="left">物流设置</el-divider>
        
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="物流方式">
              <el-select v-model="formData.shipping.shippingMethod">
                <el-option label="标准配送" value="standard" />
                <el-option label="经济配送" value="economy" />
                <el-option label="自提" value="self_pickup" />
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="币种">
              <el-select v-model="formData.shipping.currency">
                <el-option label="人民币(CNY)" value="CNY" />
                <el-option label="美元(USD)" value="USD" />
                <el-option label="欧元(EUR)" value="EUR" />
              </el-select>
            </el-form-item>
          </el-col>
        </el-row>
        
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="运费">
              <el-input :value="formatToTwoDecimals(formData.shipping.shippingPrice)" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="参考时效">
              <el-input v-model="formData.shipping.deliveryTime" />
            </el-form-item>
          </el-col>
        </el-row>
        
        <el-form-item label="承运商">
          <el-input 
            v-if="formData.shipping.carriers && formData.shipping.carriers.length > 0"
            :value="formData.shipping.carriers.join(', ')"
            readonly
          />
          <el-input 
            v-else
            value="无"
            readonly
          />
        </el-form-item>
        
        <!-- 商品详情 -->
        <el-divider content-position="left">商品详情</el-divider>
        
        <el-form-item label="" class="full-width-editor">
          <div class="rich-content-preview" v-html="formData.richContent"></div>
        </el-form-item>
        
        <!-- 审核信息 -->
        <el-divider content-position="left">审核信息</el-divider>
        
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="商品状态">
              <el-tag :type="getStatusTagType(formData.status)">
                {{ getStatusText(formData.status) }}
              </el-tag>
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="审核人">
              <el-input v-model="formData.auditedBy" readonly />
            </el-form-item>
          </el-col>
        </el-row>
        
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="审核时间">
              <el-input v-model="formData.auditedAt" readonly />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="首次上架时间">
              <el-input v-model="formData.publishDate" readonly />
            </el-form-item>
          </el-col>
        </el-row>
        
        <el-form-item label="审核备注">
          <el-input
            v-model="formData.auditRemark"
            type="textarea"
            :rows="4"
            readonly
          />
        </el-form-item>
      </el-form>
    </el-card>
  </div>
</template>

<script>
import { ref, reactive, onMounted, defineProps, defineExpose, computed } from 'vue'
import {
  ElCard,
  ElForm,
  ElFormItem,
  ElInput,
  ElSelect,
  ElOption,
  ElButton,
  ElRow,
  ElCol,
  ElDivider,
  ElCheckbox,
  ElMessage,
  ElImage,
  ElIcon,
  ElTag
} from 'element-plus'
import { Picture } from '@element-plus/icons-vue'

export default {
  name: 'ProductView',
  components: {
    ElCard,
    ElForm,
    ElFormItem,
    ElInput,
    ElSelect,
    ElOption,
    ElButton,
    ElRow,
    ElCol,
    ElDivider,
    ElCheckbox,
    ElImage,
    ElIcon,
    ElTag,
    Picture
  },
  props: {
    adminLoginPath: {
      type: String,
      required: true
    },
    productId: {
      type: [Number, String],
      default: null
    },
    autoLoad: {
      type: Boolean,
      default: false
    }
  },
  setup(props) {
    const loading = ref(false)
    
    // 分类数据
    const categories = ref([])
    const subcategories = ref([])
    const items = ref([])
    
    // 物流公司数据
    const logisticsCompanies = ref([])
    
    // 所有标签选项
    const allTags = ref([
      '热卖', '新品', '促销', '推荐', '限时特价'
    ])

    // 会员等级信息
    const vipLevels = ref([
      { value: '0', label: '普通用户' },
      { value: '1', label: 'VIP1' },
      { value: '2', label: 'VIP2' },
      { value: '3', label: 'VIP3' },
      { value: '4', label: 'VIP4' },
      { value: '5', label: 'VIP5' }
    ]);

    // 加载会员等级信息
    const loadVipLevels = async () => {
      try {
        const response = await fetch(`/admin${props.adminLoginPath}/product/vip-levels`, {
          method: 'GET',
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          }
        });

        const result = await response.json();

        if (result.success) {
          // 转换数据格式以匹配前端代码
          vipLevels.value = result.data.map(level => ({
            value: level.value.toString(),
            label: level.label
          }));
        }
      } catch (error) {
        console.error('加载会员等级信息失败:', error);
        // 如果加载失败，使用默认值
        vipLevels.value = [
          { value: '0', label: '普通用户' },
          { value: '1', label: 'VIP1' },
          { value: '2', label: 'VIP2' },
          { value: '3', label: 'VIP3' },
          { value: '4', label: 'VIP4' },
          { value: '5', label: 'VIP5' }
        ];
      }
    };

    // 加载物流公司数据
    const loadLogisticsCompanies = async () => {
      try {
        const response = await fetch(`/admin${props.adminLoginPath}/product/logistics-companies`, {
          method: 'GET',
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          }
        });

        const result = await response.json();

        if (result.success) {
          logisticsCompanies.value = result.data || []
        }
      } catch (error) {
        console.error('加载物流公司数据失败:', error);
      }
    };

    // 表单数据
    const formData = reactive({
      id: null,
      sku: '',
      spu: '',
      title: '',
      titleEn: '',
      categoryId: '',
      subcategoryId: '',
      itemId: '',
      brand: '',
      length: null,
      width: null,
      height: null,
      weight: null,
      packageLength: null,
      packageWidth: null,
      packageHeight: null,
      packageWeight: null,
      tags: [],
      supportDropship: true,
      supportWholesale: false,
      supportCircleBuy: false,
      supportSelfPickup: false,
      alertStockLine: 10,
      richContent: '',
      status: 'draft',
      auditRemark: '',
      auditedBy: '',
      auditedAt: '',
      publishDate: '',
      // 库存信息
      stock: {
        availableStock: 0,
        warehouseCode: 'WH001',
        warehouseName: '默认仓库',
        warehouseAddress: ''
      },
      // 价格信息
      price: {
        currency: 'CNY',
        businessType: 'dropship',
        originalPrice: null,
        sellingPrice: null,
        discountRate: 0,
        memberDiscount: {
          '0': 0, // 普通会员
          '1': 0, // VIP1
          '2': 0, // VIP2
          '3': 0, // VIP3
          '4': 0, // VIP4
          '5': 0  // VIP5
        },
        minWholesaleQuantity: null
      },
      // 物流信息
      shipping: {
        shippingMethod: 'standard',
        currency: 'CNY',
        shippingPrice: '0.00',
        deliveryTime: '3-5天',
        carriers: []
      }
    })

    // 主图数据
    const mainImages = ref([])
    
    // 主图URL数组（用于预览）
    const mainImageUrls = computed(() => {
      return mainImages.value.map(image => image.url)
    })

    // 数字格式化函数 - 保留两位小数
    const formatToTwoDecimals = (value) => {
      if (value === null || value === undefined || value === '') return '-'
      const num = parseFloat(value)
      return isNaN(num) ? '-' : num.toFixed(2)
    }

    // 整数格式化函数
    const formatToInteger = (value) => {
      if (value === null || value === undefined || value === '') return '-'
      const num = parseFloat(value)
      return isNaN(num) ? '-' : Math.floor(num)
    }

    // 百分比格式化函数
    const formatToPercentage = (value) => {
      if (value === null || value === undefined || value === '') return '0%'
      const num = parseFloat(value)
      return isNaN(num) ? '0%' : num.toFixed(1) + '%'
    }

    // 加载分类数据
    const loadCategories = async () => {
      try {
        const response = await fetch(`/admin${props.adminLoginPath}/product/categories`, {
          method: 'GET',
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          }
        })

        const result = await response.json()

        if (result.success) {
          categories.value = result.data || []
        }
      } catch (error) {
        console.error('加载分类数据失败:', error)
      }
    }

    // 处理一级分类变化
    const handleCategoryChange = async (categoryId) => {
      formData.subcategoryId = ''
      formData.itemId = ''
      subcategories.value = []
      items.value = []

      if (!categoryId) return

      try {
        const response = await fetch(`/admin${props.adminLoginPath}/product/subcategories/${categoryId}`, {
          method: 'GET',
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          }
        })

        const result = await response.json()

        if (result.success) {
          subcategories.value = result.data || []
        }
      } catch (error) {
        console.error('加载子分类数据失败:', error)
      }
    }

    // 处理二级分类变化
    const handleSubcategoryChange = async (subcategoryId) => {
      formData.itemId = ''
      items.value = []

      if (!subcategoryId) return

      try {
        const response = await fetch(`/admin${props.adminLoginPath}/product/items/${subcategoryId}`, {
          method: 'GET',
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          }
        })

        const result = await response.json()

        if (result.success) {
          items.value = result.data || []
        }
      } catch (error) {
        console.error('加载商品项数据失败:', error)
      }
    }

    // 加载商品详情数据
    const loadProductDetail = async (productId = null) => {
      // 使用传入的productId或props.productId
      const id = productId || props.productId
      
      // 如果没有商品ID，等待一段时间后重试（处理异步设置的情况）
      if (!id) {
        // 等待100ms后重试
        await new Promise(resolve => setTimeout(resolve, 100))
        
        // 再次检查props.productId
        if (!props.productId) {
          ElMessage.error('商品ID缺失')
          loading.value = false
          return
        }
        
        // 使用更新后的productId
        return loadProductDetail(props.productId)
      }

      loading.value = true
      try {
        const response = await fetch(`/admin${props.adminLoginPath}/product/detail/${id}`, {
          method: 'GET',
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          }
        })

        const result = await response.json()

        if (result.success) {
          const product = result.data

          // 先加载分类数据
          await loadCategories()

          // 填充表单数据
          Object.assign(formData, {
            id: product.id,
            sku: product.sku,
            spu: product.spu,
            title: product.title,
            titleEn: product.titleEn || '',
            categoryId: product.categoryId || '',
            subcategoryId: product.subcategoryId || '',
            itemId: product.itemId || '',
            brand: product.brand || '',
            length: product.length,
            width: product.width,
            height: product.height,
            weight: product.weight,
            packageLength: product.packageLength,
            packageWidth: product.packageWidth,
            packageHeight: product.packageHeight,
            packageWeight: product.packageWeight,
            tags: product.tags || [],
            supportDropship: product.supportDropship,
            supportWholesale: product.supportWholesale,
            supportCircleBuy: product.supportCircleBuy,
            supportSelfPickup: product.supportSelfPickup,
            alertStockLine: product.alertStockLine || 10,
            richContent: product.richContent || '',
            status: product.status,
            auditRemark: product.auditRemark || '',
            auditedBy: product.auditedBy || '',
            auditedAt: product.auditedAt || '',
            publishDate: product.publishDate || ''
          })

          // 填充库存信息
          if (product.stock) {
            Object.assign(formData.stock, product.stock)
          }

          // 填充价格信息
          if (product.price) {
            formData.price.currency = product.price.currency || 'CNY'
            formData.price.businessType = product.price.businessType || 'dropship'
            formData.price.originalPrice = product.price.originalPrice
            formData.price.sellingPrice = product.price.sellingPrice
            formData.price.discountRate = product.price.discountRate ? product.price.discountRate * 100 : 0
            formData.price.minWholesaleQuantity = product.price.minWholesaleQuantity
            
            // 填充会员折扣
            if (product.price.memberDiscount) {
              Object.keys(formData.price.memberDiscount).forEach(key => {
                formData.price.memberDiscount[key] = product.price.memberDiscount[key] ? product.price.memberDiscount[key] * 100 : 0
              })
            }
          }

          // 填充物流信息
          if (product.shipping) {
            Object.assign(formData.shipping, product.shipping)
          }

          // 处理主图数据
          if (product.mainImages && Array.isArray(product.mainImages) && product.mainImages.length > 0) {
            mainImages.value = product.mainImages.map(img => ({
              url: img.url || ''
            }))
          } else if (product.mainImage || product.mainImageKey) {
            // 兼容旧数据格式
            const mainImageUrl = product.mainImage || product.mainImageKey
            mainImages.value = [{
              url: mainImageUrl
            }]
          } else {
            mainImages.value = []
          }

          // 如果有一级分类，加载二级分类
          if (formData.categoryId) {
            await handleCategoryChange(formData.categoryId)
          }

          // 如果有二级分类，加载三级分类
          if (formData.subcategoryId) {
            await handleSubcategoryChange(formData.subcategoryId)
          }
        } else {
          ElMessage.error(result.message || '加载商品详情失败')
        }
      } catch (error) {
        console.error('加载商品详情失败:', error)
        ElMessage.error('网络错误，请稍后重试')
      } finally {
        loading.value = false
      }
    }

    // 图片预览处理
    const handleImagePreview = (index) => {
      // Element Plus 的 el-image 组件会自动处理预览功能
      // 这里可以添加额外的处理逻辑（如果需要）
      console.log('预览图片，索引：', index)
    }

    // 获取状态标签类型
    const getStatusTagType = (status) => {
      const typeMap = {
        'draft': 'info',
        'pending': 'warning',
        'approved': 'success',
        'rejected': 'danger',
        'offline': ''
      }
      return typeMap[status] || 'info'
    }

    // 获取状态文本
    const getStatusText = (status) => {
      const statusMap = {
        'draft': '草稿',
        'pending': '待审核',
        'approved': '已通过',
        'rejected': '已拒绝',
        'offline': '已下架'
      }
      return statusMap[status] || status
    }

    // 初始化数据加载
    const initData = () => {
      loadProductDetail();
      loadVipLevels(); // 加载会员等级信息
      loadLogisticsCompanies(); // 加载物流公司信息
    }

    // 计算会员价格
    const memberPrices = computed(() => {
      const prices = {}
      const sellingPrice = parseFloat(formData.price.sellingPrice)
      
      if (isNaN(sellingPrice)) {
        // 如果售价无效，所有会员价都设为null
        for (let i = 0; i <= 5; i++) {
          prices[i] = null
        }
        return prices
      }
      
      // 计算每个会员等级的实际售价
      for (let i = 0; i <= 5; i++) {
        const discount = parseFloat(formData.price.memberDiscount[i]) || 0
        // 确保折扣率在0-90范围内
        const validDiscount = Math.max(0, Math.min(90, discount))
        // 计算实际售价：售价 * (1 - 折扣率/100)
        const memberPrice = sellingPrice * (1 - validDiscount / 100)
        prices[i] = memberPrice.toFixed(2)
      }
      
      return prices
    })

    // 暴露方法给父组件
    defineExpose({
      initData
    })

    // 组件挂载时
    onMounted(() => {
      if (props.autoLoad) {
        initData()
      }
    })

    return {
      loading,
      categories,
      subcategories,
      items,
      logisticsCompanies,
      allTags,
      formData,
      mainImages,
      mainImageUrls,
      loadCategories,
      handleCategoryChange,
      handleSubcategoryChange,
      loadProductDetail,
      handleImagePreview,
      getStatusTagType,
      getStatusText,
      initData,
      memberPrices,
      vipLevels,
      formatToTwoDecimals,
      formatToInteger,
      formatToPercentage
    }
  }
}
</script>

<style scoped>
.product-view {
  padding: 20px;
}

.box-card {
  max-width: 1200px;
  margin: 0 auto;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-weight: bold;
  font-size: 16px;
}

:deep(.el-form-item__label) {
  font-weight: 500;
}

:deep(.el-divider__text) {
  font-weight: bold;
  color: #303133;
}

.image-preview-list {
  display: flex;
  flex-wrap: wrap;
  gap: 15px;
}

.image-item {
  display: flex;
  flex-direction: column;
  gap: 5px;
}

.image-slot {
  display: flex;
  justify-content: center;
  align-items: center;
  width: 100%;
  height: 100%;
  background: #f5f7fa;
  color: #909399;
}

.member-discount-container {
  width: 100%;
}

/* 富文本编辑器铺满内容区域 */
.full-width-editor {
  width: 100%;
}

.full-width-editor :deep(.el-form-item__content) {
  width: 100% !important;
  margin-left: 0 !important;
}

.member-price {
  font-size: 12px;
  color: #67c23a;
  margin-top: 5px;
  font-weight: bold;
}

.rich-content-preview {
  min-height: 200px;
  padding: 15px;
  border: 1px solid #dcdfe6;
  border-radius: 4px;
  background-color: #fff;
}
</style>