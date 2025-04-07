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
.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px 16px;
}

.header-section {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
  border-bottom: 1px solid #e9ecef;
  padding-bottom: 1rem;
}

h1 {
  font-size: 1.5rem;
  margin-bottom: 0;
  color: #343a40;
}

.btn {
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.2s;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  font-family: inherit;
}

.btn-primary {
  background-color: #A79277;
  color: white;
}

.btn-primary:hover {
  background-color: #8a7660;
}

.media-filters {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
  flex-wrap: wrap;
  gap: 1rem;
}

.search-box {
  position: relative;
  flex: 1;
  max-width: 400px;
}

.search-input {
  padding: 0.5rem 1rem 0.5rem 2.5rem;
  border: 1px solid #ced4da;
  border-radius: 4px;
  width: 100%;
  background-color: white;
  transition: border-color 0.15s ease-in-out;
}

.search-input:focus {
  outline: none;
  border-color: #A79277;
  box-shadow: 0 0 0 0.2rem rgba(167, 146, 119, 0.25);
}

.search-icon {
  position: absolute;
  left: 10px;
  top: 50%;
  transform: translateY(-50%);
  color: #6c757d;
}

.filter-buttons {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.filter-btn {
  background-color: #f8f9fa;
  border: 1px solid #ced4da;
  color: #6c757d;
  padding: 0.5rem 1rem;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.2s;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  font-family: inherit;
}

.filter-btn:hover, .filter-btn.active {
  background-color: #A79277;
  border-color: #A79277;
  color: white;
}

.bulk-actions, .selection-toggle {
  margin-bottom: 1.5rem;
  display: flex;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.btn-select-all, .btn-deselect-all, .btn-delete-selected, .btn-exit-selection, .btn-enter-selection {
  padding: 0.5rem 1rem;
  border-radius: 4px;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 500;
  transition: all 0.2s;
  font-family: inherit;
}

.btn-select-all {
  background-color: #f8f9fa;
  border: 1px solid #ced4da;
  color: #6c757d;
}

.btn-select-all:hover {
  background-color: #e9ecef;
}

.btn-deselect-all {
  background-color: #f8f9fa;
  border: 1px solid #ced4da;
  color: #6c757d;
}

.btn-deselect-all:hover {
  background-color: #e9ecef;
}

.btn-delete-selected {
  background-color: #dc3545;
  border: none;
  color: white;
}

.btn-delete-selected:hover {
  background-color: #c82333;
}

.btn-delete-selected:disabled {
  background-color: #e9ecef;
  color: #6c757d;
  cursor: not-allowed;
}

.btn-exit-selection {
  background-color: #6c757d;
  border: none;
  color: white;
}

.btn-exit-selection:hover {
  background-color: #5a6268;
}

.btn-enter-selection {
  background-color: #A79277;
  border: none;
  color: white;
}

.btn-enter-selection:hover {
  background-color: #8a7660;
}

/* Modal Styles */
.modal-container {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  z-index: 1000;
  display: flex;
  align-items: center;
  justify-content: center;
}

.modal-backdrop {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  cursor: pointer;
}

.modal-content {
  position: relative;
  background-color: white;
  border-radius: 8px;
  width: 90%;
  max-width: 700px;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
  box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
  overflow: hidden;
  z-index: 1001;
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
  font-size: 1.25rem;
  color: #6c757d;
  cursor: pointer;
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
}

@media (max-width: 768px) {
  .media-filters {
    flex-direction: column;
    align-items: stretch;
  }
  
  .search-box {
    max-width: none;
  }
  
  .filter-buttons {
    justify-content: center;
  }
  
  .bulk-actions, .selection-toggle {
    flex-direction: column;
  }
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

.no-results {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 4rem 0;
  color: #6c757d;
  background-color: #f8f9fa;
  border-radius: 8px;
  border: 1px dashed #ced4da;
}

.no-results .hint {
  font-size: 0.9rem;
  margin-top: 0.5rem;
  color: #868e96;
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  margin-top: 2rem;
  gap: 0.5rem;
}

.pagination-btn, .pagination-page {
  display: flex;
  align-items: center;
  justify-content: center;
  min-width: 38px;
  height: 38px;
  padding: 0 0.75rem;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.2s;
  font-family: inherit;
  background-color: #f8f9fa;
  border: 1px solid #ced4da;
  color: #6c757d;
}

.pagination-page {
  font-weight: 500;
}

.pagination-page.active {
  background-color: #A79277;
  color: white;
  border-color: #A79277;
}

.pagination-btn:hover, .pagination-page:hover:not(.active) {
  background-color: #e9ecef;
}

.pagination-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.pagination-ellipsis {
  display: flex;
  align-items: center;
  justify-content: center;
  min-width: 38px;
  height: 38px;
}

.search-btn {
  position: absolute;
  left: 10px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  color: #6c757d;
  cursor: pointer;
  padding: 5px;
  border-radius: 4px;
  transition: all 0.2s;
}

.search-btn:hover {
  color: #A79277;
  background-color: rgba(167, 146, 119, 0.1);
}
</style>
