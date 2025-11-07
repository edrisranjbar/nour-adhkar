<template>
  <div class="media-manage">
    
    <main class="container">
      <div class="header-section">
        <h1>مدیریت رسانه‌ها</h1>
        <button class="btn btn-primary" @click="openUploadDialog">
          <font-awesome-icon icon="fa-solid fa-cloud-upload-alt" /> آپلود فایل جدید
        </button>
      </div>
      
      <div class="media-filters">
        <div class="search-box">
          <input 
            type="text" 
            v-model="searchQuery" 
            placeholder="جستجوی فایل..." 
            class="search-input"
            @keyup.enter="applySearchFilter"
          />
          <button class="search-btn" @click="applySearchFilter">
            <font-awesome-icon icon="fa-solid fa-search" />
          </button>
        </div>
        
        <div class="filter-buttons">
          <button 
            @click="changeTypeFilter('all')" 
            :class="['filter-btn', currentFilter === 'all' ? 'active' : '']"
          >
            همه
          </button>
          <button 
            @click="changeTypeFilter('image')" 
            :class="['filter-btn', currentFilter === 'image' ? 'active' : '']"
          >
            <font-awesome-icon icon="fa-solid fa-image" /> تصاویر
          </button>
          <button 
            @click="changeTypeFilter('audio')" 
            :class="['filter-btn', currentFilter === 'audio' ? 'active' : '']"
          >
            <font-awesome-icon icon="fa-solid fa-music" /> صوت
          </button>
          <button 
            @click="changeTypeFilter('other')" 
            :class="['filter-btn', currentFilter === 'other' ? 'active' : '']"
          >
            <font-awesome-icon icon="fa-solid fa-file" /> سایر
          </button>
        </div>
      </div>
      
      <div class="bulk-actions" v-if="selectionMode">
        <button class="btn-select-all" @click="selectAllMedia">
          <font-awesome-icon icon="fa-solid fa-check-double" /> انتخاب همه
        </button>
        <button class="btn-deselect-all" @click="deselectAllMedia">
          <font-awesome-icon icon="fa-solid fa-times" /> لغو انتخاب
        </button>
        <button class="btn-delete-selected" @click="confirmDeleteSelected" :disabled="!selectedItems.length">
          <font-awesome-icon icon="fa-solid fa-trash" /> حذف انتخاب شده ({{ selectedItems.length }})
        </button>
        <button class="btn-exit-selection" @click="exitSelectionMode">
          <font-awesome-icon icon="fa-solid fa-arrow-left" /> خروج از حالت انتخاب
        </button>
      </div>
      <div class="selection-toggle" v-else>
        <button class="btn-enter-selection" @click="enterSelectionMode">
          <font-awesome-icon icon="fa-solid fa-check-square" /> حالت انتخاب
        </button>
      </div>
      
      <!-- Loading state -->
      <div v-if="loading" class="loading-container">
        <div class="spinner"></div>
        <p>در حال بارگذاری...</p>
      </div>
      
      <!-- No results message -->
      <div v-else-if="!mediaItems.length" class="no-results">
        <font-awesome-icon icon="fa-solid fa-images" size="3x" />
        <p>هیچ رسانه‌ای یافت نشد</p>
        <p v-if="searchQuery || currentFilter !== 'all'" class="hint">تلاش کنید فیلترها را تغییر دهید یا جستجوی دیگری انجام دهید</p>
      </div>
      
      <!-- Media Grid Component -->
      <MediaGrid 
        v-else
        :mediaItems="filteredMedia" 
        :loading="loading"
        :filter="searchQuery"
        :selectable="selectionMode"
        @delete="confirmDelete"
        @edit="openMediaDetails"
        @notification="showNotification"
        @selection-change="onSelectionChange"
        ref="mediaGrid"
      />
      
      <!-- Pagination controls -->
      <div v-if="!loading && mediaItems.length && pagination.totalPages > 1" class="pagination">
        <button 
          class="pagination-btn" 
          :disabled="pagination.currentPage === 1"
          @click="goToPage(pagination.currentPage - 1)"
        >
          <font-awesome-icon icon="fa-solid fa-chevron-right" />
        </button>
        
        <template v-for="page in paginationPages" :key="page">
          <button 
            v-if="page !== '...'" 
            :class="['pagination-page', pagination.currentPage === page ? 'active' : '']"
            @click="goToPage(page)"
          >
            {{ page }}
          </button>
          <span v-else class="pagination-ellipsis">...</span>
        </template>
        
        <button 
          class="pagination-btn" 
          :disabled="pagination.currentPage === pagination.totalPages"
          @click="goToPage(pagination.currentPage + 1)"
        >
          <font-awesome-icon icon="fa-solid fa-chevron-left" />
        </button>
      </div>
      
      <!-- Upload Dialog -->
      <div v-if="showUploadDialog" class="modal-container">
        <div class="modal-backdrop" @click="closeUploadDialog"></div>
        <div class="modal-content">
          <div class="modal-header">
            <h2>آپلود فایل جدید</h2>
            <button @click="closeUploadDialog" class="close-btn">
              <font-awesome-icon icon="fa-solid fa-times" />
            </button>
          </div>
          <div class="modal-body">
            <MediaUploader
              acceptedTypes="image/jpeg,image/png,image/gif,audio/mp3,audio/mpeg,application/pdf"
              :maxFileSize="20 * 1024 * 1024"
              :maxFiles="10"
              @upload-complete="handleUploadComplete"
              @notification="showNotification"
            />
          </div>
        </div>
      </div>
      
      <!-- Media Details Modal -->
      <MediaDetailsModal
        :show="showMediaDetails"
        :media="selectedMedia"
        @close="closeMediaDetails"
        @save="saveMediaChanges"
        @notification="showNotification"
      />
      
      <!-- Confirm Dialog for Delete -->
      <ConfirmDialog
        :show="showDeleteDialog"
        title="حذف فایل"
        message="آیا از حذف این فایل اطمینان دارید؟ این عمل قابل بازگشت نیست."
        confirmText="حذف"
        type="danger"
        @confirm="deleteMedia"
        @cancel="cancelDelete"
      />
      
      <!-- Confirm Dialog for Delete Multiple -->
      <ConfirmDialog
        :show="showDeleteMultipleDialog"
        title="حذف چندین فایل"
        :message="`آیا از حذف ${selectedItems.length} فایل انتخاب شده اطمینان دارید؟ این عمل قابل بازگشت نیست.`"
        confirmText="حذف"
        type="danger"
        @confirm="deleteSelectedMedia"
        @cancel="cancelDeleteMultiple"
      />
      
      <!-- Notification Toast -->
      <NotificationToast
        :notification="notification"
        @close="clearNotification"
      />
    </main>
  </div>
