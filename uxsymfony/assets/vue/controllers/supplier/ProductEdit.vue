<template>
  <div class="product-edit" v-loading="loading">
    <el-card class="box-card">
      <template #header>
        <div class="card-header">
          <span>编辑商品</span>
        </div>
      </template>
      
      <el-form :model="formData" :rules="formRules" ref="formRef" label-width="120px">
        <!-- 基本信息 -->
        <el-divider content-position="left">基本信息</el-divider>
        
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="SKU" prop="sku">
              <el-input v-model="formData.sku" placeholder="自动生成或手动输入" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="SPU" prop="spu">
              <el-input v-model="formData.spu" placeholder="自动生成或手动输入" />
            </el-form-item>
          </el-col>
        </el-row>
        
        <el-form-item label="商品名称" prop="title">
          <el-input v-model="formData.title" placeholder="请输入商品名称" />
        </el-form-item>
        
        <el-form-item label="英文名称">
          <el-input v-model="formData.titleEn" placeholder="请输入英文名称" />
          <div class="form-item-tip">如不填写英文名称，网站将默认显示中文名称</div>
        </el-form-item>
        
        <el-form-item label="商品主图" prop="mainImage">
          <div class="image-upload-list">
            <div v-for="(image, index) in mainImages" :key="`main-image-${index}`" class="image-item">
              <file-upload
                :model-value="image.url"
                @update:model-value="updateMainImageUrl(index, $event)"
                :supplier-login-path="supplierLoginPath"
                endpoint-type="product"
                accept="image/*"
                :max-size="5"
                class="image-upload-item"
              />
            </div>
            <div v-if="mainImages.length < 10" class="image-item">
              <file-upload
                :key="`new-main-upload-${newMainUploadKey}`"
                v-model="newMainImageKey"
                :supplier-login-path="supplierLoginPath"
                endpoint-type="product"
                accept="image/*"
                :max-size="5"
                class="image-upload-item"
                @upload-success="addNewMainImage"
              />
            </div>
          </div>

        </el-form-item>
        <div class="form-item-tip" style="text-align: center;">最多可上传10张主图，建议尺寸：800x800px，支持JPG、PNG格式，大小不超过5MB</div>
        <!-- 分类信息 -->
        <el-divider content-position="left">分类信息</el-divider>
        
        <el-row :gutter="20">
          <el-col :span="8">
            <el-form-item label="一级分类">
              <el-select v-model="formData.categoryId" placeholder="请选择分类" @change="handleCategoryChange" clearable>
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
              <el-select v-model="formData.subcategoryId" placeholder="请选择子分类" @change="handleSubcategoryChange" clearable :disabled="!formData.categoryId">
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
              <el-select v-model="formData.itemId" placeholder="请选择商品项" clearable :disabled="!formData.subcategoryId">
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
          <el-input v-model="formData.brand" placeholder="请输入品牌" />
        </el-form-item>
        
        <el-row :gutter="20">
          <el-col :span="6">
            <el-form-item label="长度(cm)">
              <el-input v-model.number="formData.length" type="number" placeholder="长度" />
            </el-form-item>
          </el-col>
          <el-col :span="6">
            <el-form-item label="宽度(cm)">
              <el-input v-model.number="formData.width" type="number" placeholder="宽度" />
            </el-form-item>
          </el-col>
          <el-col :span="6">
            <el-form-item label="高度(cm)">
              <el-input v-model.number="formData.height" type="number" placeholder="高度" />
            </el-form-item>
          </el-col>
          <el-col :span="6">
            <el-form-item label="重量(g)">
              <el-input v-model.number="formData.weight" type="number" placeholder="重量" />
            </el-form-item>
          </el-col>
        </el-row>
        
        <!-- 包装信息 -->
        <el-divider content-position="left">包装信息</el-divider>
        
        <el-row :gutter="20">
          <el-col :span="6">
            <el-form-item label="包装长度(cm)">
              <el-input v-model.number="formData.packageLength" type="number" placeholder="包装长度" />
            </el-form-item>
          </el-col>
          <el-col :span="6">
            <el-form-item label="包装宽度(cm)">
              <el-input v-model.number="formData.packageWidth" type="number" placeholder="包装宽度" />
            </el-form-item>
          </el-col>
          <el-col :span="6">
            <el-form-item label="包装高度(cm)">
              <el-input v-model.number="formData.packageHeight" type="number" placeholder="包装高度" />
            </el-form-item>
          </el-col>
          <el-col :span="6">
            <el-form-item label="包装重量(g)">
              <el-input v-model.number="formData.packageWeight" type="number" placeholder="包装重量" />
            </el-form-item>
          </el-col>
        </el-row>
        
        <!-- 商品标签 -->
        <el-form-item label="商品标签">
          <el-select
            v-model="formData.tags"
            multiple
            filterable
            allow-create
            default-first-option
            placeholder="请输入商品标签，可创建新标签"
            style="width: 100%"
          >
            <el-option label="热卖" value="热卖" />
            <el-option label="新品" value="新品" />
            <el-option label="促销" value="促销" />
            <el-option label="推荐" value="推荐" />
            <el-option label="限时特价" value="限时特价" />
          </el-select>
          <div class="form-item-tip">可选择现有标签或创建新标签，按回车确认</div>
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
              <el-input v-model.number="formData.stock.availableStock" type="number" placeholder="请输入可售库存数量" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="库存预警线">
              <el-input v-model.number="formData.alertStockLine" type="number" placeholder="当库存低于此值时预警" />
              <div class="form-item-tip">当库存低于此值时系统会发送预警通知</div>
            </el-form-item>
          </el-col>
        </el-row>
        
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="仓库代码">
              <el-input v-model="formData.stock.warehouseCode" placeholder="请输入仓库代码" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="仓库名称">
              <el-input v-model="formData.stock.warehouseName" placeholder="请输入仓库名称" />
            </el-form-item>
          </el-col>
        </el-row>
        
        <el-form-item label="仓库地址">
          <el-input v-model="formData.stock.warehouseAddress" placeholder="请输入仓库地址" />
        </el-form-item>
        
        <!-- 价格设置 -->
        <el-divider content-position="left">价格设置</el-divider>
        
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="币种">
              <el-select v-model="formData.price.currency" placeholder="请选择币种">
                <el-option label="人民币(CNY)" value="CNY" />
                <el-option label="美元(USD)" value="USD" />
                <el-option label="欧元(EUR)" value="EUR" />
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="业务类型">
              <el-select v-model="formData.price.businessType" placeholder="请选择业务类型">
                <el-option label="一件代发" value="dropship" />
                <el-option label="批发" value="wholesale" />
              </el-select>
            </el-form-item>
          </el-col>
        </el-row>
        
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="原价">
              <el-input v-model.number="formData.price.originalPrice" type="number" placeholder="请输入原价" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="折扣率(%)">
              <el-input v-model.number="formData.price.discountRate" type="number" placeholder="请输入折扣率(0-90)" min="0" max="90">
                <template #append>%</template>
              </el-input>
              <div class="form-item-tip">折扣范围：0-90%，例如输入10表示10%折扣</div>
            </el-form-item>
          </el-col>
        </el-row>
        
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="售价">
              <el-input v-model.number="formData.price.sellingPrice" type="number" placeholder="根据原价和折扣自动计算" readonly />
              <div class="form-item-tip">根据原价和折扣率自动计算</div>
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="最小起订量">
              <el-input v-model.number="formData.price.minWholesaleQuantity" type="number" placeholder="请输入最小起订量" />
            </el-form-item>
          </el-col>
        </el-row>
        
        <!-- 会员折扣设置 -->
        <el-form-item label="会员折扣">
          <div class="member-discount-container">
            <el-row :gutter="20">
              <el-col :span="8" v-for="level in vipLevels" :key="level.value">
                <el-form-item :label="level.label">
                  <el-input 
                    v-model.number="formData.price.memberDiscount[level.value]" 
                    type="number" 
                    placeholder="0" 
                    min="0" 
                    max="90"
                  >
                    <template #append>%</template>
                  </el-input>
                  <div class="member-price" v-if="memberPrices[level.value] !== null">
                    实际售价：¥{{ memberPrices[level.value] }}
                  </div>
                </el-form-item>
              </el-col>
            </el-row>
          </div>
          <div class="form-item-tip">会员折扣范围：0-90%，以售价为基础计算，例如售价100元，折扣10%则会员价为90元</div>
        </el-form-item>
        
        <!-- 物流设置 -->
        <el-divider content-position="left">物流设置</el-divider>
        
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="物流方式">
              <el-select v-model="formData.shipping.shippingMethod" placeholder="请选择物流方式">
                <el-option label="标准配送" value="standard" />
                <el-option label="经济配送" value="economy" />
                <el-option label="自提" value="self_pickup" />
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="币种">
              <el-select v-model="formData.shipping.currency" placeholder="请选择币种">
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
              <el-input v-model.number="formData.shipping.shippingPrice" type="number" placeholder="请输入运费" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="参考时效">
              <el-input v-model="formData.shipping.deliveryTime" placeholder="如：3-5天" />
            </el-form-item>
          </el-col>
        </el-row>
        
        <el-form-item label="承运商">
          <el-select v-model="formData.shipping.carriers" multiple placeholder="请选择承运商" style="width: 100%;" filterable>
            <el-option 
              v-for="company in logisticsCompanies" 
              :key="company.id" 
              :label="company.name + ' / ' + company.nameEn" 
              :value="company.id" 
            />
          </el-select>
        </el-form-item>
        
        <!-- 商品详情 -->
        <el-divider content-position="left">商品详情</el-divider>
        
        <el-form-item label="" class="full-width-editor">
          <rich-text-editor
            v-model="formData.richContent"
            :supplier-login-path="supplierLoginPath"
            placeholder="请输入商品详细内容..."
            height="500px"
          />
        </el-form-item>
        
        <!-- 提交状态 -->
        <el-divider content-position="left">提交状态</el-divider>
        
        <el-form-item label="商品状态" prop="status">
          <el-radio-group v-model="formData.status">
            <el-radio label="draft">保存为草稿</el-radio>
            <el-radio label="pending">提交审核</el-radio>
          </el-radio-group>
          <div class="form-item-tip">草稿状态可以继续编辑，提交审核后需等待管理员审核</div>
        </el-form-item>
        
        <!-- 提交按钮 -->
        <el-form-item>
          <el-button type="primary" @click="handleSubmit" :loading="submitting">更新</el-button>
          <el-button @click="handleCancel">取消</el-button>
        </el-form-item>
      </el-form>
    </el-card>
  </div>
