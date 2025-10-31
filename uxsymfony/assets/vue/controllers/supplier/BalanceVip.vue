<template>
  <div class="balance-vip-container">
    <!-- 余额统计卡片 -->
    <el-row :gutter="20" class="balance-cards">
      <el-col :span="8">
        <el-card class="balance-card available-balance">
          <div class="card-icon">
            <el-icon :size="48" color="#52c41a">
              <Wallet />
            </el-icon>
          </div>
          <div class="card-content">
            <div class="card-label">可用余额</div>
            <div class="card-value">{{ formatCurrency(balanceData.balance) }}</div>
          </div>
        </el-card>
      </el-col>
      <el-col :span="8">
        <el-card class="balance-card frozen-balance">
          <div class="card-icon">
            <el-icon :size="48" color="#faad14">
              <Lock />
            </el-icon>
          </div>
          <div class="card-content">
            <div class="card-label">冻结余额</div>
            <div class="card-value frozen">{{ formatCurrency(balanceData.balanceFrozen) }}</div>
          </div>
        </el-card>
      </el-col>
      <el-col :span="8">
        <el-card class="balance-card withdraw-card">
          <div class="card-icon">
            <el-icon :size="48" color="#faad14">
              <Download />
            </el-icon>
          </div>
          <div class="card-content">
            <div class="card-label">快速操作</div>
            <el-button @click="showWithdrawDialog" class="withdraw-button">
              <el-icon style="margin-right: 8px;"><Download /></el-icon>
              申请提现
            </el-button>
          </div>
        </el-card>
      </el-col>
    </el-row>
    
    <!-- 会员信息卡片 -->
    <el-card class="membership-info-card">
      <template #header>
        <div class="card-header">
          <span class="header-title">
            <el-icon style="margin-right: 8px;"><TrophyBase /></el-icon>
            会员信息
          </span>
        </div>
      </template>
      <el-row :gutter="20">
        <el-col :span="8">
          <div class="info-item">
            <div class="info-label">会员类型</div>
            <div class="info-value" :class="{ 'vip-active': balanceData.isMemberActive }">
              {{ membershipTypeName }}
            </div>
          </div>
        </el-col>
        <el-col :span="8">
          <div class="info-item">
            <div class="info-label">到期时间</div>
            <div class="info-value">{{ membershipExpiredAt }}</div>
          </div>
        </el-col>
        <el-col :span="8">
          <div class="info-item">
            <div class="info-label">佣金比例</div>
            <div class="info-value" :class="{ 'commission-free': balanceData.isMemberActive }">
              {{ commissionRateText }}
            </div>
          </div>
        </el-col>
      </el-row>
      
      <!-- 会员等级规则说明 -->
      <div class="membership-rules">
        <div class="rules-header">
          <el-icon class="rules-icon"><InfoFilled /></el-icon>
          <span class="rules-title">会员规则说明</span>
        </div>
        <div class="rules-content">
          有效期内只能升级不能降级，可购买任意等级会员延长有效期，过期后可重新选择任意等级。
        </div>
      </div>
    </el-card>
    
    <!-- 会员套餐选择 -->
    <el-card class="membership-plans-card">
      <template #header>
        <div class="card-header">
          <span class="header-title">
            <el-icon style="margin-right: 8px;"><TrophyBase /></el-icon>
            会员套餐
          </span>
          <span class="header-subtitle">开通会员，尊享特权</span>
        </div>
      </template>
      <el-row :gutter="20">
        <el-col :span="6" v-for="plan in membershipPlans" :key="plan.type">
          <div 
            class="membership-plan" 
            :class="[
              { 'active': selectedMembershipPlan === plan.type },
              `plan-${plan.type}`
            ]"
            @click="selectMembershipPlan(plan.type)"
          >
            <div class="plan-badge" v-if="plan.type === 'yearly'">推荐</div>
            <div class="plan-header">
              <div class="plan-name">{{ plan.name }}</div>
              <div class="plan-price">
                <span class="currency">¥</span>
                <span class="amount">{{ plan.price }}</span>
              </div>
            </div>
            <el-button 
              class="plan-button"
              type="primary"
              size="large"
              @click.stop="confirmPurchaseMembership(plan.type)"
              :loading="purchasingPlan === plan.type"
            >
              立即开通
            </el-button>
            <div class="plan-benefits">
              <div class="benefit" v-for="benefit in plan.benefits" :key="benefit">
                <el-icon color="#52c41a" class="check-icon"><CircleCheck /></el-icon>
                <span>{{ benefit }}</span>
              </div>
            </div>
          </div>
        </el-col>
      </el-row>
    </el-card>
    
    <!-- 提现对话框 -->
    <el-dialog v-model="withdrawDialogVisible" title="申请提现" width="500px">
      <el-form :model="withdrawForm" ref="withdrawFormRef" :rules="withdrawRules" label-width="100px">
        <el-form-item label="提现金额" prop="amount">
          <el-input v-model.number="withdrawForm.amount" placeholder="请输入提现金额" @input="formatAmount">
            <template #append>元</template>
          </el-input>
        </el-form-item>
        <el-form-item label="可用余额">
          <span>{{ formatCurrency(balanceData.balance) }}</span>
        </el-form-item>
        <el-form-item label="收款信息" prop="paymentInfo">
          <el-input 
            v-model="withdrawForm.paymentInfo" 
            type="textarea" 
            :rows="5"
            placeholder="请填写收款信息，包括收款人、银行账号、开户银行等"
          />
        </el-form-item>
        <el-alert
          title="提示：请确保收款信息准确无误，以免影响提现到账"
          type="warning"
          :closable="false"
          style="margin-bottom: 15px;"
        />
      </el-form>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="withdrawDialogVisible = false">取消</el-button>
          <el-button type="primary" @click="submitWithdraw" :loading="withdrawLoading">确认</el-button>
        </span>
      </template>
    </el-dialog>
    
    <!-- 会员购买确认对话框 -->
    <el-dialog v-model="purchaseConfirmDialogVisible" title="确认购买会员" width="500px">
      <div v-if="purchaseConfirmData">
        <p>您即将购买 <strong>{{ purchaseConfirmData.membershipName }}</strong></p>
        <p>价格：<strong>¥{{ purchaseConfirmData.price }}</strong></p>
        <div v-if="purchaseConfirmData.isMemberActive">
          <p>您当前是 <strong>{{ membershipTypeMap[purchaseConfirmData.currentMembershipType] }}</strong>，有效期至 {{ purchaseConfirmData.currentMembershipExpiredAt }}</p>
          <p>本次购买将延长您的会员有效期。</p>
        </div>
        <div v-else>
          <p v-if="purchaseConfirmData.currentMembershipType && purchaseConfirmData.currentMembershipType !== 'none'">
            您当前是 <strong>{{ membershipTypeMap[purchaseConfirmData.currentMembershipType] }}</strong>，但已过期。
          </p>
          <p v-else>
            您当前没有会员。
          </p>
          <p>本次购买将为您开通新的会员。</p>
        </div>
        <p>购买后将从您的余额中扣除 <strong>¥{{ purchaseConfirmData.price }}</strong></p>
      </div>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="purchaseConfirmDialogVisible = false">取消</el-button>
          <el-button type="primary" @click="purchaseMembership" :loading="purchasingPlan !== ''">确认购买</el-button>
        </span>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, onMounted, defineExpose, computed } from 'vue'
