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
  inset: 0;
  background: rgba(8, 11, 19, 0.75);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1100;
}

.modal-content {
  background: var(--admin-surface);
  border-radius: 14px;
  width: 95%;
  max-width: 760px;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
  box-shadow: 0 24px 70px rgba(15, 23, 42, 0.6);
  border: 1px solid var(--admin-border);
  color: var(--admin-text);
  overflow: hidden;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.25rem 1.75rem;
  border-bottom: 1px solid var(--admin-border);
}

.modal-header h2 {
  margin: 0;
  font-size: 1.35rem;
  color: var(--admin-text);
}

.close-btn {
  background: none;
  border: none;
  color: var(--admin-muted);
  font-size: 1.2rem;
  cursor: pointer;
  transition: color 0.2s ease;
  font-family: inherit;
}

.close-btn:hover {
  color: var(--admin-text);
}

.modal-body {
  padding: 1.5rem;
  overflow-y: auto;
  display: grid;
  gap: 1.5rem;
}

.media-preview {
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(255, 255, 255, 0.04);
  border: 1px dashed var(--admin-border);
  border-radius: 12px;
  padding: 1.5rem;
}

.media-image {
  max-width: 100%;
  max-height: 320px;
  object-fit: contain;
  border-radius: 8px;
}

.media-audio {
  width: 100%;
}

.media-file {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  font-size: 1.2rem;
  color: var(--admin-muted);
}

.media-details {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.detail-section {
  display: grid;
  gap: 1.1rem;
}

.detail-item label {
  display: block;
  font-weight: 600;
  color: var(--admin-muted);
  margin-bottom: 0.35rem;
}

.detail-item span {
  color: var(--admin-text);
}

.detail-input,
.detail-textarea {
  width: 100%;
  border-radius: 8px;
  border: 1px solid var(--admin-border);
  background: rgba(255, 255, 255, 0.04);
  color: var(--admin-text);
  padding: 0.6rem 0.75rem;
  font-family: inherit;
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.detail-input:focus,
.detail-textarea:focus {
  outline: none;
  border-color: var(--admin-accent);
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.25);
}

.detail-textarea {
  min-height: 120px;
  resize: vertical;
}

.url-container {
  display: flex;
  gap: 0.5rem;
  align-items: center;
}

.url-input {
  flex: 1;
}

.copy-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0.55rem 0.75rem;
  border-radius: 8px;
  border: 1px solid var(--admin-border);
  background: rgba(255, 255, 255, 0.05);
  color: var(--admin-text);
  cursor: pointer;
  transition: all 0.2s ease;
}

.copy-btn:hover {
  background: var(--admin-accent);
  border-color: transparent;
  color: #fff;
}

.detail-item textarea::placeholder,
.detail-input::placeholder {
  color: var(--admin-muted);
}

.tags-input {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.tags-container {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.tag {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  background: rgba(59, 130, 246, 0.12);
  color: var(--admin-text);
  border-radius: 999px;
  padding: 0.35rem 0.75rem;
  font-size: 0.85rem;
}

.view-tag {
  background: rgba(255, 255, 255, 0.06);
}

.remove-tag {
  background: none;
  border: none;
  color: var(--admin-muted);
  cursor: pointer;
  font-size: 0.85rem;
  transition: color 0.2s ease;
}

.remove-tag:hover {
  color: var(--admin-text);
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  padding: 1.25rem 1.75rem;
  border-top: 1px solid var(--admin-border);
}

.edit-btn,
.cancel-btn,
.save-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  border-radius: 8px;
  border: none;
  padding: 0.65rem 1.35rem;
  cursor: pointer;
  font-family: inherit;
  transition: transform 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
}

.edit-btn {
  background: var(--admin-accent);
  color: #fff;
}

.edit-btn:hover {
  background: rgba(59, 130, 246, 0.85);
  transform: translateY(-1px);
  box-shadow: 0 10px 20px rgba(59, 130, 246, 0.25);
}

.cancel-btn {
  background: rgba(148, 163, 184, 0.2);
  color: var(--admin-muted);
}

.cancel-btn:hover {
  background: rgba(148, 163, 184, 0.3);
  transform: translateY(-1px);
}

.save-btn {
  background: #4ade80;
  color: #0f172a;
}

.save-btn:hover {
  background: #22c55e;
  transform: translateY(-1px);
  box-shadow: 0 10px 20px rgba(34, 197, 94, 0.2);
}

@media (min-width: 720px) {
  .modal-body {
    grid-template-columns: 1fr 1.1fr;
  }
}
</style> 