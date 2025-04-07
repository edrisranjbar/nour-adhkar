<template>
  <div class="media-grid-container">
    <div v-if="loading" class="loading-container">
      <div class="spinner"></div>
      <p>در حال بارگذاری...</p>
    </div>
    
    <div v-else-if="filteredMedia.length" class="media-grid">
      <div 
        v-for="item in filteredMedia" 
        :key="item.id" 
        class="media-item" 
        :class="{ 'selected': selectedItems.includes(item.id) }"
        @click="toggleSelection(item.id)"
      >
        <div class="selection-indicator" v-if="selectable">
          <font-awesome-icon 
            :icon="selectedItems.includes(item.id) ? 'fa-solid fa-check-circle' : 'fa-solid fa-circle'" 
          />
        </div>
        
        <div class="media-preview">
          <img v-if="isImage(item.type)" :src="item.url" :alt="item.name" @load="onImageLoad" />
          <div v-else-if="isAudio(item.type)" class="audio-preview">
            <font-awesome-icon icon="fa-solid fa-music" />
            <audio controls>
              <source :src="item.url" :type="item.type">
            </audio>
          </div>
          <div v-else class="file-preview">
            <font-awesome-icon icon="fa-solid fa-file" />
          </div>
        </div>
        
        <div class="media-info">
          <p class="media-name">{{ item.name }}</p>
          <p class="media-size">{{ formatSize(item.size) }}</p>
        </div>
        
        <div class="media-actions" @click.stop>
          <button @click="copyUrl(item.url)" title="کپی لینک">
            <font-awesome-icon icon="fa-solid fa-link" />
          </button>
          <button @click="$emit('edit', item)" title="ویرایش">
            <font-awesome-icon icon="fa-solid fa-pen" />
          </button>
          <button @click="$emit('delete', item.id)" title="حذف">
            <font-awesome-icon icon="fa-solid fa-trash" />
          </button>
        </div>
      </div>
    </div>
    
    <div v-else class="no-media">
      <font-awesome-icon icon="fa-solid fa-images" />
      <p>هیچ فایلی یافت نشد</p>
    </div>
  </div>
</template>

<script>
export default {
  name: 'MediaGrid',
  props: {
    mediaItems: {
      type: Array,
      required: true
    },
    loading: {
      type: Boolean,
      default: false
    },
    filter: {
      type: String,
      default: ''
    },
    selectable: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      selectedItems: []
    };
  },
  computed: {
    filteredMedia() {
      if (!this.filter) return this.mediaItems;
      
      const searchTerm = this.filter.toLowerCase();
      return this.mediaItems.filter(item => 
        item.name.toLowerCase().includes(searchTerm) ||
        item.type.toLowerCase().includes(searchTerm)
      );
    }
  },
  methods: {
    isImage(type) {
      return type.startsWith('image/');
    },
    isAudio(type) {
      return type.startsWith('audio/');
    },
    formatSize(size) {
      const kb = size / 1024;
      if (kb < 1024) {
        return `${kb.toFixed(2)} KB`;
      } else {
        return `${(kb / 1024).toFixed(2)} MB`;
      }
    },
    copyUrl(url) {
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
    toggleSelection(id) {
      if (!this.selectable) return;
      
      const index = this.selectedItems.indexOf(id);
      if (index === -1) {
        this.selectedItems.push(id);
      } else {
        this.selectedItems.splice(index, 1);
      }
      
      this.$emit('selection-change', this.selectedItems);
    },
    clearSelection() {
      this.selectedItems = [];
      this.$emit('selection-change', []);
    },
    selectAll() {
      this.selectedItems = this.filteredMedia.map(item => item.id);
      this.$emit('selection-change', this.selectedItems);
    },
    onImageLoad(e) {
      // Add fade-in effect when images load
      e.target.style.opacity = 1;
    }
  }
};
</script>

<style scoped>
.media-grid-container {
  width: 100%;
}

.media-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 1.5rem;
  margin-top: 1rem;
}

.media-item {
  position: relative;
  border: 1px solid #dee2e6;
  border-radius: 8px;
  overflow: hidden;
  background-color: white;
  transition: all 0.3s;
  box-shadow: 0 1px 3px rgba(0,0,0,0.05);
  cursor: pointer;
}

.media-item:hover {
  transform: translateY(-5px);
  box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.media-item.selected {
  border-color: #A79277;
  box-shadow: 0 0 0 2px rgba(167, 146, 119, 0.3);
}

.selection-indicator {
  position: absolute;
  top: 10px;
  right: 10px;
  z-index: 5;
  color: #A79277;
  font-size: 1.2rem;
  background: rgba(255, 255, 255, 0.8);
  border-radius: 50%;
  width: 25px;
  height: 25px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.media-preview {
  height: 150px;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #f8f9fa;
  border-bottom: 1px solid #dee2e6;
  position: relative;
}

.media-preview img {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
  opacity: 0;
  transition: opacity 0.3s ease;
}

.audio-preview, .file-preview {
  display: flex;
  flex-direction: column;
  align-items: center;
  width: 100%;
}

.audio-preview i, .file-preview i {
  font-size: 2.5rem;
  margin-bottom: 0.5rem;
  color: #6c757d;
}

.media-info {
  padding: 0.75rem;
}

.media-name {
  font-weight: 600;
  margin-bottom: 0.25rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  color: #343a40;
}

.media-size {
  color: #6c757d;
  font-size: 0.875rem;
}

.media-actions {
  display: flex;
  justify-content: flex-end;
  padding: 0.5rem;
  border-top: 1px solid #dee2e6;
  background-color: #f8f9fa;
}

.media-actions button {
  background: none;
  border: none;
  cursor: pointer;
  margin-left: 0.5rem;
  color: #6c757d;
  padding: 0.25rem;
  border-radius: 4px;
  transition: all 0.2s;
  font-family: inherit;
}

.media-actions button:hover {
  color: #A79277;
  background-color: rgba(167, 146, 119, 0.1);
}

.no-media {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  padding: 3rem 0;
  color: #6c757d;
  background-color: #f8f9fa;
  border-radius: 8px;
  border: 1px dashed #ced4da;
  margin-top: 1rem;
}

.no-media i {
  font-size: 3rem;
  margin-bottom: 1rem;
  color: #ced4da;
}

.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 3rem 0;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 4px solid rgba(167, 146, 119, 0.3);
  border-radius: 50%;
  border-top-color: #A79277;
  animation: spin 1s infinite linear;
  margin-bottom: 1rem;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

@media (max-width: 576px) {
  .media-grid {
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 1rem;
  }
  
  .media-preview {
    height: 120px;
  }
}
</style> 