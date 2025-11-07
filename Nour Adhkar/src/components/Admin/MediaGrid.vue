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
  border: 1px solid var(--admin-border);
  border-radius: 12px;
  overflow: hidden;
  background-color: var(--admin-surface);
  transition: all 0.3s ease;
  box-shadow: 0 12px 32px rgba(15, 23, 42, 0.35);
  cursor: pointer;
  color: var(--admin-text);
}

.media-item:hover {
  transform: translateY(-6px);
  box-shadow: 0 20px 40px rgba(15, 23, 42, 0.45);
}

.media-item.selected {
  border-color: var(--admin-accent);
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.4);
}

.selection-indicator {
  position: absolute;
  top: 10px;
  right: 10px;
  z-index: 5;
  color: var(--admin-accent);
  font-size: 1.1rem;
  background: rgba(8, 11, 19, 0.7);
  border-radius: 50%;
  width: 28px;
  height: 28px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.media-preview {
  height: 150px;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: rgba(255, 255, 255, 0.03);
  border-bottom: 1px solid var(--admin-border);
  position: relative;
}

.media-preview img {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
  opacity: 0;
  transition: opacity 0.3s ease;
}

.audio-preview,
.file-preview {
  display: flex;
  flex-direction: column;
  align-items: center;
  width: 100%;
}

.audio-preview i,
.file-preview i {
  font-size: 2.5rem;
  margin-bottom: 0.5rem;
  color: var(--admin-muted);
}

.media-info {
  padding: 0.85rem;
}

.media-name {
  font-weight: 600;
  margin-bottom: 0.25rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  color: var(--admin-text);
}

.media-size {
  color: var(--admin-muted);
  font-size: 0.85rem;
}

.media-actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.5rem;
  padding: 0.6rem 0.75rem;
  border-top: 1px solid var(--admin-border);
  background-color: rgba(255, 255, 255, 0.03);
}

.media-actions button {
  background: none;
  border: none;
  cursor: pointer;
  color: var(--admin-muted);
  padding: 0.35rem;
  border-radius: 6px;
  transition: color 0.2s ease, background-color 0.2s ease, transform 0.2s ease;
  font-family: inherit;
}

.media-actions button:hover {
  color: var(--admin-accent);
  background-color: rgba(59, 130, 246, 0.12);
  transform: translateY(-1px);
}

.no-media,
.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 3rem 0;
  color: var(--admin-muted);
  background-color: rgba(255, 255, 255, 0.04);
  border-radius: 12px;
  border: 1px dashed var(--admin-border);
  margin-top: 1rem;
}

.no-media i {
  font-size: 3rem;
  margin-bottom: 1rem;
  color: var(--admin-border);
}

.spinner {
  width: 40px;
  height: 40px;
  border: 4px solid rgba(59, 130, 246, 0.2);
  border-radius: 50%;
  border-top-color: var(--admin-accent);
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