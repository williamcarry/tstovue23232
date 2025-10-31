<template>
  <div class="file-upload">
    <el-upload
      class="file-uploader"
      action=""
      :auto-upload="false"
      :show-file-list="false"
      :on-change="handleFileChange"
      :before-upload="beforeFileUpload"
      :accept="accept"
    >
      <img v-if="previewImageUrl && isImage" :src="previewImageUrl" class="file-image-preview" />
      <div v-else-if="previewImageUrl && !isImage" class="file-preview">
        <el-icon class="file-icon"><Document /></el-icon>
        <span class="file-name">{{ fileName }}</span>
      </div>
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
    
    <div v-if="previewImageUrl && showRemove" class="remove-button">
      <el-button type="danger" size="small" @click="removeFile">移除</el-button>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, computed, defineProps, defineEmits } from 'vue'
import { ElUpload, ElIcon, ElProgress, ElButton, ElMessage } from 'element-plus'
import { Plus, Document } from '@element-plus/icons-vue'

// 定义 props
const props = defineProps({
  modelValue: {
    type: String,
    default: ''
  },
  adminLoginPath: {
    type: String,
    default: ''
  },
  supplierLoginPath: {
    type: String,
    default: ''
  },
  accept: {
    type: String,
    default: 'image/*'  // 默认只接受图片
  },
  maxSize: {
    type: Number,
    default: 5  // 默认5MB
  },
  showRemove: {
    type: Boolean,
    default: true
  },
  endpointType: {
    type: String,
    default: 'supplier'  // 默认使用供应商接口，可选 'supplier' 或 'member'
  }
})

// 定义 emits
const emit = defineEmits(['update:modelValue', 'upload-success', 'upload-error', 'remove'])

// 响应式数据
const fileKey = ref(props.modelValue)  // 存储七牛云的key
const previewImageUrl = ref('')  // 用于显示的预览URL
const uploadProgress = ref(0)
const uploadError = ref('')
const fileName = ref('')

// 计算属性
const isImage = computed(() => {
  if (!fileKey.value) return false
  const imageExtensions = ['.jpg', '.jpeg', '.png', '.gif', '.webp', '.bmp']
  return imageExtensions.some(ext => fileKey.value.toLowerCase().endsWith(ext))
})

// 获取签名URL（用于显示已存储的文件）
const fetchSignedUrl = async (key) => {
  if (!key || key.startsWith('http')) {
    console.log('[FileUpload] Key is already URL or empty:', key)
    previewImageUrl.value = key
    return
  }
  
  try {
    // 检查当前环境：供应商后台还是管理员后台
    const isSupplierBackend = !!props.supplierLoginPath || window.location.pathname.includes('/supplier')
    
    let signedUrlEndpoint = ''
    if (isSupplierBackend) {
      // 供应商后台
      const loginPath = props.supplierLoginPath || window.supplierLoginPath || ''
      signedUrlEndpoint = `/supplier${loginPath}/file/signed-url`
    } else {
      // 管理员后台
      const loginPath = props.adminLoginPath || window.adminLoginPath || ''
      // 根据endpointType属性确定使用哪个接口
      if (props.endpointType === 'member') {
        signedUrlEndpoint = `/admin${loginPath}/member/file/signed-url`
      } else if (props.endpointType === 'site-config') {
        signedUrlEndpoint = `/admin${loginPath}/site-config/file/signed-url`
      } else {
        signedUrlEndpoint = `/admin${loginPath}/supplier/file/signed-url`
      }
    }
    
    console.log('[FileUpload] Fetching signed URL from:', signedUrlEndpoint)
    console.log('[FileUpload] Request key:', key)
    
    const response = await fetch(signedUrlEndpoint, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify({ key: key })
    })
    
    const result = await response.json()
    console.log('[FileUpload] Signed URL result:', result)
    
    if (result.success) {
      console.log('[FileUpload] Got signed URL:', result.url)
      previewImageUrl.value = result.url
      // 从URL中提取文件名
      try {
        const url = new URL(result.url)
        const pathParts = url.pathname.split('/')
        if (pathParts.length > 0) {
          fileName.value = decodeURIComponent(pathParts[pathParts.length - 1])
        }
      } catch (e) {
        console.error('Error extracting filename:', e)
      }
    } else {
      console.error('[FileUpload] Failed to get signed URL:', result.error)
      previewImageUrl.value = ''  // 失败时不显示文件
    }
  } catch (error) {
    console.error('[FileUpload] Error fetching signed URL:', error)
    previewImageUrl.value = ''
  }
}

