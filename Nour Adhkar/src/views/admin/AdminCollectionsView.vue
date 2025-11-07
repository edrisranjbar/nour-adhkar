<template>
  <div class="admin-collections">
    <div class="header">
      <h1>مدیریت مجموعه‌های اذکار</h1>
      <button class="add-button" @click="showAddForm = true">
        <font-awesome-icon icon="fa-solid fa-plus" />
        افزودن مجموعه جدید
      </button>
    </div>

    <!-- Search and Filters -->
    <div class="filters">
      <div class="search-box">
        <font-awesome-icon icon="fa-solid fa-search" class="search-icon" />
        <input 
          type="text" 
          v-model="searchQuery" 
          placeholder="جستجو در مجموعه‌ها..." 
          class="search-input"
          @input="handleSearch"
        />
      </div>
      
      <div class="filter-actions">
        <select v-model="sortBy" class="filter-select" @change="applyFilters">
          <option value="created_at_desc">جدیدترین</option>
          <option value="created_at_asc">قدیمی‌ترین</option>
          <option value="title_asc">عنوان (الف تا ی)</option>
          <option value="title_desc">عنوان (ی تا الف)</option>
        </select>
      </div>
    </div>

    <!-- Add/Edit Form -->
    <div v-if="showAddForm" class="form-overlay">
      <div class="form-container">
        <h2>{{ editingCollection ? 'ویرایش مجموعه' : 'افزودن مجموعه جدید' }}</h2>
        <form @submit.prevent="saveCollection">
          <div class="form-group">
            <label for="title">عنوان</label>
            <input
              id="title"
              v-model="formData.title"
              type="text"
              required
              placeholder="عنوان مجموعه را وارد کنید"
            />
          </div>

          <div class="form-group">
            <label for="description">توضیحات</label>
            <textarea
              id="description"
              v-model="formData.description"
              placeholder="توضیحات مجموعه را وارد کنید"
            ></textarea>
          </div>

          <div class="form-group">
            <label>اذکار مجموعه</label>
            <div class="adhkar-selection">
              <div v-for="dhikr in availableAdhkar" :key="dhikr.id" class="dhikr-item">
                <label class="checkbox-label">
                  <input 
                    type="checkbox" 
                    :value="dhikr.id"
                    v-model="formData.adhkarIds"
                  />
                  <span class="dhikr-title">{{ dhikr.title || dhikr.arabic_text.substring(0, 30) + '...' }}</span>
                </label>
              </div>
            </div>
          </div>

          <div class="form-actions">
            <button type="button" class="cancel-button" @click="closeForm">
              انصراف
            </button>
            <button type="submit" class="save-button">
              {{ editingCollection ? 'ذخیره تغییرات' : 'افزودن مجموعه' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Collections Table -->
    <div v-if="loading" class="loading-state">
      <font-awesome-icon icon="fa-solid fa-spinner" spin />
      <span>در حال بارگذاری...</span>
    </div>

    <div v-else-if="!collections.length" class="empty-state">
      <font-awesome-icon icon="fa-solid fa-book" class="empty-icon" />
      <p>هیچ مجموعه‌ای یافت نشد</p>
      <p class="subtitle">برای افزودن مجموعه جدید روی دکمه بالا کلیک کنید</p>
    </div>

    <div v-else class="collections-table-container">
      <table class="collections-table">
        <thead>
          <tr>
            <th>شناسه</th>
            <th>عنوان</th>
            <th>تعداد اذکار</th>
            <th>توضیحات</th>
            <th>عملیات</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="collection in paginatedCollections" :key="collection.id">
            <td>{{ collection.id }}</td>
            <td>{{ collection.title }}</td>
            <td>{{ collection.adhkar?.length || 0 }}</td>
            <td class="description-cell">{{ truncateDescription(collection.description) }}</td>
            <td class="actions">
              <ActionButton
                type="edit"
                title="ویرایش"
                @click="editCollection(collection)"
              />
              <ActionButton
                type="delete"
                title="حذف"
                @click="confirmDelete(collection)"
              />
            </td>
          </tr>
        </tbody>
      </table>
      
      <!-- Pagination -->
      <Pagination
        :pagination="pagination"
        @page-change="changePage"
      />
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteDialog" class="modal-overlay" @click.self="closeDeleteDialog">
      <div class="modal-container">
        <div class="modal-header">
          <font-awesome-icon icon="fa-solid fa-trash" class="delete-icon" />
          <h2>حذف مجموعه</h2>
        </div>
        <p>آیا از حذف این مجموعه اطمینان دارید؟</p>
        <div class="modal-actions">
          <button @click="deleteCollection" class="btn-danger">
            <font-awesome-icon icon="fa-solid fa-trash" />
            حذف
          </button>
          <button @click="closeDeleteDialog" class="btn-secondary">انصراف</button>
        </div>
      </div>
    </div>

    <!-- Notification Toast -->
    <NotificationToast
      :notification="notification"
      @close="clearNotification"
    />
  </div>
</template>

<script>
import axios from 'axios';
import { BASE_API_URL } from '@/config';
import NotificationToast from '@/components/Admin/NotificationToast.vue';
import ActionButton from '@/components/Admin/ActionButton.vue';
import Pagination from '@/components/Admin/Pagination.vue';
import { library } from '@fortawesome/fontawesome-svg-core';
import { 
  faPlus, 
  faSearch, 
  faEdit, 
  faTrash, 
  faBook, 
  faSpinner 
} from '@fortawesome/free-solid-svg-icons';

// Add icons to the library
library.add(faPlus, faSearch, faEdit, faTrash, faBook, faSpinner);

export default {
  name: 'AdminCollectionsView',
  components: {
    NotificationToast,
    ActionButton,
    Pagination
  },
  data() {
    return {
      loading: true,
      collections: [],
      availableAdhkar: [],
      searchQuery: '',
      sortBy: 'created_at_desc',
      showAddForm: false,
      showDeleteDialog: false,
      editingCollection: null,
      collectionToDelete: null,
      formData: {
        title: '',
        description: '',
        adhkarIds: []
      },
      notification: {
        type: '',
        message: ''
      },
      // Pagination
      pagination: {
        current_page: 1,
        last_page: 1,
        total: 0,
        per_page: 10
      }
    }
  },
  computed: {
    filteredCollections() {
      let filtered = [...this.collections];
      
      // Apply search filter
      if (this.searchQuery) {
        const query = this.searchQuery.toLowerCase();
        filtered = filtered.filter(collection => 
          (collection.title && collection.title.toLowerCase().includes(query)) ||
          (collection.description && collection.description.toLowerCase().includes(query))
        );
      }
      
      // Apply sorting
      const [field, order] = this.sortBy.split('_');
      filtered.sort((a, b) => {
        let aValue = a[field] || '';
        let bValue = b[field] || '';
        
        return order === 'asc' ? 
          (aValue > bValue ? 1 : -1) : 
          (aValue < bValue ? 1 : -1);
      });
      
      return filtered;
    },
    paginatedCollections() {
      const startIndex = (this.pagination.current_page - 1) * this.pagination.per_page;
      return this.filteredCollections.slice(startIndex, startIndex + this.pagination.per_page);
    },
  },
  methods: {
    async loadCollections() {
      this.loading = true;
      try {
        const response = await axios.get(`${BASE_API_URL}/admin/collections`, {
          headers: {
            Authorization: `Bearer ${this.$store.state.token}`
          }
        });
        this.collections = response.data.collections || [];
      } catch (error) {
        console.error('Error loading collections:', error);
        this.showNotification('خطا در بارگذاری مجموعه‌ها', 'error');
      } finally {
        this.loading = false;
      }
    },
    async loadAdhkar() {
      try {
        const response = await axios.get(`${BASE_API_URL}/admin/adhkar`, {
          headers: {
            Authorization: `Bearer ${this.$store.state.token}`
          }
        });
        this.availableAdhkar = response.data.adhkar || [];
      } catch (error) {
        console.error('Error loading adhkar:', error);
        this.showNotification('خطا در بارگذاری اذکار', 'error');
      }
    },
    handleSearch() {
      this.pagination.current_page = 1; // Reset to first page on search
      this.updatePagination();
    },
    applyFilters() {
      this.updatePagination();
    },
    updatePagination() {
      this.pagination.total = this.filteredCollections.length;
      this.pagination.last_page = Math.ceil(this.filteredCollections.length / this.pagination.per_page);
    },
    changePage(page) {
      this.pagination.current_page = page;
      window.scrollTo(0, 0);
    },
    truncateDescription(description) {
      if (!description) return '';
      return description.length > 50 ? description.substring(0, 50) + '...' : description;
    },
    resetForm() {
      this.formData = {
        title: '',
        description: '',
        adhkarIds: []
      };
      this.editingCollection = null;
    },
    closeForm() {
      this.showAddForm = false;
      this.resetForm();
    },
    editCollection(collection) {
      this.editingCollection = collection;
      this.formData = {
        title: collection.title,
        description: collection.description,
        adhkarIds: collection.adhkar?.map(dhikr => dhikr.id) || []
      };
      this.showAddForm = true;
    },
    async saveCollection() {
      try {
        if (this.editingCollection) {
          await axios.put(
            `${BASE_API_URL}/admin/collections/${this.editingCollection.id}`,
            this.formData,
            {
              headers: {
                Authorization: `Bearer ${this.$store.state.token}`
              }
            }
          );
          this.showNotification('مجموعه با موفقیت ویرایش شد', 'success');
        } else {
          await axios.post(
            `${BASE_API_URL}/admin/collections`,
            this.formData,
            {
              headers: {
                Authorization: `Bearer ${this.$store.state.token}`
              }
            }
          );
          this.showNotification('مجموعه جدید با موفقیت اضافه شد', 'success');
        }
        this.closeForm();
        await this.loadCollections();
      } catch (error) {
        console.error('Error saving collection:', error);
        this.showNotification('خطا در ذخیره مجموعه', 'error');
      }
    },
    confirmDelete(collection) {
      this.collectionToDelete = collection;
      this.showDeleteDialog = true;
    },
    closeDeleteDialog() {
      this.showDeleteDialog = false;
      this.collectionToDelete = null;
    },
    async deleteCollection() {
      if (!this.collectionToDelete) return;
      
      try {
        await axios.delete(
          `${BASE_API_URL}/admin/collections/${this.collectionToDelete.id}`,
          {
            headers: {
              Authorization: `Bearer ${this.$store.state.token}`
            }
          }
        );
        this.showNotification('مجموعه با موفقیت حذف شد', 'success');
        this.closeDeleteDialog();
        await this.loadCollections();
      } catch (error) {
        console.error('Error deleting collection:', error);
        this.showNotification('خطا در حذف مجموعه', 'error');
      }
    },
    showNotification(message, type = 'success') {
      this.notification = {
        message,
        type
      };
      
      // Auto-hide after 3 seconds
      setTimeout(() => {
        this.clearNotification();
      }, 3000);
    },
    clearNotification() {
      this.notification = {
        type: '',
        message: ''
      };
    }
  },
  watch: {
    filteredCollections() {
      this.updatePagination();
    }
  },
  async created() {
    await Promise.all([
      this.loadCollections(),
      this.loadAdhkar()
    ]);
    this.updatePagination();
  }
}
</script>

<style scoped>
.admin-collections {
  padding: 1.5rem;
  background: var(--admin-bg);
  color: var(--admin-text);
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.add-button {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  background-color: var(--admin-accent);
  color: #fff;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  transition: background-color 0.2s ease;
  font-family: inherit;
}

.add-button:hover {
  background-color: rgba(59, 130, 246, 0.85);
}

.filters {
  display: flex;
  gap: 1rem;
  margin-bottom: 2rem;
}

.search-box {
  flex: 1;
  position: relative;
}

.search-icon {
  position: absolute;
  right: 1rem;
  top: 50%;
  transform: translateY(-50%);
  color: var(--admin-muted);
}

.search-input {
  width: 100%;
  padding: 0.75rem 2.5rem 0.75rem 1rem;
  border: 1px solid var(--admin-border);
  border-radius: 6px;
  font-size: 1rem;
  font-family: inherit;
  background-color: rgba(255, 255, 255, 0.03);
  color: var(--admin-text);
}

.search-input::placeholder {
  color: var(--admin-muted);
}

.filter-actions {
  display: flex;
  gap: 1rem;
}

.filter-select {
  padding: 0.75rem 1rem;
  border: 1px solid var(--admin-border);
  border-radius: 6px;
  font-size: 1rem;
  min-width: 150px;
  font-family: inherit;
  background-color: rgba(255, 255, 255, 0.03);
  color: var(--admin-text);
}

/* Table Styles */
.collections-table-container {
  background: var(--admin-surface);
  border-radius: 10px;
  box-shadow: 0 8px 24px rgba(15, 23, 42, 0.35);
  border: 1px solid var(--admin-border);
  overflow: hidden;
}

.collections-table {
  width: 100%;
  border-collapse: collapse;
}

.collections-table th,
.collections-table td {
  padding: 1rem;
  text-align: right;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  color: var(--admin-text);
}

.collections-table th {
  background: rgba(255, 255, 255, 0.04);
  font-weight: 600;
  text-transform: uppercase;
  font-size: 0.85rem;
  letter-spacing: 0.05em;
}

.collections-table tr:hover {
  background-color: rgba(59, 130, 246, 0.08);
}

.description-cell {
  max-width: 300px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  color: var(--admin-muted);
}

.actions {
  display: flex;
  gap: 0.5rem;
  justify-content: center;
}

/* Form Styles */
.form-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(8, 11, 19, 0.75);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.form-container {
  background: var(--admin-surface);
  border-radius: 12px;
  padding: 2rem;
  width: 90%;
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
  border: 1px solid var(--admin-border);
  box-shadow: 0 16px 48px rgba(15, 23, 42, 0.45);
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  color: var(--admin-text);
  font-weight: 500;
}

.form-group input,
.form-group textarea {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid var(--admin-border);
  border-radius: 6px;
  font-size: 1rem;
  font-family: inherit;
  background-color: rgba(255, 255, 255, 0.03);
  color: var(--admin-text);
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.form-group input::placeholder,
.form-group textarea::placeholder {
  color: var(--admin-muted);
}

.form-group textarea {
  min-height: 120px;
  resize: vertical;
}

.form-group input:focus,
.form-group textarea:focus {
  outline: none;
  border-color: var(--admin-accent);
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.25);
}

.adhkar-selection {
  max-height: 300px;
  overflow-y: auto;
  border: 1px solid var(--admin-border);
  border-radius: 6px;
  padding: 1rem;
  background: rgba(255, 255, 255, 0.02);
}

.dhikr-item {
  padding: 0.5rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  color: var(--admin-text);
}

.dhikr-item:last-child {
  border-bottom: none;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
}

.dhikr-title {
  font-size: 0.9rem;
  color: var(--admin-text);
}

.form-actions {
  display: flex;
  gap: 1rem;
  margin-top: 2rem;
}

.cancel-button,
.save-button {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 1rem;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  font-family: inherit;
}

.cancel-button {
  background-color: rgba(148, 163, 184, 0.15);
  color: var(--admin-muted);
}

.save-button {
  background-color: var(--admin-accent);
  color: #fff;
}

.cancel-button:hover {
  transform: translateY(-1px);
  box-shadow: 0 8px 16px rgba(148, 163, 184, 0.2);
}

.save-button:hover {
  transform: translateY(-1px);
  box-shadow: 0 10px 20px rgba(59, 130, 246, 0.3);
}

/* Other States */
.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
  padding: 3rem;
  color: var(--admin-muted);
}

.empty-state {
  text-align: center;
  padding: 3rem 1rem;
  background: var(--admin-surface);
  border: 1px solid var(--admin-border);
  border-radius: 8px;
  margin: 2rem 0;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.18);
  color: var(--admin-text);
}

.empty-icon {
  font-size: 3rem;
  color: var(--admin-accent);
  margin-bottom: 1rem;
}

.subtitle {
  color: var(--admin-muted);
  margin-top: 0.5rem;
}

/* Modal Styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(8, 11, 19, 0.75);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background: var(--admin-surface);
  border-radius: 12px;
  padding: 2rem;
  max-width: 500px;
  width: 90%;
  border: 1px solid var(--admin-border);
  box-shadow: 0 16px 48px rgba(15, 23, 42, 0.45);
  color: var(--admin-text);
}

.pagination-container {
  margin-top: 2rem;
  display: flex;
  justify-content: center;
}

@media (max-width: 768px) {
  .filters {
    flex-direction: column;
  }
  
  .filter-actions {
    width: 100%;
    justify-content: space-between;
  }
  
  .filter-select {
    width: 100%;
  }
}
</style> 