</template>

<script>
import { ref, reactive, onMounted, defineProps, defineExpose, watch, computed } from 'vue'
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
  ElRadio,
  ElRadioGroup,
  ElCheckbox,
  ElMessage,
  ElMessageBox
} from 'element-plus'
import FileUpload from '../admin/FileUpload.vue'
import RichTextEditor from './RichTextEditor.vue'

export default {
  name: 'ProductEdit',
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
    ElRadio,
    ElRadioGroup,
    ElCheckbox,
    FileUpload,
    RichTextEditor
  },
  props: {
    supplierLoginPath: {
      type: String,
      required: true
    },
    productId: {
      type: [Number, String],
      required: true
    },
    autoLoad: {
      type: Boolean,
      default: false
    }
  },
  setup(props) {
    const loading = ref(false)
    const submitting = ref(false)
    const formRef = ref(null)
    const newImageKey = ref('') // 用于新增图片的临时key
    const newImageAltText = ref('') // 用于新增图片的描述文本
    const isNewImageMain = ref(false) // 用于新增图片是否为主图
    const newUploadKey = ref(0) // 用于强制刷新新的上传组件

    // 分类数据
    const categories = ref([])
    const subcategories = ref([])
    const items = ref([])

    // 物流公司数据
    const logisticsCompanies = ref([])

    // 表单数据
    const formData = reactive({
      id: null,
      sku: '',
      spu: '',
      title: '',
      titleEn: '',
      mainImage: '',
      detailImages: [], // 详情图数组，每个元素包含url, altText
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
        discountRate: 0, // 折扣率
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

    // Quill 编辑器已集成到 RichTextEditor 组件中，无需额外配置

    // 监听原价和折扣率变化，自动计算售价
    watch(() => [formData.price.originalPrice, formData.price.discountRate], () => {
      if (formData.price.originalPrice !== null && formData.price.originalPrice !== undefined) {
        const originalPrice = parseFloat(formData.price.originalPrice)
        const discountRate = parseFloat(formData.price.discountRate) || 0
        
        // 确保折扣率在0-90范围内
        const validDiscountRate = Math.max(0, Math.min(90, discountRate))
        formData.price.discountRate = validDiscountRate
        
        // 计算售价：原价 * (1 - 折扣率/100)
        const sellingPrice = originalPrice * (1 - validDiscountRate / 100)
        formData.price.sellingPrice = sellingPrice.toFixed(2)
      } else {
        formData.price.sellingPrice = null
      }
    })

    // 添加数字格式化函数
    const formatToTwoDecimals = (value) => {
      if (value === null || value === undefined || value === '') return null
      const num = parseFloat(value)
      return isNaN(num) ? null : num.toFixed(2)
    }

    // 添加数字输入验证函数
    const validateTwoDecimals = (rule, value, callback) => {
      if (value === null || value === undefined || value === '') {
        callback()
        return
      }
      
      const num = parseFloat(value)
      if (isNaN(num)) {
        callback(new Error('请输入有效的数字'))
        return
      }
      
      // 检查小数位数
      const strValue = value.toString()
      if (strValue.includes('.')) {
        const decimalPart = strValue.split('.')[1]
        if (decimalPart.length > 2) {
          callback(new Error('最多只能输入两位小数'))
          return
        }
      }
      
      callback()
    }

    // 表单验证规则
    const formRules = {
      title: [
        { required: true, message: '请输入商品名称', trigger: 'blur' }
      ],
      status: [
        { required: true, message: '请选择商品状态', trigger: 'change' }
      ],
      // 添加主图验证规则
      mainImage: [
        { 
          validator: (rule, value, callback) => {
            // 检查是否有至少一张主图
            const hasMainImage = mainImages.value.some(image => image.url && image.url.trim() !== '');
            if (!hasMainImage) {
              callback(new Error('请至少上传一张商品主图'));
            } else {
              callback();
            }
          }, 
          trigger: 'change' 
        }
      ],
      // 添加数字字段验证规则
      'price.originalPrice': [
        { validator: validateTwoDecimals, trigger: 'blur' }
      ],
      'price.sellingPrice': [
        { validator: validateTwoDecimals, trigger: 'blur' }
      ],
      'shipping.shippingPrice': [
        { validator: validateTwoDecimals, trigger: 'blur' }
      ],
      // 商品属性验证规则
      'length': [
        { validator: validateTwoDecimals, trigger: 'blur' }
      ],
      'width': [
        { validator: validateTwoDecimals, trigger: 'blur' }
      ],
      'height': [
        { validator: validateTwoDecimals, trigger: 'blur' }
      ],
      'weight': [
        { validator: validateTwoDecimals, trigger: 'blur' }
      ],
      // 包装信息验证规则
      'packageLength': [
        { validator: validateTwoDecimals, trigger: 'blur' }
      ],
      'packageWidth': [
        { validator: validateTwoDecimals, trigger: 'blur' }
      ],
      'packageHeight': [
        { validator: validateTwoDecimals, trigger: 'blur' }
      ],
      'packageWeight': [
        { validator: validateTwoDecimals, trigger: 'blur' }
      ],
      // 库存信息验证规则
      'stock.availableStock': [
        { validator: validateNonNegativeInteger, trigger: 'blur' }
      ],
      'alertStockLine': [
        { validator: validateNonNegativeInteger, trigger: 'blur' }
      ],
      // 价格信息验证规则
      'price.minWholesaleQuantity': [
        { validator: validateNonNegativeInteger, trigger: 'blur' }
      ]
    }

    // 为会员折扣动态添加验证规则
    const addMemberDiscountValidationRules = () => {
      // 为每个会员等级添加验证规则
      for (let i = 0; i <= 5; i++) {
        formRules[`price.memberDiscount.${i}`] = [
          { validator: validateOneDecimal, trigger: 'blur' }
        ];
      }
    };

    // 添加一位小数验证函数（用于会员折扣）
    const validateOneDecimal = (rule, value, callback) => {
      if (value === null || value === undefined || value === '') {
        callback();
        return;
      }
      
      const num = parseFloat(value);
      if (isNaN(num)) {
        callback(new Error('请输入有效的数字'));
        return;
      }
      
      // 检查小数位数
      const strValue = value.toString();
      if (strValue.includes('.')) {
        const decimalPart = strValue.split('.')[1];
        if (decimalPart.length > 1) {
          callback(new Error('最多只能输入一位小数'));
          return;
        }
      }
      
      // 检查范围是否在0-90之间
      if (num < 0 || num > 90) {
        callback(new Error('折扣率必须在0-90之间'));
        return;
      }
      
      callback();
    };

    // 添加正整数（包括零）验证函数
    const validateNonNegativeInteger = (rule, value, callback) => {
      if (value === null || value === undefined || value === '') {
        callback()
        return
      }
      
      const num = parseFloat(value)
      if (isNaN(num)) {
        callback(new Error('请输入有效的数字'))
        return
      }
      
      // 检查是否为非负整数
      if (num < 0 || !Number.isInteger(num)) {
        callback(new Error('请输入非负整数'))
        return
      }
      
      callback()
    }

    // 监听表单数据变化，自动格式化数字字段
    watch(() => formData.length, (newVal) => {
      if (newVal !== null && newVal !== undefined && newVal !== '') {
        const num = parseFloat(newVal)
        if (!isNaN(num)) {
          formData.length = num // 不再自动格式化为两位小数
        }
      }
    })

    watch(() => formData.width, (newVal) => {
      if (newVal !== null && newVal !== undefined && newVal !== '') {
        const num = parseFloat(newVal)
        if (!isNaN(num)) {
          formData.width = num // 不再自动格式化为两位小数
        }
      }
    })

    watch(() => formData.height, (newVal) => {
      if (newVal !== null && newVal !== undefined && newVal !== '') {
        const num = parseFloat(newVal)
        if (!isNaN(num)) {
          formData.height = num // 不再自动格式化为两位小数
        }
      }
    })

    watch(() => formData.weight, (newVal) => {
      if (newVal !== null && newVal !== undefined && newVal !== '') {
        const num = parseFloat(newVal)
        if (!isNaN(num)) {
          formData.weight = num // 不再自动格式化为两位小数
        }
      }
    })

    watch(() => formData.packageLength, (newVal) => {
      if (newVal !== null && newVal !== undefined && newVal !== '') {
        const num = parseFloat(newVal)
        if (!isNaN(num)) {
          formData.packageLength = num // 不再自动格式化为两位小数
        }
      }
    })

    watch(() => formData.packageWidth, (newVal) => {
      if (newVal !== null && newVal !== undefined && newVal !== '') {
        const num = parseFloat(newVal)
        if (!isNaN(num)) {
          formData.packageWidth = num // 不再自动格式化为两位小数
        }
      }
    })

    watch(() => formData.packageHeight, (newVal) => {
      if (newVal !== null && newVal !== undefined && newVal !== '') {
        const num = parseFloat(newVal)
        if (!isNaN(num)) {
          formData.packageHeight = num // 不再自动格式化为两位小数
        }
      }
    })

    watch(() => formData.packageWeight, (newVal) => {
      if (newVal !== null && newVal !== undefined && newVal !== '') {
        const num = parseFloat(newVal)
        if (!isNaN(num)) {
          formData.packageWeight = num // 不再自动格式化为两位小数
        }
      }
    })

    watch(() => formData.stock.availableStock, (newVal) => {
      if (newVal !== null && newVal !== undefined && newVal !== '') {
        const num = parseFloat(newVal)
        if (!isNaN(num)) {
          formData.stock.availableStock = Math.floor(num) // 库存为整数
        }
      }
    })

    watch(() => formData.price.originalPrice, (newVal) => {
      if (newVal !== null && newVal !== undefined && newVal !== '') {
        const num = parseFloat(newVal)
        if (!isNaN(num)) {
          formData.price.originalPrice = num // 不再自动格式化为两位小数
        }
      }
    })

    watch(() => formData.price.sellingPrice, (newVal) => {
      if (newVal !== null && newVal !== undefined && newVal !== '') {
        const num = parseFloat(newVal)
        if (!isNaN(num)) {
          formData.price.sellingPrice = num // 不再自动格式化为两位小数
        }
      }
    })

    watch(() => formData.price.minWholesaleQuantity, (newVal) => {
      if (newVal !== null && newVal !== undefined && newVal !== '') {
        const num = parseFloat(newVal)
        if (!isNaN(num)) {
          formData.price.minWholesaleQuantity = Math.floor(num) // 最小起订量为整数
        }
      }
    })

    watch(() => formData.shipping.shippingPrice, (newVal) => {
      if (newVal !== null && newVal !== undefined && newVal !== '') {
        const num = parseFloat(newVal)
        if (!isNaN(num)) {
          formData.shipping.shippingPrice = num // 不再自动格式化为两位小数
        }
      }
    })

    watch(() => formData.alertStockLine, (newVal) => {
      if (newVal !== null && newVal !== undefined && newVal !== '') {
        const num = parseFloat(newVal)
        if (!isNaN(num)) {
          formData.alertStockLine = Math.floor(num) // 库存预警线为整数
        }
      }
    })

    // 监听会员折扣变化，不再自动格式化
    watch(() => formData.price.memberDiscount, (newVal) => {
      if (newVal && typeof newVal === 'object') {
        Object.keys(newVal).forEach(key => {
          if (newVal[key] !== null && newVal[key] !== undefined && newVal[key] !== '') {
            const num = parseFloat(newVal[key])
            if (!isNaN(num)) {
              newVal[key] = num // 不再自动格式化为一位小数
            }
          }
        })
      }
    }, { deep: true })

    // 添加新图片
    const addNewImage = () => {
      if (newImageKey.value && formData.detailImages.length < 10) {
        formData.detailImages.push({
          url: newImageKey.value,
          altText: newImageAltText.value
        })
        
        // 重置临时变量
        newImageKey.value = ''
        newImageAltText.value = ''
        newUploadKey.value++ // 增加key值以强制刷新新的上传组件
      }
    }

    // 加载分类数据
    const loadCategories = async () => {
      try {
        const response = await fetch(`/supplier${props.supplierLoginPath}/product/categories`, {
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
        const response = await fetch(`/supplier${props.supplierLoginPath}/product/subcategories/${categoryId}`, {
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
        const response = await fetch(`/supplier${props.supplierLoginPath}/product/items/${subcategoryId}`, {
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

    // 主图数据
    const mainImages = ref([
      { url: '' }
    ]);
    const newMainImageKey = ref('');
    const newMainUploadKey = ref(0);

    // 更新主图URL
    const updateMainImageUrl = (index, newUrl) => {
      console.log(`[商品编辑] 更新主图[${index}] URL:`, newUrl);
      if (mainImages.value[index]) {
        mainImages.value[index].url = newUrl;
        // 强制触发响应式更新
        mainImages.value = [...mainImages.value];
      }
    };

    // 添加新主图
    const addNewMainImage = () => {
      if (newMainImageKey.value && mainImages.value.length < 10) {
        const newImage = {
          url: newMainImageKey.value
        };
        mainImages.value.push(newImage);
        
        // 重置临时变量
        newMainImageKey.value = '';
        newMainUploadKey.value++; // 增加key值以强制刷新新的上传组件
      }
    };

    // 加载商品详情数据
    const loadProductDetail = async () => {
      if (!props.productId) {
        ElMessage.error('商品ID缺失')
        return
      }

      loading.value = true
      try {
        const response = await fetch(`/supplier${props.supplierLoginPath}/product/detail/${props.productId}`, {
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
            mainImage: product.mainImageKey || '',
            // 修复：处理详情图数据，确保格式一致
            detailImages: Array.isArray(product.detailImages) 
              ? product.detailImages.map(img => ({
                  url: img.url || img.imageKey || '',  // 支持多种数据格式
                  altText: img.altText || ''
                }))
              : [],
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
            status: product.status
          })

          console.log('[商品编辑] 加载的详情图数据:', formData.detailImages)

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
            // 处理承运商数据，确保只保留名称格式的数据
            if (product.shipping.carriers && Array.isArray(product.shipping.carriers)) {
              // 过滤掉ID，只保留名称格式的数据
              const carrierNames = product.shipping.carriers.filter(carrier => {
                return typeof carrier === 'string' && carrier.includes(' / ');
              });
              product.shipping.carriers = carrierNames;
            }
            Object.assign(formData.shipping, product.shipping);
          }

          // 处理主图数据
          console.log('[商品编辑] 后端返回的主图数据:', product.mainImages);
          if (product.mainImages && Array.isArray(product.mainImages) && product.mainImages.length > 0) {
            // 后端返回主图数组，每个元素是包含url和imageKey的对象
            // FileUpload组件需要的是imageKey，而不是完整URL
            mainImages.value = product.mainImages.map((img, index) => ({
              url: img.imageKey || ''  // 使用imageKey而不是url
            }));
            console.log('[商品编辑] 处理后的主图数据:', mainImages.value);
          } else if (product.mainImage || product.mainImageKey) {
            // 后端只返回单个主图，我们需要将其转换为数组格式
            const mainImageKey = product.mainImageKey || product.mainImage;
            mainImages.value = [{
              url: mainImageKey
            }];
          } else {
            // 没有主图数据，初始化为空数组
            mainImages.value = [{ url: '' }];
          }
          console.log('[商品编辑] 最终的主图数据:', mainImages.value);
        
          // 强制触发响应式更新，确保图片能正确显示
          mainImages.value = [...mainImages.value];
        
          // 延迟一段时间后再次更新，确保FileUpload组件能正确处理数据
          setTimeout(() => {
            mainImages.value = [...mainImages.value];
            console.log('[商品编辑] 延迟更新后的主图数据:', mainImages.value);
          }, 100);

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
    };

    // 提交表单 - 更新商品
    const handleSubmit = async () => {
      if (!formRef.value) return

      await formRef.value.validate(async (valid) => {
        if (!valid) {
          ElMessage.error('请完善表单信息')
          return
        }

        try {
          submitting.value = true;

          // 检查是否有主图
          const hasMainImage = mainImages.value.some(image => image.url && image.url.trim() !== '');
          if (!hasMainImage) {
            ElMessage.error('请至少上传一张商品主图');
            submitting.value = false;
            return;
          }

          // 设置主图（使用第一张图片作为主图）
          if (mainImages.value.length > 0 && mainImages.value[0].url) {
            formData.mainImage = mainImages.value[0].url;
          }

          // 构造提交数据
          const submitData = {
            ...formData,
            // 添加主图数据
            mainImages: mainImages.value
          }

          const response = await fetch(`/supplier${props.supplierLoginPath}/product/update/${formData.id}`, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(submitData)
          })

          const result = await response.json()

          if (result.success) {
            ElMessage.success('商品更新成功')
            
            // 先关闭当前编辑商品标签页
            window.dispatchEvent(new CustomEvent('close-current-tab'))
            // 然后触发导航事件返回商品列表
            setTimeout(() => {
              window.dispatchEvent(new CustomEvent('navigate-to', {
                detail: { key: 'product-list' }
              }))
            }, 100)
          } else {
            ElMessage.error(result.message || '更新失败')
          }
        } catch (error) {
          console.error('更新失败:', error)
          ElMessage.error('网络错误，请稍后重试')
        } finally {
          submitting.value = false
        }
      })
    }

    // 取消编辑
    const handleCancel = () => {
      ElMessageBox.confirm('确定要取消编辑吗？未保存的数据将丢失。', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '继续编辑',
        type: 'warning'
      }).then(() => {
        // 先关闭当前编辑商品标签页
        window.dispatchEvent(new CustomEvent('close-current-tab'))
        // 然后触发导航事件返回商品列表
        setTimeout(() => {
          window.dispatchEvent(new CustomEvent('navigate-to', {
            detail: { key: 'product-list' }
          }))
        }, 100)
      })
    }

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
        const response = await fetch(`/supplier${props.supplierLoginPath}/product/vip-levels`, {
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

    // 计算会员价格
    const memberPrices = computed(() => {
      const prices = {};
      const sellingPrice = parseFloat(formData.price.sellingPrice);
      
      if (isNaN(sellingPrice)) {
        // 如果售价无效，所有会员价都设为null
        for (const level of vipLevels.value) {
          prices[level.value] = null;
        }
        return prices;
      }
      
      // 计算每个会员等级的实际售价
      for (const level of vipLevels.value) {
        const discount = parseFloat(formData.price.memberDiscount[level.value]) || 0;
        // 确保折扣率在0-90范围内
        const validDiscount = Math.max(0, Math.min(90, discount));
        // 计算实际售价：售价 * (1 - 折扣率/100)
        const memberPrice = sellingPrice * (1 - validDiscount / 100);
        prices[level.value] = memberPrice.toFixed(2);
      }
      
      return prices;
    });

    // 加载物流公司数据
    const loadLogisticsCompanies = async () => {
      try {
        const response = await fetch(`/supplier${props.supplierLoginPath}/product/logistics-companies`, {
          method: 'GET',
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          }
        })

        const result = await response.json()

        if (result.success) {
          logisticsCompanies.value = result.data || []
        }
      } catch (error) {
        console.error('加载物流公司数据失败:', error)
      }
    }

    // 初始化数据加载
    const initData = () => {
      loadProductDetail();
      loadVipLevels(); // 加载会员等级信息
      loadLogisticsCompanies(); // 加载物流公司信息
    };

    // 暴露方法给父组件
    defineExpose({
      initData
    })

    // 组件挂载时
    onMounted(() => {
      if (props.autoLoad) {
        initData()
      }
      addMemberDiscountValidationRules();
    })

    return {
      loading,
      submitting,
      formRef,
      newImageKey,
      newImageAltText,
      newUploadKey,
      categories,
      subcategories,
      items,
      logisticsCompanies, // 添加物流公司数据
      formData,
      formRules,
      mainImages,
      newMainImageKey,
      newMainUploadKey,
      updateMainImageUrl,
      addNewMainImage,
      loadProductDetail,
      handleSubmit,
      handleCancel,
      initData,
      memberPrices,
      // 添加 vipLevels 到返回的对象中
      vipLevels
    }
  }
}

</script>

<style scoped>
.product-edit {
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

.form-item-tip {
  font-size: 12px;
  color: #909399;
  margin-top: 5px;
}

:deep(.el-form-item__label) {
  font-weight: 500;
}

:deep(.el-divider__text) {
  font-weight: bold;
  color: #303133;
}

.image-upload-list {
  display: flex;
  flex-wrap: wrap;
  gap: 15px;
}

.image-item {
  display: flex;
  flex-direction: column;
  gap: 5px;
}

.image-info {
  display: flex;
  flex-direction: column;
  gap: 5px;
}

.image-info .el-input {
  width: 178px;
}

.image-info .el-checkbox {
  margin-top: 5px;
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
</style>
