<!--
CSS 引用说明：
1. 全局样式：在 src/main.ts 中自动加载
   - src/assets/main.css (导入 src/assets/base.css)
     - @tailwind base, components, utilities (Tailwind CSS)
     - 全局 CSS 变量 (--color-*, --section-gap, --category-width 等)
   - Element Plus 样式 (element-plus/dist/index.css)
2. 页面局部样式：该��件底部的 <style scoped> 块
3. 导入的子组件样式：由各子组件的 <style scoped> 块提供
-->
<template>
  <div class="min-h-screen" style="background-color: #f2f3f7">
    <SiteHeader />

    <!-- Hero Section with purple gradient background -->
    <div
      class="w-full py-12"
      style="
        background-image: url('https://www.saleyee.com/static/imgs/74596c7198f18c4bc5d1f0ed47151b9e.png'),
          linear-gradient(135deg, rgb(81, 53, 190) 0%, rgb(155, 56, 197) 100%);
        background-repeat: no-repeat, repeat;
        background-size: auto, auto;
        background-position: 0px 0%, 0% 0%;
      "
    >
      <div class="mx-auto w-full max-w-[1500px] md:w-[80%] md:min-w-[1200px] px-4 md:px-0">
        <div class="text-center">
          <div class="flex items-center justify-center gap-3 mb-4">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12a1 1 0 001 1h2a1 1 0 001-1V6a1 1 0 00-1-1h-2a1 1 0 00-1 1v12m0-12V4a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 001 1h1a1 1 0 001-1V4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 001 1h2a1 1 0 001-1v-1" />
            </svg>
            <span class="text-white text-lg font-semibold">畅销精选</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content Container -->
    <div class="mx-auto w-full max-w-[1500px] md:w-[80%] md:min-w-[1200px] px-4 md:px-0 py-8">
      <!-- 1. 热度排行榜 Section -->
      <div class="bg-white rounded-lg shadow-md overflow-hidden mb-8">
        <div class="bg-purple-600 text-white px-6 py-4 flex items-center gap-3">
          <img
            src="https://www.saleyee.com/ContentNew/Images/2023/202309/topsales_icon1.png"
            alt="热度排行榜"
            class="w-6 h-6"
          />
          <h2 class="text-xl font-bold">热度排行榜</h2>
        </div>

        <div class="p-6 max-h-[700px] overflow-y-auto">
          <p class="text-sm text-slate-600 mb-6 flex items-start gap-2">
            <span class="text-slate-400 font-bold mt-0.5">ℹ</span>
            <span>根据产品销量和搜索点击量等综合计算后得出</span>
          </p>

          <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
            <div v-for="(item, index) in hotRankings" :key="'hot-' + index" class="bg-white border border-slate-200 rounded-lg overflow-hidden hover:shadow-lg transition">
              <a :href="`/item/${item.spu}.html`" class="block relative">
                <div class="relative w-full aspect-square bg-slate-100 overflow-hidden">
                  <img
                    :src="item.image"
                    :alt="item.title"
                    class="w-full h-full object-cover hover:scale-105 transition"
                  />
                  <div
                    class="absolute top-0 left-0 w-8 h-8 bg-contain flex items-center justify-center text-white text-xs font-bold"
                    style="
                      background-image: url('https://www.saleyee.com/static/imgs/1a3194981ae43cb3240a0902417c7956.png');
                    "
                  >
                    {{ index + 1 }}
                  </div>
                </div>
              </a>

              <div class="p-3">
                <a :href="`/item/${item.spu}.html`" class="block mb-2">
                  <h3 class="text-xs text-slate-800 font-medium line-clamp-2 hover:text-primary transition h-8">
                    {{ item.title }}
                  </h3>
                </a>

                <div class="mb-2 flex items-center gap-1">
                  <img
                    src="https://www.saleyee.com/ContentNew/Images/2023/202309/topsales_icon1.png"
                    alt="热度"
                    class="w-4 h-4"
                  />
                  <span class="text-red-600 text-xs font-semibold">{{ item.heat }}</span>
                </div>

                <p class="text-red-600 text-xs font-semibold mb-2">登录后可见</p>

                <div class="flex flex-wrap gap-1">
                  <button
                    v-for="warehouse in item.warehouses"
                    :key="warehouse"
                    type="button"
                    class="px-1.5 py-0.5 text-xs rounded border border-red-600 text-red-600 hover:bg-red-600 hover:text-white transition"
                  >
                    {{ warehouse }}
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 2. 收��排行榜 Section -->
      <div class="bg-white rounded-lg shadow-md overflow-hidden mb-8">
        <div class="bg-purple-600 text-white px-6 py-4 flex items-center gap-3">
          <img
            src="https://www.saleyee.com/ContentNew/Images/2023/202309/topsales_icon2.png"
            alt="收藏排行榜"
            class="w-6 h-6"
          />
          <h2 class="text-xl font-bold">收��排行榜</h2>
        </div>

        <div class="p-6 max-h-[700px] overflow-y-auto">
          <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
            <div v-for="(item, index) in favoriteRankings" :key="'fav-' + index" class="bg-white border border-slate-200 rounded-lg overflow-hidden hover:shadow-lg transition">
              <a :href="`/item/${item.spu}.html`" class="block relative">
                <div class="relative w-full aspect-square bg-slate-100 overflow-hidden">
                  <img
                    :src="item.image"
                    :alt="item.title"
                    class="w-full h-full object-cover hover:scale-105 transition"
                  />
                  <div
                    class="absolute top-0 left-0 w-8 h-8 bg-contain flex items-center justify-center text-white text-xs font-bold"
                    style="
                      background-image: url('https://www.saleyee.com/static/imgs/937eb20746fa4bd9f4e250a38fd5e18d.png');
                    "
                  >
                    {{ index + 1 }}
                  </div>
                </div>
              </a>

              <div class="p-3">
                <a :href="`/item/${item.spu}.html`" class="block mb-2">
                  <h3 class="text-xs text-slate-800 font-medium line-clamp-2 hover:text-primary transition h-8">
                    {{ item.title }}
                  </h3>
                </a>

                <div class="mb-2 flex items-center gap-1">
                  <img
                    src="https://www.saleyee.com/ContentNew/Images/2023/202309/topsales_icon2.png"
                    alt="收藏"
                    class="w-4 h-4"
                  />
                  <span class="text-red-600 text-xs font-semibold">{{ item.favorites }}</span>
                </div>

                <p class="text-red-600 text-xs font-semibold mb-2">登录后可见</p>

                <div class="flex flex-wrap gap-1">
                  <button
                    v-for="warehouse in item.warehouses"
                    :key="warehouse"
                    type="button"
                    class="px-1.5 py-0.5 text-xs rounded border border-red-600 text-red-600 hover:bg-red-600 hover:text-white transition"
                  >
                    {{ warehouse }}
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 3. 下载排行榜 Section -->
      <div class="bg-white rounded-lg shadow-md overflow-hidden mb-8">
        <div class="bg-purple-600 text-white px-6 py-4 flex items-center gap-3">
          <img
            src="https://www.saleyee.com/ContentNew/Images/2023/202309/topsales_icon3.png"
            alt="下载排行榜"
            class="w-6 h-6"
          />
          <h2 class="text-xl font-bold">下载排行榜</h2>
        </div>

        <div class="p-6 max-h-[700px] overflow-y-auto">
          <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
            <div v-for="(item, index) in downloadRankings" :key="'down-' + index" class="bg-white border border-slate-200 rounded-lg overflow-hidden hover:shadow-lg transition">
              <a :href="`/item/${item.spu}.html`" class="block relative">
                <div class="relative w-full aspect-square bg-slate-100 overflow-hidden">
                  <img
                    :src="item.image"
                    :alt="item.title"
                    class="w-full h-full object-cover hover:scale-105 transition"
                  />
                  <div
                    class="absolute top-0 left-0 w-8 h-8 bg-contain flex items-center justify-center text-white text-xs font-bold"
                    style="
                      background-image: url('https://www.saleyee.com/static/imgs/b209fb68f715fb09f9b54897ad1b4a93.png');
                    "
                  >
                    {{ index + 1 }}
                  </div>
                  <span
                    v-if="item.variants"
                    class="absolute bottom-0 left-0 right-0 px-2 py-1 bg-black/50 text-white text-xs text-center"
                  >
                    {{ item.variants }}
                  </span>
                </div>
              </a>

              <div class="p-3">
                <a :href="`/item/${item.spu}.html`" class="block mb-2">
                  <h3 class="text-xs text-slate-800 font-medium line-clamp-2 hover:text-primary transition h-8">
                    {{ item.title }}
                  </h3>
                </a>

                <div class="mb-2 flex items-center gap-1">
                  <img
                    src="https://www.saleyee.com/ContentNew/Images/2023/202309/topsales_icon3.png"
                    alt="下载"
                    class="w-4 h-4"
                  />
                  <span class="text-red-600 text-xs font-semibold">{{ item.downloads }}</span>
                </div>

                <p class="text-red-600 text-xs font-semibold mb-2">登录后可见</p>

                <div class="flex flex-wrap gap-1">
                  <button
                    v-for="warehouse in item.warehouses"
                    :key="warehouse"
                    type="button"
                    class="px-1.5 py-0.5 text-xs rounded border border-red-600 text-red-600 hover:bg-red-600 hover:text-white transition"
                  >
                    {{ warehouse }}
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 4. 刊登映射排行榜 Section -->
      <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="bg-purple-600 text-white px-6 py-4 flex items-center gap-3">
          <img
            src="https://www.saleyee.com/ContentNew/Images/2023/202309/topsales_icon4.png"
            alt="刊登映射排行榜"
            class="w-6 h-6"
          />
          <h2 class="text-xl font-bold">刊登映射排行榜</h2>
        </div>

        <div class="p-6 max-h-[700px] overflow-y-auto">
          <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
            <div v-for="(item, index) in publishRankings" :key="'pub-' + index" class="bg-white border border-slate-200 rounded-lg overflow-hidden hover:shadow-lg transition">
              <a :href="`/item/${item.spu}.html`" class="block relative">
                <div class="relative w-full aspect-square bg-slate-100 overflow-hidden">
                  <img
                    :src="item.image"
                    :alt="item.title"
                    class="w-full h-full object-cover hover:scale-105 transition"
                  />
                  <div
                    class="absolute top-0 left-0 w-8 h-8 bg-contain flex items-center justify-center text-white text-xs font-bold"
                    style="
                      background-image: url('https://www.saleyee.com/static/imgs/b2f7049c751c34aa56ea6969d4ee4a7a.png');
                    "
                  >
                    {{ index + 1 }}
                  </div>
                </div>
              </a>

              <div class="p-3">
                <a :href="`/item/${item.spu}.html`" class="block mb-2">
                  <h3 class="text-xs text-slate-800 font-medium line-clamp-2 hover:text-primary transition h-8">
                    {{ item.title }}
                  </h3>
                </a>

                <div class="mb-2 flex items-center gap-1">
                  <img
                    src="https://www.saleyee.com/ContentNew/Images/2023/202309/topsales_icon4.png"
                    alt="刊��"
                    class="w-4 h-4"
                  />
                  <span class="text-red-600 text-xs font-semibold">{{ item.publishes }}</span>
                </div>

                <p class="text-red-600 text-xs font-semibold mb-2">登录后可见</p>

                <div class="flex flex-wrap gap-1">
                  <button
                    v-for="warehouse in item.warehouses"
                    :key="warehouse"
                    type="button"
                    class="px-1.5 py-0.5 text-xs rounded border border-red-600 text-red-600 hover:bg-red-600 hover:text-white transition"
                  >
                    {{ warehouse }}
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <SiteFooter />
    <RightFloatNav />
  </div>
