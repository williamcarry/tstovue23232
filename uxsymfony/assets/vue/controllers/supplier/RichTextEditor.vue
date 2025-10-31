<template>
  <div class="rich-text-editor">
    <div ref="editorContainer" class="editor-container"></div>
    <!-- 隐藏的文件输入框用于图片上传 -->
    <input 
      ref="fileInput" 
      type="file" 
      accept="image/*" 
      style="display: none;" 
      @change="handleImageUpload"
    />
    <!-- 上传提示遮罩 -->
    <div v-if="uploading" class="upload-overlay">
      <div class="upload-spinner">
        <div class="spinner"></div>
        <p>图片上传中...</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, watch, defineProps, defineEmits } from 'vue'
import Quill from 'quill'
import 'quill/dist/quill.snow.css'

const props = defineProps({
  modelValue: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: '请输入内容...'
  },
  height: {
    type: String,
    default: '500px'
  },
  readonly: {
    type: Boolean,
    default: false
  },
  supplierLoginPath: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['update:modelValue'])

const editorContainer = ref(null)
const fileInput = ref(null)
const uploading = ref(false) // 上传状态
let quillEditor = null
let isUpdatingContent = false

// 初始化编辑器
onMounted(() => {
  if (!editorContainer.value) return

  // Quill 配置
  const options = {
    theme: 'snow',
    placeholder: props.placeholder,
    readOnly: props.readonly,
    modules: {
      toolbar: [
        [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
        [{ 'size': ['small', false, 'large', 'huge'] }],
        ['bold', 'italic', 'underline', 'strike'],
        [{ 'color': [] }, { 'background': [] }],
        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
        [{ 'align': [] }],
        ['blockquote', 'code-block'],
        ['link', 'image'],
        ['clean']
      ]
    }
  }

  // 创建 Quill 实例
  quillEditor = new Quill(editorContainer.value, options)

  // 设置初始内容
  if (props.modelValue) {
    quillEditor.root.innerHTML = props.modelValue
  }

  // 设置编辑器高度
  quillEditor.root.style.height = props.height

  // 监听内容变化
  quillEditor.on('text-change', () => {
    if (!isUpdatingContent) {
      const html = quillEditor.root.innerHTML
      emit('update:modelValue', html === '<p><br></p>' ? '' : html)
    }
  })

  // 自定义图片处理
  customizeImageHandler()
})

// 自定义图片处理
const customizeImageHandler = () => {
  if (!quillEditor) return

  // 获取工具栏
  const toolbar = quillEditor.getModule('toolbar')
  
  // 重写图片按钮的行为
  toolbar.addHandler('image', () => {
    // 如果正在上传，阻止重复点击
    if (uploading.value) {
      alert('图片正在上传中，请稍候...')
      return
    }
    
    // 触发文件选择
    if (fileInput.value) {
      fileInput.value.click()
    }
  })
}

// 处理图片上传
const handleImageUpload = async (event) => {
  const file = event.target.files[0]
  if (!file) return

  // 验证文件类型
  const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp']
  if (!allowedTypes.includes(file.type)) {
    alert('请上传图片文件（JPG/PNG/GIF/WEBP）')
    return
  }

  // 验证文件大小（最大5MB）
  if (file.size > 5 * 1024 * 1024) {
    alert('图片大小不能超过5MB')
    return
  }

  // 设置上传状态
  uploading.value = true

  try {
    // 创建 FormData
    const formData = new FormData()
    formData.append('file', file)

    // 上传到服务器
    const response = await fetch(`/supplier${props.supplierLoginPath}/upload-file`, {
      method: 'POST',
      body: formData,
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      }
    })

    const result = await response.json()

    if (result.success) {
      // 获取当前光标位置
      const range = quillEditor.getSelection()
      
      // 插入图片
      if (range) {
        quillEditor.insertEmbed(range.index, 'image', result.previewUrl)
        // 移动光标到图片后面
        quillEditor.setSelection(range.index + 1)
      }
    } else {
      alert('图片上传失败：' + (result.message || '未知错误'))
    }
  } catch (error) {
    console.error('上传失败:', error)
    alert('网络错误，上传失败')
  } finally {
    // 重置上传状态
    uploading.value = false
    // 清空文件输入框
    event.target.value = ''
  }
}

// 监听 modelValue 变化
watch(() => props.modelValue, (newValue) => {
  if (quillEditor && !isUpdatingContent) {
    const currentContent = quillEditor.root.innerHTML
    if (currentContent !== newValue) {
      isUpdatingContent = true
      quillEditor.root.innerHTML = newValue || ''
      isUpdatingContent = false
    }
  }
})

// 组件卸载时清理
onBeforeUnmount(() => {
  if (quillEditor) {
    quillEditor = null
  }
})
</script>

<style scoped>
.rich-text-editor {
  border: 1px solid #dcdfe6;
  border-radius: 4px;
  overflow: hidden;
  width: 100%;
  position: relative;
}

.editor-container {
  background: white;
  width: 100%;
}

:deep(.ql-container) {
  font-size: 14px;
  border: none;
}

:deep(.ql-editor) {
  min-height: 200px;
  max-height: 600px;
  overflow-y: auto;
  line-height: 1.6;
}

:deep(.ql-toolbar) {
  background: #f5f7fa;
  border: none;
  border-bottom: 1px solid #dcdfe6;
  line-height: 1.5;
}

:deep(.ql-toolbar.ql-snow) {
  border: none;
  border-bottom: 1px solid #dcdfe6;
}

:deep(.ql-container.ql-snow) {
  border: none;
}

:deep(.ql-snow .ql-picker) {
  color: #606266;
  line-height: 1.5;
}

:deep(.ql-snow .ql-stroke) {
  stroke: #606266;
}

:deep(.ql-snow .ql-fill) {
  fill: #606266;
}

:deep(.ql-snow .ql-picker-label) {
  padding-left: 8px;
  padding-right: 8px;
}

:deep(.ql-toolbar button) {
  width: 28px;
  height: 28px;
}

:deep(.ql-editor img) {
  max-width: 100%;
  height: auto;
}

/* 上传提示遮罩 */
.upload-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(255, 255, 255, 0.8);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.upload-spinner {
  text-align: center;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #f3f3f3;
  border-top: 4px solid #409eff;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.upload-spinner p {
  margin-top: 10px;
  color: #606266;
  font-size: 14px;
}
</style>