<template>
  <div class="min-h-screen bg-slate-50">
    <div class="mx-auto w-full max-w-[1500px] md:w-[80%] md:min-w-[1150px] px-4 md:px-0 py-6">
      <div class="bg-white shadow-sm mb-6">
        <div class="flex items-center px-5 py-2 border-b border-slate-200">
          <span class="font-bold text-sm w-20 shrink-0 text-left mr-4 inline-block text-primary px-2 py-1 rounded">欢迎</span>
          <div class="flex-1 flex flex-wrap gap-2 items-center">
            <span class="text-slate-500 text-sm">欢迎来到我们的商城</span>
          </div>
        </div>
      </div>
      
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-8">
        <div
          v-for="product in products"
          :key="product.id"
          class="bg-white rounded-lg overflow-hidden shadow-sm hover:shadow-lg transition"
        >
          <div class="aspect-square overflow-hidden bg-slate-100 flex items-center justify-center">
            <div class="text-4xl">🛒</div>
          </div>
          
          <div class="p-4">
            <h3 class="text-sm text-slate-900 font-medium line-clamp-2 hover:text-primary transition">
              {{ product.title }}
            </h3>
            <p class="text-xs text-slate-500 mb-3">SKU: {{ product.sku }}</p>
            <div class="mb-3 p-2 bg-slate-50 rounded text-center">
              <span class="text-primary text-sm">¥{{ product.price }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  products: {
    type: Array,
    default: () => [
      {id: 1, title: '商品1', sku: 'SKU001', price: '99.00'},
      {id: 2, title: '商品2', sku: 'SKU002', price: '199.00'},
      {id: 3, title: '商品3', sku: 'SKU003', price: '299.00'},
      {id: 4, title: '商品4', sku: 'SKU004', price: '399.00'}
    ]
  }
});
</script>

<style scoped>
.text-primary {
  @apply text-blue-500;
}
.bg-primary {
  @apply bg-blue-500;
}
</style>