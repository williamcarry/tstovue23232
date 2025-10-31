<template>
  <div class="promotion-menu-upload">
    <el-upload
      class="promotion-image-uploader"
      action=""
      :auto-upload="false"
      :show-file-list="false"
      :on-change="handleImageChange"
      :before-upload="beforeImageUpload"
    >
      <img v-if="previewImageUrl" :src="previewImageUrl" class="promotion-image" />
      <el-icon v-else class="promotion-uploader-icon">
        <Plus />
      </el-icon>
    </el-upload>
    
    <div v-if="uploadProgress > 0 && uploadProgress < 100" class="upload-progress">
      <el-progress :percentage="uploadProgress" />
    </div>
    
    <div v-if="uploadError" class="upload-error">
      {{ uploadError }}
    </div>
  </div>
</template>

<script setup>
import { ref, watch, defineProps, defineEmits } from 'vue'
import { ElUpload, ElIcon, ElProgress, ElMessage } from 'element-plus'
import { Plus } from '@element-plus/icons-vue'

// 定义 props
const props = defineProps({
  modelValue: {
    type: String,
    default: ''
  },
  adminLoginPath: {
    type: String,
    default: ''
  }
})

// 定义 emits
const emit = defineEmits(['update:modelValue', 'upload-success', 'upload-error'])

// 响应式数据
const imageUrl = ref(props.modelValue)
const previewImageUrl = ref('')  // 用于显示的预览URL
const uploadProgress = ref(0)
const uploadError = ref('')

// 获取签名URL（用于显示已存储的图片）
const fetchSignedUrl = async (key) => {
  if (!key || key.startsWith('http')) {
    console.log('[PromotionMenuUpload] Key is already URL or empty:', key)
    previewImageUrl.value = key
    return
  }
  
  try {
    const loginPath = props.adminLoginPath || window.adminLoginPath || ''
    const signedUrlEndpoint = `/admin${loginPath}/promotion/image/signed-url`
    console.log('[PromotionMenuUpload] Fetching signed URL from:', signedUrlEndpoint)
    console.log('[PromotionMenuUpload] Request key:', key)
    
    const response = await fetch(signedUrlEndpoint, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ key: key })
    })
    
    const result = await response.json()
    console.log('[PromotionMenuUpload] Signed URL result:', result)
    
    if (result.success) {
      console.log('[PromotionMenuUpload] Got signed URL:', result.url)
      previewImageUrl.value = result.url
    } else {
      console.error('[PromotionMenuUpload] Failed to get signed URL:', result.error)
      previewImageUrl.value = ''  // 失败时不显示图片
    }
  } catch (error) {
    console.error('[PromotionMenuUpload] Error fetching signed URL:', error)
    previewImageUrl.value = ''
  }
}

// 监听 modelValue 变化
watch(() => props.modelValue, (newVal) => {
  console.log('[PromotionMenuUpload] modelValue changed:', newVal)
  imageUrl.value = newVal
  // 如果是七牛云的key（不包含http），不直接显示，等待后端返回签名URL
  if (newVal && !newVal.startsWith('http')) {
    console.log('[PromotionMenuUpload] Fetching signed URL for key:', newVal)
    // 对于编辑时从数据库加载的key，我们需要获取签名URL
    fetchSignedUrl(newVal)
  } else {
    console.log('[PromotionMenuUpload] Using URL directly:', newVal)
    previewImageUrl.value = newVal
  }
}, { immediate: true })

// 图片选择处理
const handleImageChange = (file) => {
  // 验证文件类型
  const isValidType = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'].includes(file.raw.type)
  if (!isValidType) {
    ElMessage.error('只支持 JPG、PNG、GIF、WEBP 格式的图片!')
    return false
  }
  
  // 验证文件大小 (5MB)
  const isLt5M = file.raw.size / 1024 / 1024 < 5
  if (!isLt5M) {
    ElMessage.error('图片大小不能超过 5MB!')
    return false
  }
  
  // 预览图片
  imageUrl.value = URL.createObjectURL(file.raw)
  
  // 上传图片
  uploadImageToQiniu(file.raw)
  
  return true
}

// 上传前验证
const beforeImageUpload = (rawFile) => {
  // 这个函数会在 handleImageChange 之前执行，但我们已经在 handleImageChange 中处理了验证
  return false // 阻止自动上传，我们手动处理
}

// 上传图片到七牛云
const uploadImageToQiniu = async (file) => {
  try {
    uploadProgress.value = 10
    uploadError.value = ''
    
    // 创建 FormData
    const formData = new FormData()
    formData.append('image', file)
    
    // 使用从prop传入的adminLoginPath
    const loginPath = props.adminLoginPath || window.adminLoginPath || ''
    const uploadUrl = `/admin${loginPath}/promotion/menu/upload-image`
    console.log('[PromotionMenuUpload] Uploading to:', uploadUrl)
    
    // 上传到服务器
    uploadProgress.value = 30
    const response = await fetch(uploadUrl, {
      method: 'POST',
      body: formData
    })
    
    uploadProgress.value = 70
    const result = await response.json()
    console.log('[PromotionMenuUpload] Upload result:', result)
    
    if (result.success) {
      // 预览图片（使用临时URL）
      if (result.previewUrl) {
        console.log('[PromotionMenuUpload] Using preview URL:', result.previewUrl)
        previewImageUrl.value = result.previewUrl
      }
        
      uploadProgress.value = 100
      imageUrl.value = result.url  // 这是key，不是完整URL
      console.log('[PromotionMenuUpload] Stored key:', result.url)
      emit('update:modelValue', result.url)  // 发送key给父组件
      emit('upload-success', result)
      ElMessage.success('图片上传成功!')
    } else {
      throw new Error(result.error || '上传失败')
    }
  } catch (error) {
    console.error('[PromotionMenuUpload] Upload error:', error)
    uploadProgress.value = 0
    uploadError.value = error.message || '上传失败，请稍后重试'
    emit('upload-error', error)
    ElMessage.error(uploadError.value)
  }
}

// 暴露方法给父组件
defineExpose({
  clearImage() {
    imageUrl.value = ''
    previewImageUrl.value = ''
    uploadProgress.value = 0
    uploadError.value = ''
    emit('update:modelValue', '')
  }
})
</script>

<style scoped>
.promotion-image-uploader {
  border: 1px dashed #d9d9d9;
  border-radius: 6px;
  cursor: pointer;
  position: relative;
  overflow: hidden;
  transition: border-color 0.3s;
  width: 178px;
  height: 178px;
}

.promotion-image-uploader:hover {
  border-color: #409eff;
}

.promotion-image {
  width: 178px;
  height: 178px;
  display: block;
}

.promotion-uploader-icon {
  font-size: 28px;
  color: #8c939d;
  width: 178px;
  height: 178px;
  text-align: center;
  line-height: 178px;
}

.upload-progress {
  margin-top: 10px;
}

.upload-error {
  color: #f56c6c;
  font-size: 12px;
  margin-top: 5px;
}
</style>