</template>

<script setup>
import SiteHeader from '@/components/SiteHeader.vue'
import SiteFooter from '@/components/SiteFooter.vue'
import RightFloatNav from '@/components/RightFloatNav.vue'

const hotRankingsBase = [
  {
    spu: '48805228',
    title: '10层9格无纺布鞋柜 ��色',
    image: 'https://img-accelerate.saleyee.cn/Resources/GoodsImages/2024/202404/5b1487bd-c161-4a8b-9a8b-5b07c7199fd2.Jpeg',
    heat: 6576,
    warehouses: ['US', 'UK', 'DE'],
  },
  {
    spu: '10380296',
    title: '【认证未出】铁制 20L 0.6mm 油桶 ����式 红色',
    image: 'https://img-accelerate.saleyee.cn/Resources/GoodsImages/2023/202309/4b1d68a8-895a-41ed-a001-e0d5621661a5.Jpeg',
    heat: 1908,
    warehouses: ['US', 'UK', 'DE'],
  },
  {
    spu: '52194990',
    title: '32in 铁艺 ���方形 黑色 烧柴火盆',
    image: 'https://img-accelerate.saleyee.cn/Resources/GoodsImages/2021/202102/43f9078f-e24d-47c7-8f53-a743e042bc5f.Jpeg',
    heat: 1392,
    warehouses: ['US', 'UK', 'FR'],
  },
  {
    spu: '97819768',
    title: '4/4 夹板 自然色 小提琴 N101',
    image: 'https://img-accelerate.saleyee.cn/Resources/GoodsImages/2022/202209/5bc737f9-f9fa-43eb-af41-2e2f8ad91065.Jpeg',
    heat: 1146,
    warehouses: ['US', 'UK'],
  },
  {
    spu: '49192021',
    title: '铁艺面板 27*27*34cm 黑色 可拆卸 片装便携式 野营火炉',
    image: 'https://img-accelerate.saleyee.cn/Resources/GoodsImages/2024/202405/c103f36e-c3d1-4716-bd72-28327d48a024.Jpeg',
    heat: 1068,
    warehouses: ['US'],
  },
  {
    spu: '29871662',
    title: '有靠背带纹路圆凳哈哈脚吧凳-黑色',
    image: 'https://img-accelerate.saleyee.cn/Resources/GoodsImages/2024/202401/bca2859e-1cf0-4ce1-9879-5b9aaf68109b.Jpeg',
    heat: 501,
    warehouses: ['US', 'UK', 'FR', 'DE'],
  },
  {
    spu: '10835179',
    title: 'TA-851B 360°转向 电视天线',
    image: 'https://img-accelerate.saleyee.cn/Resources/GoodsImages/2024/202406/ed32d02f-87bc-4389-ab77-b222594ea889.Jpeg',
    heat: 408,
    warehouses: ['US'],
  },
  {
    spu: '42371446',
    title: '【替换63038075】发动机维修工具',
    image: 'https://img-accelerate.saleyee.cn/Resources/GoodsImages/2025/202506/35fcbf25-91a0-4d8c-b45e-921bbce9d967.Jpeg',
    heat: 339,
    warehouses: ['US'],
  },
  {
    spu: '44772942',
    title: '创意指尖陀螺自行车链条解压旋转变形陀螺',
    image: 'https://img-accelerate.saleyee.cn/Resources/GoodsImages/2024/202405/01d958a5-79a7-4567-9b89-b5908e932ac0.Jpeg',
    heat: 189,
    warehouses: ['US'],
  },
  {
    spu: '31353576',
    title: '竖纹款三合一带拉手万向轮 拉杆箱',
    image: 'https://img-accelerate.saleyee.cn/Resources/GoodsImages/2023/202312/20f4fd49-d463-484e-ad7e-ab00c497d3ae.Jpeg',
    heat: 51,
    warehouses: ['US', 'FR', 'DE', 'UK'],
  },
]