</template>

<script>
import MediaGrid from '@/components/Admin/MediaGrid.vue';
import MediaUploader from '@/components/Admin/MediaUploader.vue';
import MediaDetailsModal from '@/components/Admin/MediaDetailsModal.vue';
import ConfirmDialog from '@/components/Admin/ConfirmDialog.vue';
import NotificationToast from '@/components/Admin/NotificationToast.vue';
import { mediaService } from '@/services/mediaService';

export default {
  name: 'MediaManageView',
  components: {
    MediaGrid,
    MediaUploader,
    MediaDetailsModal,
    ConfirmDialog,
    NotificationToast
  },
  data() {
    return {
      loading: true,
      mediaItems: [],
      searchQuery: '',
      currentFilter: 'all',
      showUploadDialog: false,
      showMediaDetails: false,
      showDeleteDialog: false,
      showDeleteMultipleDialog: false,
      selectedMedia: null,
      mediaToDelete: null,
      notification: {
        type: '',
        message: ''
      },
      selectionMode: false,
      selectedItems: [],
      pagination: {
        currentPage: 1,
        totalPages: 1,
        totalItems: 0
      }
    };
  },
  computed: {
    paginationPages() {
      const pages = [];
      const currentPage = this.pagination.currentPage;
      const totalPages = this.pagination.totalPages;
      
      // Always show first page
      pages.push(1);
      
      // Calculate range around current page
      let startPage = Math.max(2, currentPage - 1);
      let endPage = Math.min(totalPages - 1, currentPage + 1);
      
      // Add ellipsis if needed before startPage
      if (startPage > 2) {
        pages.push('...');
      }
      
      // Add pages in the range
      for (let i = startPage; i <= endPage; i++) {
        pages.push(i);
      }
      
      // Add ellipsis if needed after endPage
      if (endPage < totalPages - 1) {
        pages.push('...');
      }
      
      // Always show last page if we have multiple pages
      if (totalPages > 1) {
        pages.push(totalPages);
      }
      
      return pages;
    },
    filteredMedia() {
      let result = [...this.mediaItems];
      
      // Filter by type is now handled by the API call
      // but we keep a local filtering for better UX
      if (this.currentFilter !== 'all') {
        switch (this.currentFilter) {
          case 'image':
            result = result.filter(item => item.type.startsWith('image/'));
            break;
          case 'audio':
            result = result.filter(item => item.type.startsWith('audio/'));
            break;
          case 'other':
            result = result.filter(item => 
              !item.type.startsWith('image/') && 
              !item.type.startsWith('audio/')
            );
            break;
        }
      }
      
      // Search filter is applied inside the MediaGrid component
      return result;
    }
  },
  created() {
    this.fetchMedia();
  },
  methods: {
    async fetchMedia() {
      this.loading = true;
      this.mediaItems = []; // Initialize as empty array to prevent undefined errors
      
      try {
        const options = {
          page: this.pagination.currentPage,
          type: this.currentFilter !== 'all' ? this.currentFilter : undefined,
          search: this.searchQuery || undefined
        };
        
        const response = await mediaService.getMedia(options);
        
        if (response && response.success) {
          // Handle Laravel pagination structure
          const paginationData = response.data;
          
          // Extract media items from the response
          // Laravel returns 'data' as the items array in pagination
          this.mediaItems = Array.isArray(paginationData.data) ? paginationData.data : [];
          
          // Set pagination information from Laravel's response
          this.pagination = {
            currentPage: paginationData.current_page || 1,
            totalPages: paginationData.last_page || 1,
            totalItems: paginationData.total || 0
          };
          
          console.log('Media items loaded:', this.mediaItems.length);
        } else {
          this.showNotification({
            type: 'error',
            message: response?.message || 'خطا در دریافت رسانه‌ها'
          });
        }
      } catch (error) {
        console.error('Error fetching media:', error);
        this.showNotification({
          type: 'error',
          message: error.message || 'خطا در دریافت رسانه‌ها'
        });
      } finally {
        this.loading = false;
      }
    },
    
    // Pagination methods
    goToPage(page) {
      if (page !== this.pagination.currentPage) {
        this.pagination.currentPage = page;
        this.fetchMedia();
      }
    },
    
    // Search and filter methods
    applySearchFilter() {
      this.pagination.currentPage = 1; // Reset to first page when applying new filters
      this.fetchMedia();
    },
    
    changeTypeFilter(type) {
      this.currentFilter = type;
      this.pagination.currentPage = 1; // Reset to first page when applying new filters
      this.fetchMedia();
    },
    
    // Media management methods
    openUploadDialog() {
      this.showUploadDialog = true;
    },
    
    closeUploadDialog() {
      this.showUploadDialog = false;
    },
    
    openMediaDetails(media) {
      this.selectedMedia = media;
      this.showMediaDetails = true;
    },
    
    closeMediaDetails() {
      this.showMediaDetails = false;
      this.selectedMedia = null;
    },
    
    confirmDelete(id) {
      this.mediaToDelete = id;
      this.showDeleteDialog = true;
    },
    
    cancelDelete() {
      this.showDeleteDialog = false;
      this.mediaToDelete = null;
    },
    
    async deleteMedia() {
      if (!this.mediaToDelete) return;
      
      try {
        const response = await mediaService.deleteMedia(this.mediaToDelete);
        
        if (response.success) {
          // Remove the deleted item from the local array
          this.mediaItems = this.mediaItems.filter(item => item.id !== this.mediaToDelete);
          
          this.showNotification({
            type: 'success',
            message: response.message || 'فایل با موفقیت حذف شد'
          });
        } else {
          this.showNotification({
            type: 'error',
            message: response.message || 'خطا در حذف فایل'
          });
        }
      } catch (error) {
        this.showNotification({
          type: 'error',
          message: error.message || 'خطا در حذف فایل'
        });
      } finally {
        this.showDeleteDialog = false;
        this.mediaToDelete = null;
      }
    },
    
    handleUploadComplete(mediaItems) {
      // Verify if we received valid media items
      if (Array.isArray(mediaItems) && mediaItems.length > 0) {
        // Show success notification
        this.showNotification({
          type: 'success',
          message: `${mediaItems.length} فایل با موفقیت آپلود شد`
        });
        
        // Refresh the media list to fetch the latest data from the server
        this.pagination.currentPage = 1; // Reset to first page to see the new uploads
        this.fetchMedia();
        
        // Close the upload dialog
        this.closeUploadDialog();
      } else {
        console.error('Invalid media items received:', mediaItems);
        // Show error notification if no media items were received
        this.showNotification({
          type: 'error',
          message: 'خطا در دریافت اطلاعات رسانه‌های آپلود شده'
        });
      }
    },
    
    async saveMediaChanges(editedMedia) {
      try {
        const response = await mediaService.updateMedia(editedMedia.id, editedMedia);
        
        if (response.success) {
          // Update the item in the local array
          const index = this.mediaItems.findIndex(item => item.id === editedMedia.id);
          if (index !== -1) {
            this.mediaItems.splice(index, 1, response.data.media);
          }
          
          this.showNotification({
            type: 'success',
            message: response.message || 'اطلاعات فایل با موفقیت بروزرسانی شد'
          });
        } else {
          this.showNotification({
            type: 'error',
            message: response.message || 'خطا در بروزرسانی فایل'
          });
        }
      } catch (error) {
        this.showNotification({
          type: 'error',
          message: error.message || 'خطا در بروزرسانی فایل'
        });
      }
    },
    
    showNotification(notification) {
      this.notification = notification;
      
      // Clear notification after 5 seconds
      setTimeout(() => {
        this.clearNotification();
      }, 5000);
    },
    
    clearNotification() {
      this.notification = {
        type: '',
        message: ''
      };
    },
    
    // Selection mode methods
    enterSelectionMode() {
      this.selectionMode = true;
      this.selectedItems = [];
    },
    
    exitSelectionMode() {
      this.selectionMode = false;
      this.selectedItems = [];
      // Reset the selection in the MediaGrid component
      if (this.$refs.mediaGrid) {
        this.$refs.mediaGrid.clearSelection();
      }
    },
    
    onSelectionChange(selectedIds) {
      this.selectedItems = selectedIds;
    },
    
    selectAllMedia() {
      if (this.$refs.mediaGrid) {
        this.$refs.mediaGrid.selectAll();
      }
    },
    
    deselectAllMedia() {
      if (this.$refs.mediaGrid) {
        this.$refs.mediaGrid.clearSelection();
      }
    },
    
    confirmDeleteSelected() {
      if (this.selectedItems.length === 0) return;
      
      this.showDeleteMultipleDialog = true;
    },
    
    cancelDeleteMultiple() {
      this.showDeleteMultipleDialog = false;
    },
    
    async deleteSelectedMedia() {
      if (this.selectedItems.length === 0) return;
      
      try {
        const response = await mediaService.deleteMultipleMedia(this.selectedItems);
        
        if (response.success) {
          // Remove the deleted items from the local array
          this.mediaItems = this.mediaItems.filter(item => !this.selectedItems.includes(item.id));
          
          this.showNotification({
            type: 'success',
            message: response.message || `${this.selectedItems.length} فایل با موفقیت حذف شد`
          });
          
          // Exit selection mode
          this.exitSelectionMode();
        } else {
          this.showNotification({
            type: 'error',
            message: response.message || 'خطا در حذف فایل‌ها'
          });
        }
      } catch (error) {
        this.showNotification({
          type: 'error',
          message: error.message || 'خطا در حذف فایل‌ها'
        });
      } finally {
        this.showDeleteMultipleDialog = false;
      }
    }
  }
};
</script>

