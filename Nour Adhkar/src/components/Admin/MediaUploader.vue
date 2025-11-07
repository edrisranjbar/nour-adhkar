<template>
  <div class="media-uploader">
    <div class="upload-area" 
         :class="{ 'dragging': isDragging, 'has-files': selectedFiles.length > 0 }"
         @dragover.prevent="onDragOver" 
         @dragleave.prevent="isDragging = false"
         @drop.prevent="onFileDrop">
      
      <input type="file" ref="fileInput" @change="onFileSelected" multiple style="display: none" :accept="acceptedTypes">
      
      <div class="upload-prompt" v-if="!selectedFiles.length">
        <i class="fas fa-cloud-upload-alt"></i>
        <p>فایل‌ها را اینجا رها کنید یا</p>
        <button @click="$refs.fileInput.click()" class="upload-btn">
          <i class="fas fa-folder-open"></i> انتخاب فایل
        </button>
        <p class="upload-info" v-if="acceptedTypes">
          فرمت‌های مجاز: {{ formatAcceptedTypes }}
        </p>
      </div>
      
      <div class="selected-files" v-else>
        <div class="selected-files-header">
          <h3>{{ selectedFiles.length }} فایل انتخاب شده</h3>
          <button @click="clearFiles" class="clear-btn">
            <i class="fas fa-times"></i> حذف همه
          </button>
        </div>
        
        <div class="file-list">
          <div v-for="(file, idx) in selectedFiles" :key="idx" class="selected-file">
            <div class="file-preview">
              <img 
                v-if="file.preview && isImage(file.type)" 
                :src="file.preview" 
                alt="preview" 
                class="preview-thumbnail"
              />
              <i v-else-if="isAudio(file.type)" class="fas fa-music file-icon"></i>
              <i v-else class="fas fa-file file-icon"></i>
            </div>
            <div class="file-info">
              <span class="file-name">{{ file.name }}</span>
              <span class="file-size">{{ formatSize(file.size) }}</span>
            </div>
            <button @click="removeFile(idx)" class="remove-btn">
              <i class="fas fa-times"></i>
            </button>
          </div>
        </div>
        
        <div class="upload-actions">
          <button @click="clearFiles" class="cancel-btn">
            <i class="fas fa-times"></i> لغو
          </button>
          <button @click="startUpload" class="start-upload-btn" :disabled="uploading">
            <i class="fas fa-upload"></i> آپلود فایل‌ها
          </button>
        </div>
      </div>
      
      <div class="upload-progress" v-if="uploading">
        <div class="progress-bar">
          <div class="progress-fill" :style="{ width: `${uploadProgress}%` }"></div>
        </div>
        <div class="progress-text">{{ uploadProgress }}%</div>
      </div>
    </div>
  </div>
</template>

<script>
import { mediaService } from '@/services/mediaService';