const favoriteRankingsBase = [
  {
    spu: '84908498',
    title: '绿色 7ft 1100枝头 PVC材质 圣诞树 N101 美国',
    image: 'https://img-accelerate.saleyee.cn/Resources/GoodsImages/2021/202111/865ccf5e-f038-43de-a838-f32827ec88d2.Jpeg',
    favorites: 261,
    warehouses: ['US', 'UK'],
  },
  {
    spu: '70669850',
    title: '5.9ft 夏威夷老人 6颗LED灯 束口袋 圣诞充气装饰',
    image: 'https://img-accelerate.saleyee.cn/Resources/GoodsImages/2025/202508/1bb16269-6a2c-4ae9-ad75-92691ee4d52a.Jpeg',
    favorites: 198,
    warehouses: ['US'],
  },
  {
    spu: '49192021',
    title: '铁艺面板 27*27*34cm 黑色 可拆卸 片装便携式 野营火炉',
    image: 'https://img-accelerate.saleyee.cn/Resources/GoodsImages/2024/202405/c103f36e-c3d1-4716-bd72-28327d48a024.Jpeg',
    favorites: 189,
    warehouses: ['US'],
  },
  {
    spu: '57091998',
    title: '硬壳行李箱套装 3 件套三合一手提随身行李箱',
    image: 'https://img-accelerate.saleyee.cn/upload/product/202509/482c9d91-f629-47ef-bb67-f7c43e5405c2.png',
    favorites: 117,
    warehouses: ['US'],
  },
  {
    spu: '04197299',
    title: '铸铁 6QT 烹饪锅具 渐变绿 含两个硅胶手套 珐琅锅',
    image: 'https://img-accelerate.saleyee.cn/Resources/GoodsImages/2025/202508/c0f1b0d5-d95a-42ca-91b9-d2a612aaa92e.Jpeg',
    favorites: 99,
    warehouses: ['US'],
  },
  {
    spu: '29871662',
    title: '有靠背带纹路圆凳哈哈脚吧凳-黑色',
    image: 'https://img-accelerate.saleyee.cn/Resources/GoodsImages/2024/202401/bca2859e-1cf0-4ce1-9879-5b9aaf68109b.Jpeg',
    favorites: 99,
    warehouses: ['US', 'UK', 'FR', 'DE'],
  },
  {
    spu: '26853047',
    title: '【同款编码：56442889】美规 洗衣机',
    image: 'https://img-accelerate.saleyee.cn/upload/product/202509/df9f2b93-5d30-4ade-a6fd-8f5d8f8da9b7.png',
    favorites: 72,
    warehouses: ['US'],
  },
  {
    spu: '62326860',
    title: '13in*14in 2鼓牛铃 不锈钢 天巴鼓 银色（电镀）',
    image: 'https://img-accelerate.saleyee.cn/Resources/GoodsImages/2025/202510/9a5de42b-f4a6-4ea7-967c-eaf5687545c2.Jpeg',
    favorites: 72,
    warehouses: ['US'],
  },
  {
    spu: '38999855',
    title: '通用独木舟车顶架，1 对-杆架',
    image: 'https://img-accelerate.saleyee.cn/Resources/GoodsImages/2025/202507/440f8af9-75f6-4c49-922f-d59cc482f5f3.Jpeg',
    favorites: 63,
    warehouses: ['US'],
  },
  {
    spu: '10980710',
    title: '儿童汽车',
    image: 'https://img-accelerate.saleyee.cn/upload/product/202508/80bf96c4-3707-4e26-a298-aa06d0e46021.png',
    favorites: 54,
    warehouses: ['US'],
  },
]