import {
  ElCard,
  ElRow,
  ElCol,
  ElButton,
  ElDialog,
  ElForm,
  ElFormItem,
  ElInput,
  ElMessage,
  ElMessageBox,
  ElTag,
  ElAlert,
  ElIcon
} from 'element-plus'
import {
  Wallet,
  Lock,
  Download,
  TrophyBase,
  InfoFilled,
  CircleCheck
} from '@element-plus/icons-vue'

const props = defineProps({
  supplierLoginPath: {
    type: String,
    default: ''
  },
  // 添加autoLoad属性以支持按需加载
  autoLoad: {
    type: Boolean,
    default: false
  }
})

// 数据
const balanceData = ref({
  balance: '0.00',
  balanceFrozen: '0.00',
  membershipType: 'none',
  membershipExpiredAt: null,
  commissionRate: null,
  commissionRatePercentage: null,
  originalCommissionRate: null,
  originalCommissionRatePercentage: null,
  isMemberActive: false,
  membershipPrices: {},
  defaultPaymentInfo: '' // 默认收款信息
})

// 提现对话框
const withdrawDialogVisible = ref(false)
const withdrawLoading = ref(false)
const withdrawForm = ref({
  amount: '',
  paymentInfo: '' // 收款信息
})
const withdrawFormRef = ref(null)

// 会员购买
const selectedMembershipPlan = ref('')
const purchasingPlan = ref('')
const purchaseConfirmDialogVisible = ref(false)
const purchaseConfirmData = ref(null)

// 会员类型映射
const membershipTypeMap = {
  'none': '无会员',
  'monthly': '月会员',
  'quarterly': '季度会员',
  'half_yearly': '半年会员',
  'yearly': '年会员'
}