export default {
  name: 'MediaUploader',
  props: {
    acceptedTypes: {
      type: String,
      default: ''
    },
    maxFileSize: {
      type: Number,
      default: 10 * 1024 * 1024 // 10MB by default
    },
    maxFiles: {
      type: Number,
      default: 10
    }
  },
  data() {
    return {
      selectedFiles: [],
      isDragging: false,
      uploading: false,
      uploadProgress: 0
    };
  },
  computed: {
    formatAcceptedTypes() {
      if (!this.acceptedTypes) return '';
      return this.acceptedTypes
        .split(',')
        .map(type => type.trim().replace('.', '').toUpperCase())
        .join(', ');
    }
  },
  methods: {
    onDragOver() {
      this.isDragging = true;
    },
    onFileDrop(e) {
      this.isDragging = false;
      const files = e.dataTransfer.files;
      if (files.length) {
        this.processFiles(files);
      }
    },
    onFileSelected(e) {
      const files = e.target.files;
      if (files.length) {
        this.processFiles(files);
      }
    },
    processFiles(files) {
      console.log('Processing files:', files.length);
      
      // Check if exceeding max files limit
      if (this.selectedFiles.length + files.length > this.maxFiles) {
        this.$emit('notification', {
          type: 'error',
          message: `حداکثر ${this.maxFiles} فایل می‌توانید آپلود کنید`
        });
        return;
      }
      
      // Process each file
      for (let i = 0; i < files.length; i++) {
        const file = files[i];
        console.log('Processing file:', file.name, 'type:', file.type, 'size:', file.size);
        
        // Check file size
        if (file.size > this.maxFileSize) {
          this.$emit('notification', {
            type: 'error',
            message: `فایل ${file.name} بیش از حد مجاز است (${this.formatSize(this.maxFileSize)})`
          });
          continue;
        }
        
        // Check file type if acceptedTypes is specified
        if (this.acceptedTypes && !this.isAcceptedType(file.type)) {
          this.$emit('notification', {
            type: 'error',
            message: `فرمت فایل ${file.name} مجاز نیست`
          });
          continue;
        }
        
        // Create file preview for images
        const fileObj = {
          file: file,
          name: file.name,
          size: file.size,
          type: file.type,
          preview: null
        };
        
        if (this.isImage(file.type)) {
          const reader = new FileReader();
          reader.onload = (e) => {
            fileObj.preview = e.target.result;
            // Force a re-render
            this.selectedFiles = [...this.selectedFiles];
          };
          reader.readAsDataURL(file);
        }
        
        this.selectedFiles.push(fileObj);
      }
      
      console.log('Selected files after processing:', this.selectedFiles.length);
    },
    removeFile(index) {
      this.selectedFiles.splice(index, 1);
    },
    clearFiles() {
      this.selectedFiles = [];
      if (this.$refs.fileInput) {
        this.$refs.fileInput.value = '';
      }
    },
    isImage(type) {
      return type.startsWith('image/');
    },
    isAudio(type) {
      return type.startsWith('audio/');
    },
    isAcceptedType(type) {
      if (!this.acceptedTypes) return true;
      
      const acceptedTypesArray = this.acceptedTypes.split(',').map(t => t.trim());
      
      // Check MIME type
      if (acceptedTypesArray.includes(type)) return true;
      
      // Check file extension
      const extension = '.' + type.split('/')[1];
      return acceptedTypesArray.some(t => t === extension);
    },
    formatSize(size) {
      const kb = size / 1024;
      if (kb < 1024) {
        return `${kb.toFixed(1)} KB`;
      } else {
        return `${(kb / 1024).toFixed(1)} MB`;
      }
    },
    async startUpload() {
      if (this.selectedFiles.length === 0) return;
      
      this.uploading = true;
      this.uploadProgress = 0;
      
      try {
        const formData = new FormData();
        
        // Add each file individually with a more basic approach
        // Laravel expects the field name 'files'
        for (let i = 0; i < this.selectedFiles.length; i++) {
          const fileObj = this.selectedFiles[i];
          // Use numeric index to handle multiple files
          formData.append(`files[${i}]`, fileObj.file);
        }
        
        // Debug: Log the FormData contents
        console.log('FormData created with files:');
        for (let pair of formData.entries()) {
          console.log(pair[0] + ': ' + (pair[1] instanceof File ? pair[1].name + ' (' + pair[1].size + ' bytes)' : pair[1]));
        }

        // Use the API to upload files
        console.log('Starting file upload...');
        const response = await mediaService.uploadMedia(formData, (progress) => {
          this.uploadProgress = progress;
        });
        
        console.log('Upload response:', response);
        
        if (!response || !response.success) {
          throw new Error(response?.message || 'خطا در آپلود فایل‌ها');
        }
        
        // Extract media items from response based on API structure
        const uploadedMedia = response.data?.media || [];
        console.log('Uploaded media:', uploadedMedia.length, 'files');
        
        // Emit success notification
        this.$emit('notification', {
          type: 'success',
          message: `${this.selectedFiles.length} فایل با موفقیت آپلود شد`
        });
        
        // Pass the uploaded media data back to parent
        this.$emit('upload-complete', uploadedMedia);
        
        // Reset component state
        this.selectedFiles = [];
        this.uploadProgress = 0;
      } catch (error) {
        console.error('Upload error:', error);
        // Emit error notification
        this.$emit('notification', {
          type: 'error',
          message: error?.message || 'خطا در آپلود فایل‌ها'
        });
      } finally {
        this.uploading = false;
      }
    }
  }
};
</script>

<style scoped>
.media-uploader {
  width: 100%;
  color: var(--admin-text);
}

.upload-area {
  border: 2px dashed var(--admin-border);
  border-radius: 12px;
  padding: 2rem;
  text-align: center;
  transition: all 0.3s ease;
  background-color: rgba(255, 255, 255, 0.04);
  position: relative;
}

