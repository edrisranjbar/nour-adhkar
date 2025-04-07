<template>
  <div class="modal-backdrop" v-if="show" @click.self="closeModal">
    <div class="modal-content" @click.stop>
      <div class="modal-header">
        <h2>{{ isEditMode ? 'ویرایش اطلاعات رسانه' : 'جزئیات رسانه' }}</h2>
        <button class="close-btn" @click="closeModal">
          <font-awesome-icon icon="fa-solid fa-times" />
        </button>
      </div>
      
      <div class="modal-body" v-if="media">
        <div class="media-preview">
          <img 
            v-if="isImage(media.type)" 
            :src="media.url" 
            :alt="media.name"
            class="media-image"
          />
          <audio 
            v-else-if="isAudio(media.type)" 
            controls
            class="media-audio"
          >
            <source :src="media.url" :type="media.type">
          </audio>
          <div v-else class="media-file">
            <font-awesome-icon icon="fa-solid fa-file" />
            <span>{{ getFileExtension(media.name) }}</span>
          </div>
        </div>
        
        <div class="media-details">
          <div class="detail-section">
            <div class="detail-item">
              <label>نام فایل:</label>
              <input 
                v-if="isEditMode" 
                type="text" 
                v-model="editedMedia.name" 
                class="detail-input"
              />
              <span v-else>{{ media.name }}</span>
            </div>
            
            <div class="detail-item">
              <label>نوع فایل:</label>
              <span>{{ formatType(media.type) }}</span>
            </div>
            
            <div class="detail-item">
              <label>حجم فایل:</label>
              <span>{{ formatSize(media.size) }}</span>
            </div>
            
            <div class="detail-item">
              <label>آدرس:</label>
              <div class="url-container">
                <input 
                  type="text" 
                  :value="media.url" 
                  readonly 
                  class="detail-input url-input"
                />
                <button @click="copyUrl(media.url)" class="copy-btn">
                  <font-awesome-icon icon="fa-solid fa-copy" />
                </button>
              </div>
            </div>
            
            <div class="detail-item" v-if="isEditMode">
              <label>توضیحات:</label>
              <textarea 
                v-model="editedMedia.description" 
                class="detail-textarea"
                placeholder="توضیحات فایل را وارد کنید..."
              ></textarea>
            </div>
            <div class="detail-item" v-else-if="media.description">
              <label>توضیحات:</label>
              <span>{{ media.description }}</span>
            </div>
            
            <div class="detail-item" v-if="isEditMode">
              <label>برچسب‌ها:</label>
              <div class="tags-input">
                <input 
                  type="text" 
                  v-model="tagInput"
                  @keydown.enter.prevent="addTag"
                  placeholder="برچسب جدید را وارد کنید و Enter را فشار دهید"
                  class="detail-input"
                />
                <div class="tags-container">
                  <span 
                    v-for="(tag, index) in editedMedia.tags" 
                    :key="index" 
                    class="tag"
                  >
                    {{ tag }}
                    <button @click="removeTag(index)" class="remove-tag">
                      <font-awesome-icon icon="fa-solid fa-times" />
                    </button>
                  </span>
                </div>
              </div>
            </div>
            <div class="detail-item" v-else-if="media.tags && media.tags.length">
              <label>برچسب‌ها:</label>
              <div class="tags-container">
                <span 
                  v-for="(tag, index) in media.tags" 
                  :key="index" 
                  class="tag view-tag"
                >
                  {{ tag }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div class="modal-footer">
        <button v-if="!isEditMode" @click="enableEditMode" class="edit-btn">
          <font-awesome-icon icon="fa-solid fa-pen" /> ویرایش
        </button>
        <template v-else>
          <button @click="cancelEdit" class="cancel-btn">
            <font-awesome-icon icon="fa-solid fa-times" /> لغو
          </button>
          <button @click="saveChanges" class="save-btn">
            <font-awesome-icon icon="fa-solid fa-save" /> ذخیره تغییرات
          </button>
        </template>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'MediaDetailsModal',
  props: {
    show: {
      type: Boolean,
      default: false
    },
    media: {
      type: Object,
      required: false,
      default: null
    }
  },
  data() {
    return {
      isEditMode: false,
      editedMedia: {},
      tagInput: ''
    };
  },
  watch: {
    show(newVal) {
      if (newVal && this.media) {
        this.resetEditState();
      }
    },
    media: {
      handler(newVal) {
        if (newVal) {
          this.resetEditState();
        }
      },
      deep: true
    }
  },
  methods: {
    resetEditState() {
      if (!this.media) return;
      
      this.isEditMode = false;
      // Create a deep copy of media object with tags array
      this.editedMedia = {
        ...JSON.parse(JSON.stringify(this.media)), 
        tags: this.media.tags ? [...this.media.tags] : [],
        description: this.media.description || ''
      };
      this.tagInput = '';
    },
    closeModal() {
      this.$emit('close');
    },
    enableEditMode() {
      this.isEditMode = true;
    },
    cancelEdit() {
      this.resetEditState();
      this.isEditMode = false;
    },
    saveChanges() {
      // Emit the edited media object
      this.$emit('save', this.editedMedia);
      this.isEditMode = false;
    },
    isImage(type) {
      return type && type.startsWith('image/');
    },
    isAudio(type) {
      return type && type.startsWith('audio/');
    },
    formatSize(size) {
      if (!size) return '0 KB';
      
      const kb = size / 1024;
      if (kb < 1024) {
        return `${kb.toFixed(2)} KB`;
      } else {
        return `${(kb / 1024).toFixed(2)} MB`;
      }
    },
    formatType(type) {
      if (!type) return '';
      
      const typeParts = type.split('/');
      if (typeParts.length === 2) {
        return typeParts[1].toUpperCase();
      }
      return type;
    },
    getFileExtension(filename) {
      if (!filename) return '';
      
      const parts = filename.split('.');
      if (parts.length > 1) {
        return parts[parts.length - 1].toUpperCase();
      }
      return '';
    },
    copyUrl(url) {
      if (!url) return;
      
      navigator.clipboard.writeText(url)
        .then(() => {
          this.$emit('notification', {
            type: 'success',
            message: 'لینک با موفقیت کپی شد'
          });
        })
        .catch(err => {
          console.error('مشکل در کپی لینک:', err);
          this.$emit('notification', {
            type: 'error',
            message: 'مشکل در کپی لینک'
          });
        });
    },
    addTag() {
      const tag = this.tagInput.trim();
      if (!tag) return;
      
      if (!this.editedMedia.tags) {
        this.editedMedia.tags = [];
      }
      
      if (!this.editedMedia.tags.includes(tag)) {
        this.editedMedia.tags.push(tag);
      }
      
      this.tagInput = '';
    },
    removeTag(index) {
      if (this.editedMedia.tags && index >= 0 && index < this.editedMedia.tags.length) {
        this.editedMedia.tags.splice(index, 1);
      }
    }
  }
};
</script>