// 会员计划
const membershipPlans = computed(() => {
  const prices = balanceData.value.membershipPrices || {};
  return [
    {
      type: 'monthly',
      name: '月会员',
      price: prices.monthly || '...',
      benefits: ['免佣金', '优先客服', '专属活动']
    },
    {
      type: 'quarterly',
      name: '季度会员',
      price: prices.quarterly || '...',
      benefits: ['免佣金', '优先客服', '专属活动', '月度报告']
    },
    {
      type: 'half_yearly',
      name: '半年会员',
      price: prices.half_yearly || '...',
      benefits: ['免佣金', '优先客服', '专属活动', '月度报告', '定制服务']
    },
    {
      type: 'yearly',
      name: '年会员',
      price: prices.yearly || '...',
      benefits: ['免佣金', '优先客服', '专属活动', '月度报告', '定制服务', '一对一顾问']
    }
  ];
})

// 提现表单验证规则
const withdrawRules = {
  amount: [
    { required: true, message: '请输入提现金额', trigger: 'blur' },
    { type: 'number', min: 0.01, message: '提现金额必须大于0', trigger: 'blur' },
    { 
      validator: (rule, value, callback) => {
        if (value && !/^\d+(\.\d{1,2})?$/.test(value.toString())) {
          callback(new Error('提现金额最多保留两位小数'));
        } else {
          callback();
        }
      }, 
      trigger: 'blur' 
    }
  ],
  paymentInfo: [
    { required: true, message: '请填写收款信息', trigger: 'blur' },
    { min: 5, message: '收款信息至少5个字符', trigger: 'blur' }
  ]
}

// 计算属性
const membershipTypeName = computed(() => {
  return membershipTypeMap[balanceData.value.membershipType] || '无会员'
})

const membershipExpiredAt = computed(() => {
  if (balanceData.value.membershipExpiredAt) {
    return balanceData.value.membershipExpiredAt
  }
  return '未开通'
})

const commissionRateText = computed(() => {
  // 检查是否为活跃会员
  if (balanceData.value.isMemberActive) {
    return '免佣金（VIP特权）'
  }
  
  // 如果有有效的佣金比例，显示该比例
  if (balanceData.value.commissionRatePercentage !== null) {
    return balanceData.value.commissionRatePercentage + '%'
  }
  
  // 如果会员不活跃但有自定义佣金比例，显示自定义比例
  if (balanceData.value.originalCommissionRate !== null) {
    const rate = parseFloat(balanceData.value.originalCommissionRate) * 100;
    return rate.toFixed(2) + '%';
  }
  
  // 如果都没有设置，显示未设置
  return '未设置'
})

// 格式化货币
const formatCurrency = (value) => {
  if (value === null || value === undefined) return '¥0.00'
  return '¥' + parseFloat(value).toFixed(2)
}

// 格式化金额，保留两位小数
const formatAmount = (value) => {
  if (value && typeof value === 'number') {
    withdrawForm.value.amount = parseFloat(value.toFixed(2));
  }
}

// 加载数据
const loadBalanceVipData = async () => {
  try {
    const response = await fetch(`/supplier${props.supplierLoginPath}/balance-vip/data`, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
      }
    })
    
    const result = await response.json()
    
    if (result.success) {
      // 更新balanceData，确保包含membershipPrices
      balanceData.value = {
        ...balanceData.value,
        ...result.data,
        membershipPrices: result.data.membershipPrices || {}
      }
    } else {
      ElMessage.error(result.message || '获取数据失败')
    }
  } catch (error) {
    ElMessage.error('网络错误：' + error.message)
  }
}

// 显示提现对话框
const showWithdrawDialog = () => {
  withdrawForm.value.amount = ''
  // 填充默认收款信息
  withdrawForm.value.paymentInfo = balanceData.value.defaultPaymentInfo || ''
  withdrawDialogVisible.value = true
}

// 提交提现申请
const submitWithdraw = async () => {
  if (!withdrawFormRef.value) return
  
  await withdrawFormRef.value.validate(async (valid) => {
    if (valid) {
      withdrawLoading.value = true
      try {
        // 确保金额格式化为两位小数
        const amount = parseFloat(withdrawForm.value.amount).toFixed(2);
        
        const response = await fetch(`/supplier${props.supplierLoginPath}/balance-vip/withdraw`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            amount: amount,
            paymentInfo: withdrawForm.value.paymentInfo.trim()
          })
        })
        
        const result = await response.json()
        
        if (result.success) {
          ElMessage.success(result.message)
          withdrawDialogVisible.value = false
          // 重新加载数据
          await loadBalanceVipData()
        } else {
          ElMessage.error(result.message || '提现失败')
        }
      } catch (error) {
        ElMessage.error('网络错误：' + error.message)
      } finally {
        withdrawLoading.value = false
      }
    }
  })
}