const downloadRankingsBase = [
  {
    spu: '75682614',
    title: '3抽抽屉柜 床头柜储物柜 白色',
    image: 'https://img-accelerate.saleyee.cn/Resources/GoodsImages/2023/202312/85c45220-58e0-4610-9d6f-f5c3e4e64021.Jpeg',
    downloads: 153,
    variants: '6 个��体',
    warehouses: ['US'],
  },
  {
    spu: '01929826',
    title: '工业风可折叠储物箱工具箱',
    image: 'https://img-accelerate.saleyee.cn/Resources/GoodsImages/2025/202508/be2f81e1-d2bc-411c-b82e-64133827b8a3.Jpeg',
    downloads: 144,
    warehouses: ['US'],
  },
  {
    spu: '98102606',
    title: '3-4人 蓝色 手搭便携 露营帐篷',
    image: 'https://img-accelerate.saleyee.cn/Resources/GoodsImages/2025/202509/c1a1dc0d-8491-4420-ab09-2d5033e56e50.Jpeg',
    downloads: 144,
    warehouses: ['US'],
  },
  {
    spu: '46811514',
    title: '40*30*52.5cm 矩形 双层桌板 圆棒腿 柚木色 庭院木边��',
    image: 'https://img-accelerate.saleyee.cn/Resources/GoodsImages/2025/202508/7628df6c-4a10-4aef-a066-a7f82dca173c.Jpeg',
    downloads: 126,
    warehouses: ['US'],
  },
  {
    spu: '89880061',
    title: '10pcs 黑色 铁框架 庭院塑料折叠椅',
    image: 'https://img-accelerate.saleyee.cn/Resources/GoodsImages/2025/202509/586c98c3-636a-41ef-b077-0c89b5223cef.Jpeg',
    downloads: 126,
    warehouses: ['US'],
  },
  {
    spu: '51280911',
    title: '3*3m 4片面（两个门） 螺旋管 凉棚 白色',
    image: 'https://img-accelerate.saleyee.cn/Resources/GoodsImages/2021/202103/66170899-f8b6-47b8-8ef2-558dcaa946ce.Jpeg',
    downloads: 117,
    warehouses: ['US', 'UK', 'FR'],
  },
  {
    spu: '72835500',
    title: '美规 HT1287B 120V 1000W 15in 壁炉',
    image: 'https://img-accelerate.saleyee.cn/Resources/GoodsImages/2024/202409/ecb71fe1-a60d-4955-b2a4-6a3bc121546a.Jpeg',
    downloads: 72,
    warehouses: ['US'],
  },
  {
    spu: '84479976',
    title: '黑色 刨花板 贴麻面三胺 一门三抽 电脑桌',
    image: 'https://img-accelerate.saleyee.cn/Resources/GoodsImages/2025/202509/8de44673-d425-402f-9576-f669a7bc6295.Jpeg',
    downloads: 45,
    warehouses: ['US'],
  },
  {
    spu: '29744966',
    title: '成人电子游戏椅，带脚凳的PU皮革游戏椅',
    image: 'https://img-accelerate.saleyee.cn/Resources/GoodsImages/2024/202405/9f5f0f1f-2756-4899-ae49-4193b3aadfb0.Jpeg',
    downloads: 45,
    variants: '3 个变体',
    warehouses: ['US'],
  },
  {
    spu: '10980710',
    title: '儿童汽车',
    image: 'https://img-accelerate.saleyee.cn/upload/product/202508/80bf96c4-3707-4e26-a298-aa06d0e46021.png',
    downloads: 18,
    warehouses: ['US'],
  },
]

