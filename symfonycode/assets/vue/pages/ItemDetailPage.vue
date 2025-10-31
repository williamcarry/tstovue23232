<script setup>
import { ref, computed, onMounted } from 'vue'

const props = defineProps({
  product: {
    type: [Object, String],
    default: () => ({})
  },
  productId: {
    type: Number,
    default: 0
  },
  relatedProducts: {
    type: [Array, String],
    default: () => []
  }
})

const parseData = (data) => {
  if (typeof data === 'string') {
    try {
      return JSON.parse(data)
    } catch {
      return data
    }
  }
  return data || {}
}

const product = ref(parseData(props.product))
const relatedProducts = ref(parseData(props.relatedProducts))
const selectedImage = ref(product.value.image || '')
const selectedQuantity = ref(1)
const activeTab = ref('detail')

onMounted(() => {
  if (product.value.images && product.value.images.length > 0) {
    selectedImage.value = product.value.images[0]
  }
})

const handleAddToCart = () => {
  // 添加到购物车逻辑
  alert(`已添加 ${selectedQuantity.value} 件到购物车`)
}

const handleBuyNow = () => {
  // 立即购买逻辑
  window.location.href = `/shop/cart?product=${product.value.id}&qty=${selectedQuantity.value}`
}

const handleRelatedProductClick = (productId) => {
  window.location.href = `/shop/product/${productId}`
}
</script>

<template>
  <div class="min-h-screen bg-slate-50">
    <div class="mx-auto w-full max-w-[1500px] md:w-[80%] md:min-w-[1150px] px-4 md:px-0 py-6">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- 左侧：图片 -->
        <div class="md:col-span-1">
          <div class="bg-white rounded-lg p-4">
            <img :src="selectedImage" :alt="product.name" class="w-full aspect-square object-cover rounded" />
            <div class="grid grid-cols-4 gap-2 mt-4">
              <img
                v-for="(img, idx) in (product.images || [product.image])"
                :key="idx"
                :src="img"
                :alt="`${product.name} ${idx + 1}`"
                @click="selectedImage = img"
                :class="[
                  'aspect-square object-cover rounded cursor-pointer border-2 transition',
                  selectedImage === img ? 'border-primary' : 'border-slate-200'
                ]"
              />
            </div>
          </div>
        </div>

        <!-- 右侧：信息 -->
        <div class="md:col-span-2">
          <div class="bg-white rounded-lg p-6">
            <!-- 标题 -->
            <h1 class="text-2xl font-bold text-slate-900 mb-2">{{ product.name }}</h1>

            <!-- 评分 -->
            <div class="flex items-center gap-2 mb-4">
              <span class="text-yellow-400">★★★★★</span>
              <span class="text-sm text-slate-600">{{ product.rating }} ({{ product.reviews }} 条评价)</span>
            </div>

            <!-- 价格 -->
            <div class="mb-4 p-4 bg-slate-50 rounded">
              <div class="flex items-end gap-2 mb-2">
                <span class="text-3xl font-bold text-primary">¥{{ product.price?.toFixed(2) || 0 }}</span>
                <span class="text-lg text-slate-500 line-through">¥{{ product.originalPrice?.toFixed(2) || 0 }}</span>
              </div>
              <p class="text-sm text-slate-600">销量: {{ product.sales }} 件</p>
            </div>

            <!-- 基本信息 -->
            <div class="grid grid-cols-2 gap-3 mb-4 text-sm">
              <div><span class="text-slate-600">库存：</span><span class="font-semibold">{{ product.stock || 0 }}</span></div>
              <div><span class="text-slate-600">SKU：</span><span class="font-semibold">{{ product.sku }}</span></div>
              <div><span class="text-slate-600">最小订购：</span><span class="font-semibold">{{ product.minOrder || 1 }}</span></div>
              <div><span class="text-slate-600">供应商：</span><span class="font-semibold">{{ product.supplier }}</span></div>
            </div>

            <!-- 规格选择 -->
            <div v-if="product.specs && product.specs.length > 0" class="mb-4">
              <p class="text-sm font-semibold text-slate-900 mb-2">规格选择</p>
              <div v-for="spec in product.specs" :key="spec.name" class="mb-2">
                <p class="text-xs text-slate-600 mb-1">{{ spec.name }}</p>
                <div class="flex gap-2 flex-wrap">
                  <button
                    v-for="val in spec.value.split(',')"
                    :key="val"
                    class="px-3 py-1 text-xs border border-slate-300 rounded hover:border-primary transition"
                  >
                    {{ val.trim() }}
                  </button>
                </div>
              </div>
            </div>

            <!-- 数量选择 -->
            <div class="flex items-center gap-2 mb-4">
              <span class="text-sm font-semibold text-slate-900">数量：</span>
              <button
                @click="selectedQuantity = Math.max(1, selectedQuantity - 1)"
                class="px-3 py-1 border border-slate-300 rounded hover:text-primary"
              >
                -
              </button>
              <input
                v-model.number="selectedQuantity"
                type="number"
                min="1"
                class="w-12 text-center border border-slate-300 rounded"
              />
              <button
                @click="selectedQuantity++"
                class="px-3 py-1 border border-slate-300 rounded hover:text-primary"
              >
                +
              </button>
            </div>

            <!-- 按钮 -->
            <div class="flex gap-3">
              <button
                @click="handleAddToCart"
                class="btn btn-outline flex-1"
              >
                加入购物车
              </button>
              <button
                @click="handleBuyNow"
                class="btn btn-primary flex-1"
              >
                立即购买
              </button>
            </div>
          </div>

          <!-- 选项卡：详情、评价、运费 -->
          <div class="mt-6 bg-white rounded-lg">
            <div class="flex border-b border-slate-200">
              <button
                @click="activeTab = 'detail'"
                :class="['flex-1 py-3 text-center font-semibold border-b-2 transition', activeTab === 'detail' ? 'border-primary text-primary' : 'border-transparent text-slate-600']"
              >
                商品详情
              </button>
              <button
                @click="activeTab = 'reviews'"
                :class="['flex-1 py-3 text-center font-semibold border-b-2 transition', activeTab === 'reviews' ? 'border-primary text-primary' : 'border-transparent text-slate-600']"
              >
                用户评价
              </button>
              <button
                @click="activeTab = 'shipping'"
                :class="['flex-1 py-3 text-center font-semibold border-b-2 transition', activeTab === 'shipping' ? 'border-primary text-primary' : 'border-transparent text-slate-600']"
              >
                运费信息
              </button>
            </div>

            <div class="p-6">
              <div v-show="activeTab === 'detail'" class="prose prose-sm max-w-none">
                <p>{{ product.description }}</p>
              </div>
              <div v-show="activeTab === 'reviews'" class="text-center py-8 text-slate-500">
                暂无评价
              </div>
              <div v-show="activeTab === 'shipping'" class="text-sm text-slate-600">
                <p>运费：{{ product.shippingFee }} 元</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 相关商品 -->
      <div v-if="relatedProducts && relatedProducts.length > 0" class="mt-8">
        <h2 class="text-xl font-bold text-slate-900 mb-4">相关商品</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4">
          <div
            v-for="item in relatedProducts"
            :key="item.id"
            @click="handleRelatedProductClick(item.id)"
            class="product-card cursor-pointer"
          >
            <img :src="item.image" :alt="item.name" class="product-image" />
            <div class="p-3">
              <h3 class="product-title">{{ item.name }}</h3>
              <div class="flex items-center mt-2">
                <span class="product-price">¥{{ item.price.toFixed(2) }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* 组件特定样式 */
</style>