<style scoped>
.modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1050;
}

.modal-content {
  background-color: white;
  border-radius: 8px;
  width: 90%;
  max-width: 800px;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
  box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
  overflow: hidden;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.5rem;
  border-bottom: 1px solid #dee2e6;
}

.modal-header h2 {
  margin: 0;
  font-size: 1.25rem;
  color: #343a40;
}

.close-btn {
  background: none;
  border: none;
  cursor: pointer;
  font-size: 1.25rem;
  color: #6c757d;
  padding: 0.25rem;
  transition: color 0.2s;
  font-family: inherit;
}

.close-btn:hover {
  color: #343a40;
}

.modal-body {
  flex: 1;
  overflow-y: auto;
  padding: 1.5rem;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.5rem;
}

.media-preview {
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #f8f9fa;
  border-radius: 8px;
  overflow: hidden;
  height: 100%;
  max-height: 300px;
}

.media-image {
  max-width: 100%;
  max-height: 300px;
  object-fit: contain;
}

.media-audio {
  width: 100%;
}

.media-file {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 2rem;
}

.media-file i {
  font-size: 4rem;
  color: #6c757d;
  margin-bottom: 1rem;
}

.media-file span {
  font-size: 1.25rem;
  font-weight: bold;
  color: #343a40;
}

.media-details {
  display: flex;
  flex-direction: column;
}

.detail-section {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.detail-item {
  display: flex;
  flex-direction: column;
}

.detail-item label {
  font-weight: 600;
  margin-bottom: 0.5rem;
  color: #343a40;
}

.detail-item span {
  color: #495057;
}

.detail-input {
  padding: 0.5rem;
  border: 1px solid #ced4da;
  border-radius: 4px;
  width: 100%;
}

.detail-textarea {
  padding: 0.5rem;
  border: 1px solid #ced4da;
  border-radius: 4px;
  width: 100%;
  min-height: 100px;
  resize: vertical;
}

.url-container {
  display: flex;
  align-items: center;
}

.url-input {
  flex: 1;
  border-top-right-radius: 0;
  border-bottom-right-radius: 0;
  background-color: #f8f9fa;
}

.copy-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  background-color: #f8f9fa;
  border: 1px solid #dee2e6;
  color: #495057;
  padding: 0.375rem 0.75rem;
  border-radius: 0 4px 4px 0;
  cursor: pointer;
  transition: all 0.2s;
  font-family: inherit;
}

.copy-btn:hover {
  background-color: #e9ecef;
}

.tags-input {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.tags-container {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.tag {
  display: inline-flex;
  align-items: center;
  padding: 0.25rem 0.5rem;
  background-color: #e9ecef;
  color: #495057;
  border-radius: 4px;
  font-size: 0.875rem;
}

.view-tag {
  background-color: #A79277;
  color: white;
}

.remove-tag {
  background: none;
  border: none;
  color: #6c757d;
  margin-left: 0.25rem;
  cursor: pointer;
  font-size: 0.75rem;
  padding: 0.125rem;
  border-radius: 50%;
}

.remove-tag:hover {
  background-color: rgba(0, 0, 0, 0.1);
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  padding: 1rem 1.5rem;
  border-top: 1px solid #dee2e6;
  gap: 0.75rem;
}

.edit-btn, .save-btn, .cancel-btn {
  padding: 0.5rem 1rem;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-family: inherit;
}

.edit-btn {
  background-color: #A79277;
  color: white;
  border: none;
}

.edit-btn:hover {
  background-color: #8a7660;
}

.save-btn {
  background-color: #28a745;
  color: white;
  border: none;
}

.save-btn:hover {
  background-color: #218838;
}

.cancel-btn {
  background-color: #f8f9fa;
  color: #6c757d;
  border: 1px solid #ced4da;
}

.cancel-btn:hover {
  background-color: #e9ecef;
}

.btn {
  padding: 0.5rem 1rem;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.2s;
  font-family: inherit;
}

@media (max-width: 768px) {
  .modal-body {
    grid-template-columns: 1fr;
    gap: 1rem;
  }
  
  .media-preview {
    max-height: 200px;
  }
}
</style> 