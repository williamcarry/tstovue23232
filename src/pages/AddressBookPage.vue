<template>
  <div class="bg-white rounded-lg border border-slate-200">
    <!-- 标题 -->
    <div class="h-12 border-b border-slate-200 px-6 flex items-center">
      <h3 class="text-base font-semibold text-slate-900">地址簿</h3>
    </div>

    <!-- 选项卡 -->
    <div class="flex gap-0 border-b border-slate-200 px-6">
      <button
        @click="activeTab = 'shipping'"
        :class="[
          'px-4 py-3 font-medium border-b-2 transition cursor-pointer',
          activeTab === 'shipping'
            ? 'text-primary border-primary'
            : 'text-slate-700 border-transparent hover:text-slate-900'
        ]"
      >
        收货地址
      </button>
      <button
        @click="activeTab = 'billing'"
        :class="[
          'px-4 py-3 font-medium border-b-2 transition cursor-pointer',
          activeTab === 'billing'
            ? 'text-primary border-primary'
            : 'text-slate-700 border-transparent hover:text-slate-900'
        ]"
      >
        账单地址
      </button>
    </div>

    <!-- 内容区域 -->
    <div class="p-6">
      <!-- 操作按钮 -->
      <div class="flex items-center gap-3 mb-6">
        <el-button text type="danger" @click="handleDeleteSelected">删除地址</el-button>
        <el-button type="primary" @click="handleAddAddress">添加新地址</el-button>
        <p class="text-sm text-slate-600 ml-auto">
          您已创建
          <em class="font-semibold text-slate-900">{{ addresses.length }}</em>
          个{{ activeTab === 'shipping' ? '收货' : '账单' }}地址，最多可创建50个
        </p>
      </div>

      <!-- 地址表格 -->
      <el-table :data="addresses" style="width: 100%" @selection-change="handleSelectionChange">
        <el-table-column type="selection" width="50" />
        <el-table-column prop="recipient" label="收件人" width="120" />
        <el-table-column prop="address" label="收货地址" min-width="200" />
        <el-table-column prop="city" label="城市" width="100" />
        <el-table-column prop="province" label="省/州" width="100" />
        <el-table-column prop="country" label="国家/地区" width="120" />
        <el-table-column prop="postalCode" label="邮编" width="100" />
        <el-table-column prop="phone" label="电话" width="140" />
        <el-table-column label="操作" width="150" align="center">
          <template #default="{ row }">
            <el-button link type="primary" size="small" @click="handleEditAddress(row)">编辑</el-button>
            <el-button link type="danger" size="small" @click="handleDeleteAddress(row.id)">删除</el-button>
          </template>
        </el-table-column>
      </el-table>
    </div>

    <!-- 新增/编辑地址对话框 -->
    <el-dialog v-model="dialogVisible" :title="isEditing ? '编辑地址' : '添加新地址'" width="520px">
      <el-form :model="formData" label-width="120px" size="large">
        <!-- 收件人（必填） -->
        <el-form-item label="收件人：" required>
          <el-input v-model="formData.recipient" placeholder="" />
        </el-form-item>

        <!-- 电话号码 -->
        <el-form-item label="电话号码：">
          <el-input v-model="formData.phone" placeholder="1234567890" />
        </el-form-item>

        <!-- 电子邮箱 -->
        <el-form-item label="电子邮箱：">
          <el-input v-model="formData.email" placeholder="用于欧洲订单物流跟踪联系" />
        </el-form-item>

        <!-- 街道地址（必填） -->
        <el-form-item label="街道地址：" required>
          <el-input v-model="formData.address" placeholder="" />
        </el-form-item>

        <!-- 街道地址2 -->
        <el-form-item label="街道地址2：">
          <el-input v-model="formData.address2" placeholder="" />
        </el-form-item>

        <!-- 国家/地区（必填） -->
        <el-form-item label="国家/地区：" required>
          <el-select v-model="formData.country" placeholder="请选择国家/地区">
            <el-option label="United States" value="United States" />
            <el-option label="Canada" value="Canada" />
            <el-option label="United Kingdom" value="United Kingdom" />
            <el-option label="Australia" value="Australia" />
            <el-option label="Germany" value="Germany" />
            <el-option label="France" value="France" />
            <el-option label="Japan" value="Japan" />
            <el-option label="China" value="China" />
          </el-select>
        </el-form-item>

        <!-- 省份 -->
        <el-form-item label="省份：">
          <el-input v-model="formData.province" placeholder="" />
        </el-form-item>

        <!-- 城市（必填） -->
        <el-form-item label="城市：" required>
          <el-input v-model="formData.city" placeholder="" />
        </el-form-item>

        <!-- 邮编编码（必填） -->
        <el-form-item label="邮编编码：" required>
          <el-input v-model="formData.postalCode" placeholder="" />
        </el-form-item>
      </el-form>

      <template #footer>
        <div style="text-align: center">
          <el-button type="primary" @click="handleSaveAddress" style="min-width: 80px">保存</el-button>
          <el-button @click="dialogVisible = false" style="min-width: 80px">取消</el-button>
        </div>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { ElMessage } from 'element-plus'