// 监听 modelValue 变化
watch(() => props.modelValue, (newVal) => {
  console.log('[FileUpload] modelValue changed:', {
    newVal,
    isHttp: newVal && newVal.startsWith('http'),
    endpointType: props.endpointType
  })
  fileKey.value = newVal
  // 如果是七牛云的key（不包含http），不直接显示，等待后端返回签名URL
  if (newVal && !newVal.startsWith('http')) {
    console.log('[FileUpload] Fetching signed URL for key:', newVal)
    // 对于编辑时从数据库加载的key，我们需要获取签名URL
    fetchSignedUrl(newVal)
  } else {
    console.log('[FileUpload] Using URL directly:', newVal)
    previewImageUrl.value = newVal
  }
}, { immediate: true })

// 文件选择处理
const handleFileChange = (file) => {
  // 验证文件类型
  const fileType = file.raw.type
  const fileOriginalName = file.raw.name
  const fileExtension = '.' + fileOriginalName.split('.').pop().toLowerCase()
  
  // 检查文件类型是否符合accept属性要求
  const acceptTypes = props.accept.split(',').map(type => type.trim())
  let isValidType = false
  
  for (const acceptType of acceptTypes) {
    if (acceptType === '*/*' || acceptType === '') {
      isValidType = true
      break
    }
    
    if (acceptType.endsWith('/*')) {
      // 例如 image/* 匹配 image/png, image/jpeg 等
      const mainType = acceptType.slice(0, -2)
      if (fileType.startsWith(mainType)) {
        isValidType = true
        break
      }
    } else if (acceptType.startsWith('.')) {
      // 例如 .jpg, .png 匹配文件扩展名
      if (fileExtension === acceptType.toLowerCase()) {
        isValidType = true
        break
      }
    } else {
      // 例如 image/png 匹配完整MIME类型
      if (fileType === acceptType) {
        isValidType = true
        break
      }
    }
  }
  
  if (!isValidType) {
    ElMessage.error(`文件类型不支持，请上传符合要求的文件!`)
    return false
  }
  
  // 验证文件大小
  const maxSizeInBytes = props.maxSize * 1024 * 1024
  const isLtMaxSize = file.raw.size < maxSizeInBytes
  if (!isLtMaxSize) {
    ElMessage.error(`文件大小不能超过 ${props.maxSize}MB!`)
    return false
  }
  
  // 保存文件名
  fileName.value = fileOriginalName
  
  // 预览文件
  if (fileType.startsWith('image/')) {
    previewImageUrl.value = URL.createObjectURL(file.raw)
  } else {
    previewImageUrl.value = ''  // 非图片文件不预览
  }
  
  // 上传文件
  uploadFileToQiniu(file.raw)
  
  return true
}

// 上传前验证
const beforeFileUpload = (rawFile) => {
  // 这个函数会在 handleFileChange 之前执行，但我们已经在 handleFileChange 中处理了验证
  return false // 阻止自动上传，我们手动处理
}

