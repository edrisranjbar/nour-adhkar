<template>
  <div class="admin-adhkar">
    <div class="header">
      <h1>مدیریت اذکار</h1>
      <button class="add-button" @click="showAddForm = true">
        <font-awesome-icon icon="fa-solid fa-plus" />
        افزودن ذکر جدید
      </button>
    </div>

    <!-- Search and Filters -->
    <div class="filters">
      <div class="search-box">
        <font-awesome-icon icon="fa-solid fa-search" class="search-icon" />
        <input 
          type="text" 
          v-model="searchQuery" 
          placeholder="جستجو در اذکار..." 
          class="search-input"
          @input="handleSearch"
        />
      </div>
      
      <div class="filter-actions">
        <select v-model="categoryFilter" class="filter-select" @change="applyFilters">
          <option value="">همه دسته‌بندی‌ها</option>
          <option v-for="category in categories" :key="category" :value="category">
            {{ category }}
          </option>
        </select>
        
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
        <h2>{{ editingDhikr ? 'ویرایش ذکر' : 'افزودن ذکر جدید' }}</h2>
        <form @submit.prevent="saveDhikr">
          <div class="form-group">
            <label for="title">عنوان</label>
            <input
              id="title"
              v-model="formData.title"
              type="text"
              required
              placeholder="عنوان ذکر را وارد کنید"
            />
          </div>

          <div class="form-group">
            <label for="arabicText">متن عربی</label>
            <textarea
              id="arabicText"
              v-model="formData.arabicText"
              required
              placeholder="متن عربی ذکر را وارد کنید"
              dir="rtl"
            ></textarea>
          </div>

          <div class="form-group">
            <label for="translation">ترجمه</label>
            <textarea
              id="translation"
              v-model="formData.translation"
              required
              placeholder="ترجمه ذکر را وارد کنید"
            ></textarea>
          </div>

          <div class="form-group">
            <label for="category">دسته‌بندی</label>
            <input
              id="category"
              v-model="formData.category"
              type="text"
              placeholder="دسته‌بندی ذکر را وارد کنید"
            />
          </div>

          <div class="form-group">
            <label for="count">تعداد تکرار پیشنهادی</label>
            <input
              id="count"
              v-model.number="formData.recommendedCount"
              type="number"
              min="1"
              placeholder="تعداد تکرار را وارد کنید"
            />
          </div>

          <div class="form-actions">
            <button type="button" class="cancel-button" @click="closeForm">
              انصراف
            </button>
            <button type="submit" class="save-button">
              {{ editingDhikr ? 'ذخیره تغییرات' : 'افزودن ذکر' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Adhkar List -->
    <div v-if="loading" class="loading-state">
      <font-awesome-icon icon="fa-solid fa-spinner" spin />
      <span>در حال بارگذاری...</span>
    </div>

    <div v-else-if="!adhkar.length" class="empty-state">
      <font-awesome-icon icon="fa-solid fa-book" class="empty-icon" />
      <p>هیچ ذکری یافت نشد</p>
      <p class="subtitle">برای افزودن ذکر جدید روی دکمه بالا کلیک کنید</p>
    </div>

    <div v-else class="adhkar-list">
      <div v-for="dhikr in filteredAdhkar" :key="dhikr.id" class="adhkar-card">
        <div class="adhkar-header">
          <h3>{{ dhikr.title }}</h3>
          <div class="card-actions">
            <button class="edit-button" @click="editDhikr(dhikr)">
              <font-awesome-icon icon="fa-solid fa-edit" />
            </button>
            <button class="delete-button" @click="confirmDelete(dhikr)">
              <font-awesome-icon icon="fa-solid fa-trash" />
            </button>
          </div>
        </div>
        
        <div class="adhkar-content">
          <p class="arabic-text">{{ dhikr.arabicText }}</p>
          <p class="translation">{{ dhikr.translation }}</p>
          <div class="adhkar-meta">
            <span class="category" v-if="dhikr.category">{{ dhikr.category }}</span>
            <span class="count" v-if="dhikr.recommendedCount">
              تعداد تکرار: {{ dhikr.recommendedCount }}
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteDialog" class="modal-overlay" @click.self="closeDeleteDialog">
      <div class="modal-container">
        <div class="modal-header">
          <font-awesome-icon icon="fa-solid fa-trash" class="delete-icon" />
          <h2>حذف ذکر</h2>
        </div>
        <p>آیا از حذف این ذکر اطمینان دارید؟</p>
        <div class="modal-actions">
          <button @click="deleteDhikr" class="btn-danger">
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
  name: 'AdminAdhkarView',
  components: {
    NotificationToast
  },
  data() {
    return {
      loading: true,
      adhkar: [],
      searchQuery: '',
      categoryFilter: '',
      sortBy: 'created_at_desc',
      showAddForm: false,
      showDeleteDialog: false,
      editingDhikr: null,
      dhikrToDelete: null,
      formData: {
        title: '',
        arabicText: '',
        translation: '',
        category: '',
        recommendedCount: null
      },
      notification: {
        type: '',
        message: ''
      }
    }
  },
  computed: {
    categories() {
      return [...new Set(this.adhkar.map(dhikr => dhikr.category).filter(Boolean))];
    },
    filteredAdhkar() {
      let filtered = [...this.adhkar];
      
      // Apply search filter
      if (this.searchQuery) {
        const query = this.searchQuery.toLowerCase();
        filtered = filtered.filter(dhikr => 
          dhikr.title.toLowerCase().includes(query) ||
          dhikr.arabicText.toLowerCase().includes(query) ||
          dhikr.translation.toLowerCase().includes(query)
        );
      }
      
      // Apply category filter
      if (this.categoryFilter) {
        filtered = filtered.filter(dhikr => dhikr.category === this.categoryFilter);
      }
      
      // Apply sorting
      const [field, order] = this.sortBy.split('_');
      filtered.sort((a, b) => {
        const aValue = a[field];
        const bValue = b[field];
        return order === 'asc' ? 
          (aValue > bValue ? 1 : -1) : 
          (aValue < bValue ? 1 : -1);
      });
      
      return filtered;
    }
  },
  methods: {
    async loadAdhkar() {
      this.loading = true;
      try {
        const response = await axios.get(`${BASE_API_URL}/admin/adhkar`, {
          headers: {
            Authorization: `Bearer ${this.$store.state.token}`
          }
        });
        this.adhkar = response.data.adhkar || [];
      } catch (error) {
        console.error('Error loading adhkar:', error);
        this.showNotification('خطا در بارگذاری اذکار', 'error');
      } finally {
        this.loading = false;
      }
    },
    handleSearch() {
      // Debounce search if needed
      this.applyFilters();
    },
    applyFilters() {
      // Filters are applied through computed property
    },
    resetForm() {
      this.formData = {
        title: '',
        arabicText: '',
        translation: '',
        category: '',
        recommendedCount: null
      };
      this.editingDhikr = null;
    },
    closeForm() {
      this.showAddForm = false;
      this.resetForm();
    },
    editDhikr(dhikr) {
      this.editingDhikr = dhikr;
      this.formData = { ...dhikr };
      this.showAddForm = true;
    },
    async saveDhikr() {
      try {
        if (this.editingDhikr) {
          await axios.put(
            `${BASE_API_URL}/admin/adhkar/${this.editingDhikr.id}`,
            this.formData,
            {
              headers: {
                Authorization: `Bearer ${this.$store.state.token}`
              }
            }
          );
          this.showNotification('ذکر با موفقیت ویرایش شد', 'success');
        } else {
          await axios.post(
            `${BASE_API_URL}/admin/adhkar`,
            this.formData,
            {
              headers: {
                Authorization: `Bearer ${this.$store.state.token}`
              }
            }
          );
          this.showNotification('ذکر جدید با موفقیت اضافه شد', 'success');
        }
        this.closeForm();
        await this.loadAdhkar();
      } catch (error) {
        console.error('Error saving dhikr:', error);
        this.showNotification('خطا در ذخیره ذکر', 'error');
      }
    },
    confirmDelete(dhikr) {
      this.dhikrToDelete = dhikr;
      this.showDeleteDialog = true;
    },
    closeDeleteDialog() {
      this.showDeleteDialog = false;
      this.dhikrToDelete = null;
    },
    async deleteDhikr() {
      if (!this.dhikrToDelete) return;
      
      try {
        await axios.delete(
          `${BASE_API_URL}/admin/adhkar/${this.dhikrToDelete.id}`,
          {
            headers: {
              Authorization: `Bearer ${this.$store.state.token}`
            }
          }
        );
        this.showNotification('ذکر با موفقیت حذف شد', 'success');
        this.closeDeleteDialog();
        await this.loadAdhkar();
      } catch (error) {
        console.error('Error deleting dhikr:', error);
        this.showNotification('خطا در حذف ذکر', 'error');
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
  async created() {
    await this.loadAdhkar();
  }
}
</script>

<style scoped>
.admin-adhkar {
  padding: 1.5rem;
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
  background-color: #A79277;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  transition: background-color 0.2s ease;
  font-family: inherit;
}

.add-button:hover {
  background-color: #8a7660;
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
  color: #666;
}

.search-input {
  width: 100%;
  padding: 0.75rem 2.5rem 0.75rem 1rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 1rem;
  font-family: inherit;
}

.filter-actions {
  display: flex;
  gap: 1rem;
}

.filter-select {
  padding: 0.75rem 1rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 1rem;
  min-width: 150px;
  font-family: inherit;
}

.form-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.form-container {
  background: white;
  border-radius: 8px;
  padding: 2rem;
  width: 90%;
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  color: #333;
  font-weight: 500;
}

.form-group input,
.form-group textarea {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 1rem;
  font-family: inherit;
}

.form-group textarea {
  min-height: 100px;
  resize: vertical;
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
  border-radius: 4px;
  cursor: pointer;
  font-size: 1rem;
  transition: background-color 0.2s ease;
  font-family: inherit;
}

.cancel-button {
  background-color: #f8f9fa;
  color: #666;
}

.save-button {
  background-color: #A79277;
  color: white;
}

.cancel-button:hover {
  background-color: #e9ecef;
}

.save-button:hover {
  background-color: #8a7660;
}

.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
  padding: 3rem;
  color: #666;
}

.empty-state {
  text-align: center;
  padding: 3rem 1rem;
  background: white;
  border-radius: 8px;
  margin: 2rem 0;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.empty-icon {
  font-size: 3rem;
  color: #A79277;
  margin-bottom: 1rem;
}

.subtitle {
  color: #666;
  margin-top: 0.5rem;
}

.adhkar-list {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 1rem;
}

.adhkar-card {
  background: white;
  border-radius: 8px;
  padding: 1.5rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.adhkar-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.adhkar-header h3 {
  margin: 0;
  font-size: 1.1rem;
  color: #333;
}

.card-actions {
  display: flex;
  gap: 0.5rem;
}

.edit-button,
.delete-button {
  background: none;
  border: none;
  padding: 0.5rem;
  border-radius: 50%;
  cursor: pointer;
  transition: background-color 0.2s ease;
  font-family: inherit;
}

.edit-button {
  color: #2196f3;
}

.delete-button {
  color: #dc3545;
}

.edit-button:hover {
  background-color: rgba(33, 150, 243, 0.1);
}

.delete-button:hover {
  background-color: rgba(220, 53, 69, 0.1);
}

.adhkar-content {
  margin-top: 1rem;
}

.arabic-text {
  font-size: 1.5rem;
  text-align: center;
  margin: 1rem 0;
  color: #333;
}

.translation {
  color: #666;
  margin-bottom: 1rem;
}

.adhkar-meta {
  display: flex;
  gap: 1rem;
  color: #666;
  font-size: 0.9rem;
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-container {
  background: white;
  border-radius: 8px;
  padding: 2rem;
  width: 90%;
  max-width: 500px;
}

.modal-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1rem;
}

.modal-header h2 {
  margin: 0;
  color: #333;
}

.delete-icon {
  color: #dc3545;
  font-size: 1.5rem;
}

.modal-actions {
  display: flex;
  gap: 1rem;
  margin-top: 2rem;
}

.btn-danger {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  background: #dc3545;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  transition: background-color 0.2s ease;
  font-family: inherit;
}

.btn-danger:hover {
  background-color: #c82333;
}

.btn-secondary {
  padding: 0.75rem 1.5rem;
  background: #f8f9fa;
  color: #666;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  transition: background-color 0.2s ease;
  font-family: inherit;
}

.btn-secondary:hover {
  background-color: #e9ecef;
}

/* Dark mode styles */
body.dark-mode {
  .form-container,
  .modal-container,
  .adhkar-card,
  .empty-state {
    background-color: #333;
  }

  .form-group label,
  .adhkar-header h3,
  .modal-header h2 {
    color: #fff;
  }

  .arabic-text {
    color: #fff;
  }

  .translation,
  .adhkar-meta,
  .subtitle {
    color: #aaa;
  }

  .search-input,
  .filter-select,
  .form-group input,
  .form-group textarea {
    background-color: #444;
    border-color: #555;
    color: #fff;
  }

  .cancel-button,
  .btn-secondary {
    background-color: #444;
    color: #fff;
  }

  .cancel-button:hover,
  .btn-secondary:hover {
    background-color: #555;
  }
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .filters {
    flex-direction: column;
  }

  .filter-actions {
    flex-direction: column;
  }

  .filter-select {
    width: 100%;
  }

  .adhkar-list {
    grid-template-columns: 1fr;
  }
}
</style> 