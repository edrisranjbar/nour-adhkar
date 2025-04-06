<template>
  <div class="image-upload-field">
    <div class="upload-area" @click="triggerFileInput" @dragover.prevent @drop.prevent="handleDrop">
      <input
        type="file"
        ref="fileInput" 
        @change="handleFileChange"
        accept="image/jpeg,image/png,image/gif,image/webp"
        class="file-input"
      />
      <div class="upload-content">
        <font-awesome-icon icon="fa-solid fa-cloud-upload-alt" class="upload-icon" />
        <p class="upload-text">برای آپلود اینجا کلیک کنید یا فایل خود را اینجا رها کنید</p>
        <p class="upload-hint">فرمت‌های مجاز: JPG، PNG، GIF، WEBP</p>
      </div>
    </div>
    
    <div class="image-preview" v-if="imageUrl">
      <img :src="imageUrl" alt="Preview">
      <button @click="removeImage" class="remove-image-btn" type="button">حذف تصویر</button>
    </div>
    
    <div class="upload-status" v-if="uploadStatus">
      {{ uploadStatus }}
    </div>
  </div>
</template>

<script>
import { library } from '@fortawesome/fontawesome-svg-core';
import { faCloudUploadAlt } from '@fortawesome/free-solid-svg-icons';

// Add icons to the library
library.add(faCloudUploadAlt);

export default {
  name: 'ImageUploadField',
  props: {
    value: {
      type: String,
      default: ''
    },
    uploadStatus: {
      type: String,
      default: ''
    }
  },
  data() {
    return {
      imageUrl: this.value
    };
  },
  watch: {
    value(newVal) {
      this.imageUrl = newVal;
    }
  },
  methods: {
    triggerFileInput() {
      const fileInput = this.$refs.fileInput;
      if (fileInput) {
        fileInput.click();
      }
    },
    handleFileChange(event) {
      const file = event.target.files[0];
      if (file) {
        this.processFile(file);
      }
    },
    handleDrop(event) {
      const file = event.dataTransfer.files[0];
      if (file) {
        this.processFile(file);
      }
    },
    processFile(file) {
      // Create a temporary URL for preview
      const tempImageUrl = URL.createObjectURL(file);
      this.imageUrl = tempImageUrl;
      this.$emit('input', tempImageUrl);
      this.$emit('file-selected', file);
    },
    removeImage() {
      this.imageUrl = '';
      this.$emit('input', '');
      this.$emit('image-removed');
    }
  }
};
</script>

<style scoped>
.image-upload-field {
  width: 100%;
}

.upload-area {
  width: 100%;
  min-height: 200px;
  border: 2px dashed #A79277;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s ease;
  background-color: #f8f8f8;
  margin-bottom: 16px;
}

.upload-area:hover {
  background-color: #f0f0f0;
  border-color: #8a7660;
}

.file-input {
  display: none;
}

.upload-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 20px;
  text-align: center;
}

.upload-icon {
  font-size: 3rem;
  color: #A79277;
  margin-bottom: 16px;
}

.upload-text {
  font-size: 1.1rem;
  color: #333;
  margin-bottom: 8px;
  font-weight: 500;
}

.upload-hint {
  font-size: 0.9rem;
  color: #6c757d;
}

.image-preview {
  margin-top: 16px;
  border-radius: 8px;
  overflow: hidden;
  max-width: 100%;
}

.image-preview img {
  width: 100%;
  height: auto;
  display: block;
  border-radius: 8px;
}

.remove-image-btn {
  margin-top: 8px;
  background-color: #dc3545;
  color: white;
  border: none;
  border-radius: 4px;
  padding: 8px 16px;
  cursor: pointer;
  font-size: 0.9rem;
  font-family: inherit;
}

.remove-image-btn:hover {
  background-color: #c82333;
}

.upload-status {
  margin-top: 8px;
  color: #6c757d;
  font-size: 0.9rem;
}

/* Dark mode styles */
body.dark-mode .upload-area {
  background-color: #333;
  border-color: #A79277;
}

body.dark-mode .upload-area:hover {
  background-color: #3a3a3a;
  border-color: #8a7660;
}

body.dark-mode .upload-text {
  color: #eee;
}

body.dark-mode .upload-hint {
  color: #aaa;
}
</style> 