<!--
CSS 引用说明：
1. 全局样式：在 src/main.ts 中自动加载
   - src/assets/main.css (导入 src/assets/base.css)
     - @tailwind base, components, utilities (Tailwind CSS)
     - 全局 CSS 变量 (--color-*, --section-gap, --category-width 等)
   - Element Plus 样式 (element-plus/dist/index.css)
2. 页面局部样式：该文件底部的 <style scoped> 块
3. 导入的子组件样式：由各子组件的 <style scoped> 块提供
-->
<template>
  <div class="bg-white rounded-lg border border-slate-200 p-6">
    <!-- 标题 -->
    <div class="mb-6">
      <h3 class="text-sm font-semibold text-slate-900">基本信息</h3>
    </div>

    <!-- 进度步骤 -->
    <div class="flex items-center justify-between mb-8">
      <div class="flex flex-col items-center">
        <div class="flex items-center justify-center w-12 h-12 rounded-full bg-green-100 text-green-600 font-semibold mb-2">✓</div>
        <span class="text-xs text-slate-600">注册账号</span>
      </div>
      <div class="flex-1 h-0.5 bg-slate-300 mx-4"></div>
      <div class="flex flex-col items-center">
        <div class="flex items-center justify-center w-12 h-12 rounded-full bg-primary text-white font-semibold mb-2">2</div>
        <span class="text-xs text-slate-600">完善信息</span>
      </div>
      <div class="flex-1 h-0.5 bg-slate-300 mx-4"></div>
      <div class="flex flex-col items-center">
        <div class="flex items-center justify-center w-12 h-12 rounded-full bg-slate-300 text-slate-600 font-semibold mb-2">3</div>
        <span class="text-xs text-slate-600">实名认证</span>
      </div>
      <div class="flex-1 h-0.5 bg-slate-300 mx-4"></div>
      <div class="flex flex-col items-center">
        <div class="flex items-center justify-center w-12 h-12 rounded-full bg-slate-300 text-slate-600 font-semibold mb-2">4</div>
        <span class="text-xs text-slate-600">认证完成</span>
      </div>
    </div>

    <!-- 重要提醒 -->
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
      <p class="text-sm text-yellow-800">
        <span class="font-semibold">重要提醒：</span>劳烦如实填写信息，以便我们安排最合适的业务经理为您提供定制化的分销服务。您的信息也将纳入本站
        <a href="#" class="text-primary hover:underline">《隐私协议》</a>，受到严格��护。
      </p>
    </div>

    <!-- 基础信息表单 -->
    <form class="space-y-6">
      <!-- 账户类型 -->
      <div class="flex gap-4">
        <label class="flex items-center gap-2 cursor-pointer">
          <input type="radio" v-model="formData.accountType" value="personal" class="w-4 h-4" />
          <span class="text-sm text-slate-700">个人</span>
        </label>
        <label class="flex items-center gap-2 cursor-pointer">
          <input type="radio" v-model="formData.accountType" value="enterprise" class="w-4 h-4" />
          <span class="text-sm text-slate-700">企业</span>
        </label>
        <a href="#" class="text-primary text-sm hover:underline">查看账户权益</a>
        <p class="text-xs text-slate-600 ml-auto">个人或企业账户的分销价格一样，但企业账户的会员权益更多，建议您尽量选择企业账户。</p>
      </div>

      <!-- 名称 -->
      <div class="flex gap-4">
        <label class="w-24 text-sm font-semibold text-slate-900 text-right pt-2">
          <span class="text-red-500">*</span> 名称
        </label>
        <div class="flex-1">
          <el-input v-model="formData.firstName" placeholder="请输入名称" />
        </div>
      </div>

      <!-- 姓氏 -->
      <div class="flex gap-4">
        <label class="w-24 text-sm font-semibold text-slate-900 text-right pt-2">
          <span class="text-red-500">*</span> 姓氏
        </label>
        <div class="flex-1">
          <el-input v-model="formData.lastName" placeholder="请输入姓氏" />
        </div>
      </div>

      <!-- 省份/城市 -->
      <div class="flex gap-4" v-if="formData.accountType === 'enterprise'">
        <label class="w-24 text-sm font-semibold text-slate-900 text-right pt-2">
          <span class="text-red-500">*</span> 省份/城市
        </label>
        <div class="flex-1 flex gap-4">
          <el-select v-model="formData.province" placeholder="请选择省份" class="flex-1">
            <el-option label="北京市" value="beijing" />
            <el-option label="上海市" value="shanghai" />
            <el-option label="广东省" value="guangdong" />
          </el-select>
          <el-select v-model="formData.city" placeholder="请选择城市" class="flex-1">
            <el-option label="北京市" value="beijing" />
            <el-option label="上海市" value="shanghai" />
          </el-select>
        </div>
      </div>

      <!-- 详细地址 -->
      <div class="flex gap-4" v-if="formData.accountType === 'enterprise'">
        <label class="w-24 text-sm font-semibold text-slate-900 text-right pt-2">
          <span class="text-red-500">*</span> 详细地址
        </label>
        <div class="flex-1">
          <el-input v-model="formData.detailedAddress" type="textarea" placeholder="请输入详细地址" rows="2" />
        </div>
      </div>

      <!-- 分割线 -->
      <div class="border-t border-slate-200 pt-6">
        <h4 class="text-sm font-semibold text-slate-900 mb-4">为给您提供定制化的分销服务，烦请您完善以下资料：</h4>

        <!-- 公司名称 -->
        <div v-if="formData.accountType === 'enterprise'" class="flex gap-4 mb-6">
          <label class="w-24 text-sm font-semibold text-slate-900 text-right pt-2">
            <span class="text-red-500">*</span> 公司名称
          </label>
          <div class="flex-1">
            <el-input v-model="formData.companyName" placeholder="请输入 公司名称" />
          </div>
        </div>

        <!-- 公司主营业务 -->
        <div v-if="formData.accountType === 'enterprise'" class="flex gap-4 mb-6">
          <label class="w-24 text-sm font-semibold text-slate-900 text-right pt-2">
            <span class="text-red-500">*</span> 公���主营业务
          </label>
          <div class="flex-1 flex flex-wrap gap-4">
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="radio" v-model="formData.businessType" value="trade" class="w-4 h-4" />
              <span class="text-sm text-slate-700">贸易型卖家</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="radio" v-model="formData.businessType" value="factory" class="w-4 h-4" />
              <span class="text-sm text-slate-700">工厂型卖家</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="radio" v-model="formData.businessType" value="distribution" class="w-4 h-4" />
              <span class="text-sm text-slate-700">产品分销服务商</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="radio" v-model="formData.businessType" value="operation" class="w-4 h-4" />
              <span class="text-sm text-slate-700">代运营服务商</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="radio" v-model="formData.businessType" value="logistics" class="w-4 h-4" />
              <span class="text-sm text-slate-700">物流服务商</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="radio" v-model="formData.businessType" value="it" class="w-4 h-4" />
              <span class="text-sm text-slate-700">IT服务商</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="radio" v-model="formData.businessType" value="other" class="w-4 h-4" />
              <span class="text-sm text-slate-700">其他</span>
            </label>
          </div>
        </div>

        <!-- 公司规模 -->
        <div v-if="formData.accountType === 'enterprise'" class="flex gap-4 mb-6">
          <label class="w-24 text-sm font-semibold text-slate-900 text-right pt-2">
            <span class="text-red-500">*</span> 公司规模
          </label>
          <div class="flex-1 flex flex-wrap gap-4">
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="radio" v-model="formData.companySize" value="small" class="w-4 h-4" />
              <span class="text-sm text-slate-700">20人以内</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="radio" v-model="formData.companySize" value="medium" class="w-4 h-4" />
              <span class="text-sm text-slate-700">20-100人</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="radio" v-model="formData.companySize" value="large" class="w-4 h-4" />
              <span class="text-sm text-slate-700">100-500人</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="radio" v-model="formData.companySize" value="xlarge" class="w-4 h-4" />
              <span class="text-sm text-slate-700">500人以上</span>
            </label>
          </div>
        </div>

        <!-- 电商经验 -->
        <div class="flex gap-4 mb-6">
          <label class="w-24 text-sm font-semibold text-slate-900 text-right pt-2">
            <span class="text-red-500">*</span> 电商经验
          </label>
          <div class="flex-1 flex flex-wrap gap-4">
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="radio" v-model="formData.ecommerceExperience" value="none" class="w-4 h-4" />
              <span class="text-sm text-slate-700">无</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="radio" v-model="formData.ecommerceExperience" value="under1year" class="w-4 h-4" />
              <span class="text-sm text-slate-700">一年以下</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="radio" v-model="formData.ecommerceExperience" value="under3year" class="w-4 h-4" />
              <span class="text-sm text-slate-700">三年以内</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="radio" v-model="formData.ecommerceExperience" value="over3year" class="w-4 h-4" />
              <span class="text-sm text-slate-700">三年以上</span>
            </label>
          </div>
        </div>

        <!-- 接下来6个月计划 -->
        <div class="flex gap-4 mb-6">
          <label class="w-24 text-sm font-semibold text-slate-900 text-right pt-2">
            <span class="text-red-500">*</span> 接下来6个月
          </label>
          <div class="flex-1 flex flex-wrap gap-4">
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="radio" v-model="formData.sixMonthsPlan" value="yes" class="w-4 h-4" />
              <span class="text-sm text-slate-700">是</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="radio" v-model="formData.sixMonthsPlan" value="no" class="w-4 h-4" />
              <span class="text-sm text-slate-700">否</span>
            </label>
          </div>
        </div>

        <!-- 主营区域 (条件显示) -->
        <div v-if="formData.sixMonthsPlan === 'yes'" class="flex gap-4 mb-6">
          <label class="w-24 text-sm font-semibold text-slate-900 text-right pt-2">
            <span class="text-red-500">*</span> 主营区域
          </label>
          <div class="flex-1 flex flex-wrap gap-4">
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="checkbox" v-model="formData.mainRegions" value="usa" class="w-4 h-4" />
              <span class="text-sm text-slate-700">美国</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="checkbox" v-model="formData.mainRegions" value="uk" class="w-4 h-4" />
              <span class="text-sm text-slate-700">英国</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="checkbox" v-model="formData.mainRegions" value="germany" class="w-4 h-4" />
              <span class="text-sm text-slate-700">德国</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="checkbox" v-model="formData.mainRegions" value="france" class="w-4 h-4" />
              <span class="text-sm text-slate-700">法国</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="checkbox" v-model="formData.mainRegions" value="other" class="w-4 h-4" />
              <span class="text-sm text-slate-700">其他国家</span>
            </label>
          </div>
        </div>

        <!-- 主营平台 (条件显示) -->
        <div v-if="formData.sixMonthsPlan === 'yes'" class="flex gap-4 mb-6">
          <label class="w-24 text-sm font-semibold text-slate-900 text-right pt-2">
            <span class="text-red-500">*</span> 主营平台
          </label>
          <div class="flex-1 flex flex-wrap gap-4">
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="checkbox" v-model="formData.mainPlatforms" value="amazon" class="w-4 h-4" />
              <span class="text-sm text-slate-700">Amazon</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="checkbox" v-model="formData.mainPlatforms" value="ebay" class="w-4 h-4" />
              <span class="text-sm text-slate-700">Ebay</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="checkbox" v-model="formData.mainPlatforms" value="wish" class="w-4 h-4" />
              <span class="text-sm text-slate-700">Wish</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="checkbox" v-model="formData.mainPlatforms" value="walmart" class="w-4 h-4" />
              <span class="text-sm text-slate-700">Walmart</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="checkbox" v-model="formData.mainPlatforms" value="alibaba" class="w-4 h-4" />
              <span class="text-sm text-slate-700">Alibaba</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="checkbox" v-model="formData.mainPlatforms" value="aliexpress" class="w-4 h-4" />
              <span class="text-sm text-slate-700">Aliexpress</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="checkbox" v-model="formData.mainPlatforms" value="shopify" class="w-4 h-4" />
              <span class="text-sm text-slate-700">Shopify</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="checkbox" v-model="formData.mainPlatforms" value="temu" class="w-4 h-4" />
              <span class="text-sm text-slate-700">Temu</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="checkbox" v-model="formData.mainPlatforms" value="shein" class="w-4 h-4" />
              <span class="text-sm text-slate-700">Shein</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="checkbox" v-model="formData.mainPlatforms" value="tiktok" class="w-4 h-4" />
              <span class="text-sm text-slate-700">TikTok</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="checkbox" v-model="formData.mainPlatforms" value="wayfair" class="w-4 h-4" />
              <span class="text-sm text-slate-700">Wayfair</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="checkbox" v-model="formData.mainPlatforms" value="overstock" class="w-4 h-4" />
              <span class="text-sm text-slate-700">Overstock</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="checkbox" v-model="formData.mainPlatforms" value="other" class="w-4 h-4" />
              <span class="text-sm text-slate-700">其他</span>
            </label>
          </div>
        </div>

        <!-- 主营类目 (条件显示) -->
        <div v-if="formData.sixMonthsPlan === 'yes'" class="flex gap-4 mb-6">
          <label class="w-24 text-sm font-semibold text-slate-900 text-right pt-2">
            <span class="text-red-500">*</span> 主营类目
          </label>
          <div class="flex-1">
            <el-select v-model="formData.mainCategories" multiple placeholder="请选择" class="w-full">
              <el-option label="家居和园艺" value="home" />
              <el-option label="运动健身" value="sports" />
              <el-option label="乐器" value="music" />
              <el-option label="健康和美容" value="health" />
              <el-option label="消费电子" value="electronics" />
              <el-option label="工具类" value="tools" />
              <el-option label="宠物用品" value="pets" />
              <el-option label="工艺艺术" value="craft" />
              <el-option label="玩具/儿童用品" value="toys" />
              <el-option label="汽摩配件" value="auto" />
              <el-option label="相机类" value="camera" />
              <el-option label="发光照明" value="lighting" />
            </el-select>
          </div>
        </div>

        <!-- 运营策略 (条件显示) -->
        <div v-if="formData.sixMonthsPlan === 'yes'" class="flex gap-4 mb-6">
          <label class="w-24 text-sm font-semibold text-slate-900 text-right pt-2">运营策略</label>
          <div class="flex-1 flex flex-wrap gap-4">
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="radio" v-model="formData.operationStrategy" value="bulk" class="w-4 h-4" />
              <span class="text-sm text-slate-700">铺货</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="radio" v-model="formData.operationStrategy" value="refined" class="w-4 h-4" />
              <span class="text-sm text-slate-700">精细化运营</span>
            </label>
          </div>
        </div>

        <!-- 店铺链接 (条件显示) -->
        <div v-if="formData.sixMonthsPlan === 'yes'" class="flex gap-4 mb-6">
          <label class="w-24 text-sm font-semibold text-slate-900 text-right pt-2">店铺链接</label>
          <div class="flex-1">
            <el-input v-model="formData.storeLink" placeholder="请输入 店铺链接" />
          </div>
        </div>

        <!-- 联系QQ -->
        <div class="flex gap-4 mb-6">
          <label class="w-24 text-sm font-semibold text-slate-900 text-right pt-2">联系QQ</label>
          <div class="flex-1">
            <el-input v-model="formData.qq" placeholder="请输入 联系QQ" />
          </div>
        </div>

        <!-- 联系微信 -->
        <div class="flex gap-4 mb-6">
          <label class="w-24 text-sm font-semibold text-slate-900 text-right pt-2">联系微信</label>
          <div class="flex-1">
            <el-input v-model="formData.wechat" placeholder="请输入 联系微信" />
          </div>
        </div>
      </div>

      <!-- 按钮 -->
      <div class="flex gap-2 pt-6 ml-28">
        <el-button>保存</el-button>
        <el-button type="primary">下一步</el-button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { reactive } from 'vue'

const formData = reactive({
  accountType: 'enterprise',
  firstName: '红元',
  lastName: '金',
  province: 'beijing',
  city: 'beijing',
  detailedAddress: '',
  companyName: '',
  businessType: '',
  companySize: '',
  ecommerceExperience: 'none',
  sixMonthsPlan: 'yes',
  mainRegions: ['usa'],
  mainPlatforms: ['amazon', 'ebay'],
  mainCategories: [],
  operationStrategy: 'bulk',
  storeLink: '',
  qq: '2353',
  wechat: '3535353',
})
</script>

<style scoped></style>