.upload-area.dragging {
  background-color: rgba(59, 130, 246, 0.08);
  border-color: var(--admin-accent);
}

.upload-area.has-files {
  padding: 1.25rem;
}

.upload-prompt {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 200px;
  gap: 1rem;
}

.upload-prompt i {
  font-size: 3.5rem;
  color: var(--admin-accent);
}

.upload-prompt p {
  margin: 0;
  color: var(--admin-text);
  font-weight: 600;
}

.upload-info {
  font-size: 0.85rem;
  color: var(--admin-muted);
}

.upload-btn {
  background-color: var(--admin-accent);
  color: #fff;
  border: none;
  padding: 0.6rem 1.4rem;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 500;
  transition: transform 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  font-family: inherit;
}

.upload-btn:hover {
  background-color: rgba(59, 130, 246, 0.85);
  transform: translateY(-1px);
  box-shadow: 0 10px 20px rgba(59, 130, 246, 0.25);
}

.selected-files {
  width: 100%;
}

.selected-files-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
  padding-bottom: 0.75rem;
  border-bottom: 1px solid var(--admin-border);
}

.selected-files-header h3 {
  font-size: 1.2rem;
  color: var(--admin-text);
  margin: 0;
}

.clear-btn {
  background: none;
  border: none;
  color: #f87171;
  cursor: pointer;
  font-size: 0.85rem;
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  padding: 0.3rem 0.5rem;
  border-radius: 6px;
  transition: background-color 0.2s ease, transform 0.2s ease;
  font-family: inherit;
}

.clear-btn:hover {
  background-color: rgba(248, 113, 113, 0.12);
  transform: translateY(-1px);
}

.file-list {
  max-height: 250px;
  overflow-y: auto;
  margin-bottom: 1rem;
  border: 1px solid var(--admin-border);
  border-radius: 10px;
  background-color: var(--admin-surface);
}

.selected-file {
  display: flex;
  align-items: center;
  padding: 0.8rem;
  border-bottom: 1px solid var(--admin-border);
  gap: 0.75rem;
}

.selected-file:last-child {
  border-bottom: none;
}

.file-preview {
  width: 48px;
  height: 48px;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: rgba(255, 255, 255, 0.04);
  border-radius: 10px;
  overflow: hidden;
}

.preview-thumbnail {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.file-icon {
  font-size: 1.5rem;
  color: var(--admin-muted);
}

.file-info {
  flex: 1;
  overflow: hidden;
  text-align: right;
}

.file-name {
  display: block;
  font-weight: 600;
  color: var(--admin-text);
  text-overflow: ellipsis;
  white-space: nowrap;
  overflow: hidden;
}

.file-size {
  font-size: 0.75rem;
  color: var(--admin-muted);
}

.remove-btn {
  background: none;
  border: none;
  color: #f87171;
  cursor: pointer;
  padding: 0.25rem;
  border-radius: 6px;
  transition: background-color 0.2s ease, transform 0.2s ease;
}

.remove-btn:hover {
  background-color: rgba(248, 113, 113, 0.12);
  transform: translateY(-1px);
}

.upload-actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
}

.upload-actions button {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  border-radius: 8px;
  border: none;
  padding: 0.55rem 1.2rem;
  cursor: pointer;
  font-family: inherit;
  transition: transform 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
}

.upload-actions .cancel {
  background: rgba(148, 163, 184, 0.2);
  color: var(--admin-muted);
}

.upload-actions .cancel:hover {
  background: rgba(148, 163, 184, 0.3);
  transform: translateY(-1px);
}

.upload-actions .confirm {
  background: var(--admin-accent);
  color: #fff;
}

.upload-actions .confirm:hover {
  background: rgba(59, 130, 246, 0.85);
  transform: translateY(-1px);
  box-shadow: 0 10px 20px rgba(59, 130, 246, 0.25);
}

.upload-progress {
  margin-top: 1rem;
  width: 100%;
}

.progress-bar {
  height: 100%;
  background: var(--admin-accent);
  transition: width 0.3s ease;
}

.progress-fill {
  height: 100%;
  background-color: #A79277;
  transition: width 0.2s;
}

.progress-text {
  text-align: right;
  font-size: 0.75rem;
  color: #6c757d;
}

.error-message {
  color: #f87171;
  margin-top: 1rem;
  font-size: 0.9rem;
}
</style> 