<style scoped>
.media-manage {
  min-height: 100vh;
  background: var(--admin-bg);
  color: var(--admin-text);
}

.container {
  width: 100%;
  padding: 1.5rem 2rem 2.5rem;
}

.header-section {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.header-section h1 {
  margin: 0;
  font-size: 1.75rem;
  color: var(--admin-text);
}

.btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  border: none;
  border-radius: 8px;
  padding: 0.75rem 1.5rem;
  font-family: inherit;
  cursor: pointer;
  transition: transform 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
}

.btn-primary {
  background: var(--admin-accent);
  color: #fff;
}

.btn-primary:hover {
  background: rgba(59, 130, 246, 0.85);
  transform: translateY(-1px);
  box-shadow: 0 10px 20px rgba(59, 130, 246, 0.25);
}

.media-filters {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  margin-bottom: 1.5rem;
  align-items: center;
}

.search-box {
  position: relative;
  flex: 1 1 280px;
}

.search-input {
  width: 100%;
  padding: 0.75rem 2.75rem 0.75rem 0.75rem;
  border-radius: 8px;
  border: 1px solid var(--admin-border);
  background: rgba(255, 255, 255, 0.05);
  color: var(--admin-text);
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
  font-family: inherit;
}

.search-input::placeholder {
  color: var(--admin-muted);
}

