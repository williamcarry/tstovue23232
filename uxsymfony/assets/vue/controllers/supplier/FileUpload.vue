<template>
  <div class="file-upload">
    <el-upload
      class="file-uploader"
      action=""
      :auto-upload="false"
      :show-file-list="false"
      :on-change="handleFileChange"
      accept="image/*"
    >
      <img v-if="previewImageUrl" :src="previewImageUrl" class="file-image-preview" />
      <el-icon v-else class="uploader-icon">
        <Plus />
      </el-icon>
    </el-upload>
    
    <div v-if="uploadProgress > 0 && uploadProgress < 100" class="upload-progress">
      <el-progress :percentage="uploadProgress" />
    </div>
    
    <div v-if="uploadError" class="upload-error">
      {{ uploadError }}
    </div>
    
    <div v-if="previewImageUrl" class="remove-button">
      <el-button type="danger" size="small" @click="removeFile">移除</el-button>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, defineProps, defineEmits } from 'vue'
import { ElMessage } from 'element-plus'
import { Plus } from '@element-plus/icons-vue'

const props = defineProps({
  modelValue: {
    type: String,
    default: ''
  },
  supplierLoginPath: {
    type: String,
    required: true
  },
  endpointType: {
    type: String,
    default: 'product'  // product, profile等
  }
})

const emit = defineEmits(['update:modelValue'])

const fileKey = ref(props.modelValue)
const previewImageUrl = ref('')
const uploadProgress = ref(0)
const uploadError = ref('')

// 获取签名URL
const fetchSignedUrl = async (key) => {
  if (!key || key.startsWith('http')) {
    previewImageUrl.value = key
    return
  }
  
  try {
    const response = await fetch(`/supplier${props.supplierLoginPath}/${props.endpointType}/image/signed-url`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify({ key: key })
    })
    
    const result = await response.json()
    
    if (result.success) {
      previewImageUrl.value = result.url
    }
  } catch (error) {
    console.error('获取签名URL失败:', error)
  }
}

// 监听modelValue变化
watch(() => props.modelValue, (newVal) => {
  fileKey.value = newVal
  if (newVal && !newVal.startsWith('http')) {
    fetchSignedUrl(newVal)
  } else {
    previewImageUrl.value = newVal
  }
}, { immediate: true })

// 文件选择处理
const handleFileChange = async (file) => {
  const fileType = file.raw.type
  const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp']
  
  if (!allowedTypes.includes(fileType)) {
    ElMessage.error('请上传图片文件（JPG/PNG/GIF/WEBP）')
    return
  }
  
  if (file.raw.size > 5 * 1024 * 1024) {
    ElMessage.error('图片大小不能超过5MB')
    return
  }
  
  uploadError.value = ''
  uploadProgress.value = 0
  
  try {
    const formData = new FormData()
    formData.append('image', file.raw)
    
    const response = await fetch(`/supplier${props.supplierLoginPath}/product/upload-image`, {
      method: 'POST',
      body: formData,
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    
    const result = await response.json()
    
    if (result.success) {
      uploadProgress.value = 100
      fileKey.value = result.key
      previewImageUrl.value = result.previewUrl
      emit('update:modelValue', result.key)
      ElMessage.success('上传成功')
    } else {
      uploadError.value = result.message || '上传失败'
      ElMessage.error(uploadError.value)
    }
  } catch (error) {
    console.error('上传失败:', error)
    uploadError.value = '网络错误，请稍后重试'
    ElMessage.error(uploadError.value)
  } finally {
    setTimeout(() => {
      uploadProgress.value = 0
    }, 2000)
  }
}

// 移除文件
const removeFile = () => {
  fileKey.value = ''
  previewImageUrl.value = ''
  uploadError.value = ''
  emit('update:modelValue', '')
}
</script>

<style scoped>
.file-upload {
  display: inline-block;
}

.file-uploader {
  width: 148px;
  height: 148px;
  border: 1px dashed #d9d9d9;
  border-radius: 6px;
  cursor: pointer;
  position: relative;
  overflow: hidden;
  transition: border-color 0.3s;
}

.file-uploader:hover {
  border-color: #409eff;
}

.uploader-icon {
  font-size: 28px;
  color: #8c939d;
  width: 148px;
  height: 148px;
  text-align: center;
  line-height: 148px;
}

.file-image-preview {
  width: 148px;
  height: 148px;
  display: block;
  object-fit: cover;
}

.upload-progress {
  margin-top: 10px;
}

.upload-error {
  color: #f56c6c;
  font-size: 12px;
  margin-top: 5px;
}

.remove-button {
  margin-top: 10px;
}
</style>