const activeTab = ref('shipping')
const dialogVisible = ref(false)
const isEditing = ref(false)
const selectedIds = ref([])
const editingId = ref(null)

const formData = reactive({
  recipient: '',
  phone: '',
  email: '',
  address: '',
  address2: '',
  country: '',
  province: '',
  city: '',
  postalCode: '',
})

// 示例地址数据
const allAddresses = ref([
  {
    id: 1,
    recipient: '张三',
    address: '123 Main Street, Apartment 4B',
    city: 'New York',
    province: 'New York',
    country: 'United States',
    postalCode: '10001',
    phone: '+1-555-0101',
    type: 'shipping',
  },
  {
    id: 2,
    recipient: '李四',
    address: '456 Oak Avenue, Suite 200',
    city: 'Los Angeles',
    province: 'California',
    country: 'United States',
    postalCode: '90001',
    phone: '+1-555-0102',
    type: 'shipping',
  },
  {
    id: 3,
    recipient: '王五',
    address: '789 Pine Road, Building C',
    city: 'Chicago',
    province: 'Illinois',
    country: 'United States',
    postalCode: '60601',
    phone: '+1-555-0103',
    type: 'shipping',
  },
  {
    id: 4,
    recipient: '赵六',
    address: '101 Elm Street, Floor 3',
    city: 'Houston',
    province: 'Texas',
    country: 'United States',
    postalCode: '77001',
    phone: '+1-555-0104',
    type: 'billing',
  },
])

const addresses = computed(() =>
  allAddresses.value.filter((addr) => addr.type === activeTab.value),
)

function resetForm() {
  formData.recipient = ''
  formData.phone = ''
  formData.email = ''
  formData.address = ''
  formData.address2 = ''
  formData.country = ''
  formData.province = ''
  formData.city = ''
  formData.postalCode = ''
  editingId.value = null
  isEditing.value = false
}

function handleAddAddress() {
  resetForm()
  dialogVisible.value = true
}

function handleEditAddress(row) {
  formData.recipient = row.recipient
  formData.phone = row.phone
  formData.email = row.email || ''
  formData.address = row.address
  formData.address2 = row.address2 || ''
  formData.country = row.country
  formData.province = row.province
  formData.city = row.city
  formData.postalCode = row.postalCode
  editingId.value = row.id
  isEditing.value = true
  dialogVisible.value = true
}

function handleSaveAddress() {
  if (!formData.recipient || !formData.address || !formData.city || !formData.country || !formData.postalCode) {
    ElMessage.warning('请填写所有必填项')
    return
  }

  if (isEditing.value && editingId.value) {
    const index = allAddresses.value.findIndex((addr) => addr.id === editingId.value)
    if (index > -1) {
      allAddresses.value[index] = {
        ...allAddresses.value[index],
        recipient: formData.recipient,
        address: formData.address,
        city: formData.city,
        province: formData.province,
        country: formData.country,
        postalCode: formData.postalCode,
        phone: formData.phone,
        ...(formData.email && { email: formData.email }),
        ...(formData.address2 && { address2: formData.address2 }),
      }
    }
    ElMessage.success('地址已更新')
  } else {
    const newId = Math.max(...allAddresses.value.map((a) => a.id), 0) + 1
    allAddresses.value.push({
      id: newId,
      recipient: formData.recipient,
      address: formData.address,
      city: formData.city,
      province: formData.province,
      country: formData.country,
      postalCode: formData.postalCode,
      phone: formData.phone,
      ...(formData.email && { email: formData.email }),
      ...(formData.address2 && { address2: formData.address2 }),
      type: activeTab.value,
    })
    ElMessage.success('地址已添加')
  }

  dialogVisible.value = false
  resetForm()
}

function handleDeleteAddress(id) {
  const index = allAddresses.value.findIndex((addr) => addr.id === id)
  if (index > -1) {
    allAddresses.value.splice(index, 1)
    ElMessage.success('地址已删除')
  }
}

function handleDeleteSelected() {
  if (selectedIds.value.length === 0) {
    ElMessage.warning('请先选择要删除的地址')
    return
  }

  allAddresses.value = allAddresses.value.filter((addr) => !selectedIds.value.includes(addr.id))
  selectedIds.value = []
  ElMessage.success('所选地址已删除')
}

function handleSelectionChange(selection) {
  selectedIds.value = selection.map((item) => item.id)
}
</script>

<style scoped></style>