// 选择会员计划
const selectMembershipPlan = (planType) => {
  selectedMembershipPlan.value = planType
}

// 确认购买会员
const confirmPurchaseMembership = async (planType) => {
  purchasingPlan.value = planType
  try {
    const response = await fetch(`/supplier${props.supplierLoginPath}/balance-vip/purchase-membership-confirm`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        membershipType: planType
      })
    })
    
    const result = await response.json()
    
    if (result.success) {
      purchaseConfirmData.value = result.data
      purchaseConfirmDialogVisible.value = true
    } else {
      ElMessage.error(result.message || '获取确认信息失败')
    }
  } catch (error) {
    ElMessage.error('网络错误：' + error.message)
  } finally {
    purchasingPlan.value = ''
  }
}

// 购买会员
const purchaseMembership = async () => {
  if (!purchaseConfirmData.value) return
  
  const planType = purchaseConfirmData.value.membershipType
  purchasingPlan.value = planType
  purchaseConfirmDialogVisible.value = false
  
  try {
    const response = await fetch(`/supplier${props.supplierLoginPath}/balance-vip/purchase-membership`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        membershipType: planType
      })
    })
    
    const result = await response.json()
    
    if (result.success) {
      ElMessage.success(result.message)
      // 重新加载数据
      await loadBalanceVipData()
    } else {
      ElMessage.error(result.message || '购买失败')
    }
  } catch (error) {
    ElMessage.error('网络错误：' + error.message)
  } finally {
    purchasingPlan.value = ''
    purchaseConfirmData.value = null
  }
}

// 暴露方法给父组件
defineExpose({
  loadBalanceVipData
})

// 组件挂载时根据autoLoad属性决定是否自动加载数据
onMounted(() => {
  if (props.autoLoad) {
    loadBalanceVipData()
  }
})
</script>



<style scoped>
.balance-vip-container {
  padding: 24px;
  max-width: 1400px;
  margin: 0 auto;
}

/* 余额卡片 */
.balance-cards {
  margin-bottom: 24px;
}

.balance-card {
  cursor: pointer;
  transition: all 0.3s ease;
  border-radius: 12px;
  overflow: hidden;
  min-height: 140px;
}

.balance-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
}

.balance-card :deep(.el-card__body) {
  display: flex;
  align-items: center;
  padding: 24px;
  gap: 20px;
}

