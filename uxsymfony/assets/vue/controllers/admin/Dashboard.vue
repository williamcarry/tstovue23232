<template>
  <div class="admin-dashboard">
    <el-row :gutter="20">
      <el-col :span="24">
        <el-card class="welcome-card">
          <div class="welcome-content">
            <h1>{{ dashboardTitle }}</h1>
            <p>欢迎，{{ userName }}!</p>
          </div>
        </el-card>
      </el-col>
    </el-row>
    
    <el-row :gutter="20" class="stats-row">
      <el-col :span="8">
        <el-card class="stat-card">
          <div class="stat-content">
            <h3>用户总数</h3>
            <p class="stat-number">1,234</p>
          </div>
        </el-card>
      </el-col>
      <el-col :span="8">
        <el-card class="stat-card">
          <div class="stat-content">
            <h3>订单总数</h3>
            <p class="stat-number">5,678</p>
          </div>
        </el-card>
      </el-col>
      <el-col :span="8">
        <el-card class="stat-card">
          <div class="stat-content">
            <h3>产品总数</h3>
            <p class="stat-number">9,012</p>
          </div>
        </el-card>
      </el-col>
    </el-row>
    
    <el-row :gutter="20">
      <el-col :span="24">
        <el-card class="activity-card">
          <template #header>
            <div class="card-header">
              <span>最近活动</span>
            </div>
          </template>
          <el-timeline>
            <el-timeline-item
              v-for="(activity, index) in activities"
              :key="index"
              :timestamp="activity.timestamp"
              placement="top"
            >
              <el-card>
                <h4>{{ activity.content }}</h4>
              </el-card>
            </el-timeline-item>
          </el-timeline>
        </el-card>
      </el-col>
    </el-row>
  </div>
</template>

<script>
import {
  ElRow,
  ElCol,
  ElCard,
  ElTimeline,
  ElTimelineItem
} from 'element-plus'

export default {
  name: 'AdminDashboard',
  components: {
    ElRow,
    ElCol,
    ElCard,
    ElTimeline,
    ElTimelineItem
  },
  props: {
    dashboardTitle: {
      type: String,
      required: true
    },
    userName: {
      type: String,
      required: true
    }
  },
  data() {
    return {
      activities: [
        {
          content: '用户 John Doe 注册了账户',
          timestamp: '2023-06-01'
        },
        {
          content: '订单 #12345 已完成',
          timestamp: '2023-06-02'
        },
        {
          content: '新产品 "示例产品" 已添加',
          timestamp: '2023-06-03'
        }
      ]
    }
  }
}
</script>

<style scoped>
.admin-dashboard {
  padding: 20px;
  background-color: #f5f7fa;
  min-height: 100vh;
}

.welcome-card {
  margin-bottom: 20px;
}

.welcome-content h1 {
  margin: 0 0 10px 0;
  color: #303133;
}

.welcome-content p {
  margin: 0;
  color: #606266;
  font-size: 16px;
}

.stats-row {
  margin-bottom: 20px;
}

.stat-card {
  border: none;
  box-shadow: 0 2px 12px 0 rgba(0, 0, 0, 0.1);
}

.stat-content {
  text-align: center;
}

.stat-content h3 {
  margin: 0 0 10px 0;
  color: #606266;
  font-size: 16px;
}

.stat-number {
  margin: 0;
  font-size: 24px;
  font-weight: bold;
  color: #409eff;
}

.activity-card {
  border: none;
  box-shadow: 0 2px 12px 0 rgba(0, 0, 0, 0.1);
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.card-header span {
  font-weight: bold;
  color: #303133;
}
</style>