.search-input:focus {
  outline: none;
  border-color: var(--admin-accent);
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.25);
}

.search-btn {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  color: var(--admin-muted);
  cursor: pointer;
  padding: 4px;
  border-radius: 4px;
  transition: color 0.2s ease, background-color 0.2s ease;
}

.search-btn:hover {
  color: var(--admin-accent);
  background: rgba(59, 130, 246, 0.12);
}

.filter-buttons {
  display: flex;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.filter-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.6rem 1rem;
  border-radius: 8px;
  border: 1px solid var(--admin-border);
  background: rgba(255, 255, 255, 0.03);
  color: var(--admin-text);
  cursor: pointer;
  transition: all 0.2s ease;
  font-family: inherit;
}

.filter-btn.active {
  background: var(--admin-accent);
  color: #fff;
  border-color: transparent;
}

.filter-btn:hover {
  border-color: var(--admin-accent);
}

.bulk-actions,
.selection-toggle {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
  margin-bottom: 1.25rem;
}

.bulk-actions button,
.selection-toggle button {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  border-radius: 8px;
  border: 1px solid var(--admin-border);
  background: rgba(255, 255, 255, 0.05);
  color: var(--admin-text);
  padding: 0.6rem 1rem;
  cursor: pointer;
  transition: background-color 0.2s ease, border-color 0.2s ease, transform 0.2s ease;
  font-family: inherit;
}