.available-balance {
  background: linear-gradient(135deg, #f0f9ff 0%, #e6f7ff 100%);
  border: 1px solid #91d5ff;
}

.frozen-balance {
  background: linear-gradient(135deg, #fffbe6 0%, #fff7e6 100%);
  border: 1px solid #ffe58f;
}

.withdraw-card {
  background: linear-gradient(135deg, #fffbe6 0%, #fff7e6 100%);
  border: 1px solid #ffe58f;
}

.card-icon {
  flex-shrink: 0;
}

.card-content {
  flex: 1;
}

.card-label {
  font-size: 14px;
  color: #606266;
  margin-bottom: 12px;
  font-weight: 500;
}

.card-value {
  font-size: 32px;
  font-weight: 700;
  color: #52c41a;
  line-height: 1.2;
}

.card-value.frozen {
  color: #faad14;
}

.withdraw-button {
  width: 100%;
  height: 48px;
  font-size: 16px;
  font-weight: 600;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #faad14 0%, #fa8c16 100%);
  border: none;
  color: white;
  transition: all 0.3s;
}

.withdraw-button:hover {
  background: linear-gradient(135deg, #ffc53d 0%, #faad14 100%);
  box-shadow: 0 6px 16px rgba(250, 173, 20, 0.4);
  transform: translateY(-2px);
}

.withdraw-button:active {
  transform: translateY(0);
}

/* 会员信息卡片 */
.membership-info-card {
  margin-bottom: 24px;
  border-radius: 12px;
}

.membership-info-card :deep(.el-card__header) {
  background: linear-gradient(135deg, #f9f0ff 0%, #f0e6ff 100%);
  border-bottom: 1px solid #d3adf7;
  padding: 20px 24px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.header-title {
  font-size: 18px;
  font-weight: 600;
  color: #303133;
  display: flex;
  align-items: center;
}

.header-subtitle {
  font-size: 14px;
  color: #909399;
}

.info-item {
  text-align: center;
  padding: 20px;
  background: #fafafa;
  border-radius: 8px;
  transition: all 0.3s;
}

.info-item:hover {
  background: #f0f0f0;
}

.info-label {
  font-size: 14px;
  color: #909399;
  margin-bottom: 12px;
}

.info-value {
  font-size: 20px;
  font-weight: 600;
  color: #303133;
}

.info-value.vip-active {
  color: #52c41a;
}

.info-value.commission-free {
  color: #52c41a;
  font-weight: 700;
}

.no-vip {
  color: #909399;
}

/* 会员等级规则说明 */
.membership-rules {
  background: linear-gradient(135deg, #f0f9ff 0%, #e6f7ff 100%);
  border: 1px solid #91d5ff;
  border-radius: 12px;
  padding: 20px;
  margin-top: 20px;
  box-shadow: 0 4px 12px rgba(24, 144, 255, 0.1);
  transition: all 0.3s ease;
}

.membership-rules:hover {
  box-shadow: 0 6px 16px rgba(24, 144, 255, 0.15);
  transform: translateY(-2px);
}

.rules-header {
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 12px;
}

.rules-icon {
  font-size: 20px;
  color: #1890ff;
  margin-right: 8px;
}

.rules-title {
  font-size: 18px;
  font-weight: 600;
  color: #303133;
}

.rules-content {
  text-align: center;
  font-size: 14px;
  color: #606266;
  line-height: 1.6;
  padding: 0 10px;
}

/* 会员套餐卡片 */
.membership-plans-card {
  border-radius: 12px;
}

.membership-plans-card :deep(.el-card__header) {
  background: linear-gradient(135deg, #fff7e6 0%, #fff1db 100%);
  border-bottom: 1px solid #ffd591;
  padding: 20px 24px;
}

.membership-plan {
  position: relative;
  background: #ffffff;
  border: 2px solid #e8e8e8;
  border-radius: 16px;
  padding: 28px 24px;
  transition: all 0.3s ease;
  cursor: pointer;
  height: 100%;
  display: flex;
  flex-direction: column;
}

.membership-plan:hover {
  border-color: #1890ff;
  box-shadow: 0 8px 24px rgba(24, 144, 255, 0.15);
  transform: translateY(-6px);
}

.membership-plan.active {
  border-color: #1890ff;
  box-shadow: 0 8px 32px rgba(24, 144, 255, 0.25);
  background: linear-gradient(135deg, #e6f7ff 0%, #f0f9ff 100%);
}

.plan-badge {
  position: absolute;
  top: -1px;
  right: 24px;
  background: linear-gradient(135deg, #fa8c16 0%, #fa541c 100%);
  color: #fff;
  padding: 4px 16px;
  border-radius: 0 0 12px 12px;
  font-size: 12px;
  font-weight: 600;
  box-shadow: 0 4px 8px rgba(250, 140, 22, 0.3);
}

.plan-header {
  text-align: center;
  margin-bottom: 24px;
  padding-bottom: 20px;
  border-bottom: 2px solid #f0f0f0;
}

.plan-name {
  font-size: 22px;
  font-weight: 700;
  color: #303133;
  margin-bottom: 16px;
}

.plan-price {
  display: flex;
  align-items: baseline;
  justify-content: center;
  gap: 4px;
}

.currency {
  font-size: 20px;
  font-weight: 600;
  color: #fa8c16;
}

.amount {
  font-size: 40px;
  font-weight: 700;
  color: #fa8c16;
  line-height: 1;
}

.plan-button {
  width: 100%;
  height: 48px;
  font-size: 16px;
  font-weight: 600;
  border-radius: 24px;
  margin-bottom: 24px;
  background: linear-gradient(135deg, #1890ff 0%, #096dd9 100%);
  border: none;
  transition: all 0.3s;
}

.plan-button:hover {
  background: linear-gradient(135deg, #40a9ff 0%, #1890ff 100%);
  box-shadow: 0 6px 16px rgba(24, 144, 255, 0.4);
  transform: translateY(-2px);
}

.plan-yearly .plan-button {
  background: linear-gradient(135deg, #fa8c16 0%, #fa541c 100%);
}

.plan-yearly .plan-button:hover {
  background: linear-gradient(135deg, #ffa940 0%, #fa8c16 100%);
  box-shadow: 0 6px 16px rgba(250, 140, 22, 0.4);
}

.plan-benefits {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.benefit {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 14px;
  color: #606266;
  line-height: 1.6;
}

.check-icon {
  flex-shrink: 0;
}

.dialog-footer {
  text-align: right;
}
</style>