const publishRankingsBase = [
  {
    spu: '11900120',
    title: '磨砂2.0 76*122厘米 凸型 30*48in PVC 地垫',
    image: 'https://img-accelerate.saleyee.cn/upload/product/202509/6777fff5-ad0b-4cba-842d-193854be5a85.png',
    publishes: 90,
    warehouses: ['US'],
  },
  {
    spu: '76185510',
    title: '解压指尖五叶陀螺（4个装 ���缘泡泡颜色随机）',
    image: 'https://img-accelerate.saleyee.cn/Resources/GoodsImages/2024/202404/ad27a506-7b96-4173-8efb-48957a64a905.Jpeg',
    publishes: 81,
    warehouses: ['US'],
  },
  {
    spu: '44772942',
    title: '创意指尖陀螺自行车链条解压旋转变形陀���',
    image: 'https://img-accelerate.saleyee.cn/Resources/GoodsImages/2024/202405/01d958a5-79a7-4567-9b89-b5908e932ac0.Jpeg',
    publishes: 81,
    warehouses: ['US'],
  },
  {
    spu: '38289022',
    title: '6ft 红色葱布 圣诞老人拿铃铛 200L暖白皮线灯',
    image: 'https://img-accelerate.saleyee.cn/upload/product/202509/822bf231-884a-4f41-92bc-3825f22a5f39.png',
    publishes: 81,
    warehouses: ['US'],
  },
  {
    spu: '21141614',
    title: '深灰色 pvc复合 1pc 商用地垫 入户用',
    image: 'https://img-accelerate.saleyee.cn/upload/product/202509/fd8a496f-b87e-4b13-ac4a-ebe07f8561d8.png',
    publishes: 72,
    warehouses: ['US'],
  },
  {
    spu: '99931063',
    title: '美规 HSTW-13 14L 烘干机',
    image: 'https://img-accelerate.saleyee.cn/upload/product/202509/7bfddb44-f7ee-4de4-b3b6-a74372f48065.png',
    publishes: 63,
    warehouses: ['US'],
  },
  {
    spu: '46580251',
    title: '170型 39in 双-单-双拾音器 带音箱 科技木指板 电吉他',
    image: 'https://img-accelerate.saleyee.cn/upload/product/202509/19e11623-ec26-4f95-bf4d-4968c73ba8f3.png',
    publishes: 54,
    warehouses: ['US'],
  },
  {
    spu: '71031972',
    title: 'MDF 二抽一门 折叠 粉色 美甲桌',
    image: 'https://img-accelerate.saleyee.cn/upload/product/202509/b1d3f3a9-8270-4f3d-89d5-2fd73fd16b5e.png',
    publishes: 45,
    warehouses: ['US'],
  },
  {
    spu: '84746245',
    title: '动力转向泵 Power Steering Pump',
    image: 'https://img-accelerate.saleyee.cn/Resources/GoodsImages/2025/202501/5d50d361-847b-4e2b-a9df-86640c3e71e7.Jpeg',
    publishes: 45,
    warehouses: ['US'],
  },
  {
    spu: '31353576',
    title: '竖纹款三合一带拉手万向轮 拉杆箱',
    image: 'https://img-accelerate.saleyee.cn/Resources/GoodsImages/2023/202312/20f4fd49-d463-484e-ad7e-ab00c497d3ae.Jpeg',
    publishes: 36,
    warehouses: ['US', 'FR', 'DE', 'UK'],
  },
]
const extendDemo = (arr, times = 1) => {
  const out = [...arr]
  for (let t = 0; t < times; t++) {
    out.push(
      ...arr.map((it, idx) => ({
        ...it,
        spu: `${it.spu}-demo${t + 1}-${idx + 1}`,
        title: `${it.title}（Demo ${t + 1}）`,
      })),
    )
  }
  return out
}

const hotRankings = extendDemo(hotRankingsBase, 1)
const favoriteRankings = extendDemo(favoriteRankingsBase, 1)
const downloadRankings = extendDemo(downloadRankingsBase, 1)
const publishRankings = extendDemo(publishRankingsBase, 1)
</script>

<style scoped></style>