.bulk-actions button:hover,
.selection-toggle button:hover {
  background: var(--admin-accent);
  border-color: transparent;
  color: #fff;
  transform: translateY(-1px);
}

.btn-delete-selected {
  background: rgba(248, 113, 113, 0.15);
  border-color: rgba(248, 113, 113, 0.35);
  color: #f87171;
}

.btn-delete-selected:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.loading-container,
.no-results {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 4rem 0;
  border-radius: 12px;
  border: 1px dashed var(--admin-border);
  background: rgba(255, 255, 255, 0.04);
  color: var(--admin-muted);
  margin-top: 1.5rem;
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

.no-results .hint {
  font-size: 0.9rem;
  margin-top: 0.5rem;
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  flex-wrap: wrap;
  gap: 0.5rem;
  margin-top: 1.75rem;
}

.pagination-btn,
.pagination-page {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 38px;
  height: 38px;
  border-radius: 8px;
  border: 1px solid var(--admin-border);
  background: rgba(255, 255, 255, 0.05);
  color: var(--admin-text);
  cursor: pointer;
  transition: all 0.2s ease;
  font-family: inherit;
}

.pagination-page.active {
  background: var(--admin-accent);
  color: #fff;
  border-color: transparent;
}

.pagination-btn:hover,
.pagination-page:hover:not(.active) {
  background: var(--admin-accent);
  border-color: transparent;
  color: #fff;
}

.pagination-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.pagination-ellipsis {
  color: var(--admin-muted);
  min-width: 38px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.modal-container {
  position: fixed;
  inset: 0;
  z-index: 1000;
  display: flex;
  align-items: center;
  justify-content: center;
}

.modal-backdrop {
  position: absolute;
  inset: 0;
  background: rgba(8, 11, 19, 0.75);
}

.modal-content {
  position: relative;
  background: var(--admin-surface);
  border-radius: 14px;
  width: 90%;
  max-width: 700px;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
  box-shadow: 0 24px 70px rgba(15, 23, 42, 0.6);
  border: 1px solid var(--admin-border);
  overflow: hidden;
  z-index: 1001;
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
  font-size: 1.25rem;
  color: var(--admin-text);
}

.close-btn {
  background: none;
  border: none;
  font-size: 1.25rem;
  color: var(--admin-muted);
  cursor: pointer;
  transition: color 0.2s ease;
  font-family: inherit;
}

.close-btn:hover {
  color: var(--admin-text);
}

.modal-body {
  flex: 1;
  overflow-y: auto;
  padding: 1.5rem;
}

@media (max-width: 768px) {
  .header-section {
    flex-direction: column;
    align-items: flex-start;
  }

  .filter-buttons {
    width: 100%;
  }

  .bulk-actions,
  .selection-toggle {
    flex-direction: column;
    align-items: stretch;
  }
}
</style>