// 上传文件到七牛云
const uploadFileToQiniu = async (file) => {
  try {
    uploadProgress.value = 10
    uploadError.value = ''
    
    // 创建 FormData
    const formData = new FormData()
    formData.append('file', file)
    
    // 检查当前环境：供应商后台还是管理员后台
    const isSupplierBackend = !!props.supplierLoginPath || window.location.pathname.includes('/supplier')
    
    let uploadUrl = ''
    if (isSupplierBackend) {
      // 供应商后台 - 使用供应商的上传接口
      const loginPath = props.supplierLoginPath || window.supplierLoginPath || ''
      uploadUrl = `/supplier${loginPath}/upload-file`
    } else {
      // 管理员后台 - 根据endpointType选择接口
      const loginPath = props.adminLoginPath || window.adminLoginPath || ''
      if (props.endpointType === 'member') {
        uploadUrl = `/admin${loginPath}/member/upload-file`
      } else if (props.endpointType === 'site-config') {
        uploadUrl = `/admin${loginPath}/site-config/upload-file`
      } else {
        uploadUrl = `/admin${loginPath}/supplier/upload-file`
      }
    }
    
    console.log('[FileUpload] Current environment:', isSupplierBackend ? 'Supplier Backend' : 'Admin Backend')
    console.log('[FileUpload] Uploading to:', uploadUrl)
    console.log('[FileUpload] File info:', { name: file.name, type: file.type, size: file.size })
    
    // 上传到服务器
    uploadProgress.value = 30
    const response = await fetch(uploadUrl, {
      method: 'POST',
      body: formData,
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    
    console.log('[FileUpload] Response status:', response.status)
    console.log('[FileUpload] Response headers:', response.headers.get('content-type'))
    
    // 检查响应类型
    const contentType = response.headers.get('content-type')
    if (!contentType || !contentType.includes('application/json')) {
      // 响应不是JSON，可能是HTML错误页面
      const text = await response.text()
      console.error('[FileUpload] Non-JSON response:', text.substring(0, 500))
      
      // 检查是否是登录页面（包含登录表单）
      if (text.includes('login-form') || text.includes('登录')) {
        throw new Error('请先登录管理后台再进行文件上传操作')
      }
      
      throw new Error('服务器返回了非JSON响应，可能是权限错误或路径错误。状态码: ' . response.status)
    }
    
    uploadProgress.value = 70
    const result = await response.json()
    console.log('[FileUpload] Upload result:', result)
    
    // 处理特定的错误状态码
    if (response.status === 401) {
      throw new Error('请先登录管理后台再进行文件上传操作')
    }
    
    if (response.status === 403) {
      throw new Error('没有权限进行文件上传操作')
    }
    
    if (!result.success) {
      throw new Error(result.error || '上传失败')
    }
    
    // 上传成功
    if (result.success) {
      // 预览文件（使用临时URL）
      if (result.previewUrl) {
        console.log('[FileUpload] Using preview URL:', result.previewUrl)
        previewImageUrl.value = result.previewUrl
      }
        
      uploadProgress.value = 100
      fileKey.value = result.key  // 这是key，不是完整URL
      console.log('[FileUpload] Stored key:', result.key)
      emit('update:modelValue', result.key)  // 发送key给父组件
      emit('upload-success', result)
      ElMessage.success('文件上传成功!')
    } else {
      throw new Error(result.error || '上传失败')
    }
  } catch (error) {
    console.error('[FileUpload] Upload error:', error)
    uploadProgress.value = 0
    uploadError.value = error.message || '上传失败，请稍后重试'
    emit('upload-error', error)
    ElMessage.error(uploadError.value)
  }
}

// 移除文件
const removeFile = () => {
  fileKey.value = ''
  previewImageUrl.value = ''
  fileName.value = ''
  uploadProgress.value = 0
  uploadError.value = ''
  emit('update:modelValue', '')
  emit('remove')
}

// 暴露方法给父组件
defineExpose({
  clearFile() {
    removeFile()
  }
})
</script>

<style scoped>
.file-upload {
  display: inline-block;
}

.file-uploader {
  border: 1px dashed #d9d9d9;
  border-radius: 6px;
  cursor: pointer;
  position: relative;
  overflow: hidden;
  transition: border-color 0.3s;
  width: 178px;
  height: 178px;
}

.file-uploader:hover {
  border-color: #409eff;
}

.file-image-preview {
  width: 178px;
  height: 178px;
  display: block;
  object-fit: cover;
}

.file-preview {
  width: 178px;
  height: 178px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  background-color: #f5f7fa;
}

.file-icon {
  font-size: 48px;
  color: #909399;
  margin-bottom: 10px;
}

.file-name {
  font-size: 12px;
  color: #606266;
  text-align: center;
  padding: 0 10px;
  word-break: break-all;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.uploader-icon {
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

.remove-button {
  margin-top: 10px;
  text-align: center;
}
</style>