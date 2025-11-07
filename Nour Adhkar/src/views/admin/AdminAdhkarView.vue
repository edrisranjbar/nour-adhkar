<template>
  <div class="admin-adhkar">
    <AdminHeader 
      title="مدیریت اذکار"
      add-button-text="افزودن ذکر جدید"
      @add="showAddForm = true"
    />

    <!-- Loading and Empty States -->
    <div v-if="loading" class="loading-state">
      <font-awesome-icon icon="fa-solid fa-spinner" spin />
      <span>در حال بارگذاری...</span>
    </div>

    <div v-else-if="!adhkar.length" class="empty-state">
      <font-awesome-icon icon="fa-solid fa-book" class="empty-icon" />
      <p>هیچ ذکری یافت نشد</p>
      <p class="subtitle">برای افزودن ذکر جدید روی دکمه بالا کلیک کنید</p>
    </div>

    <DataTable
      v-else
      :columns="tableColumns"
      :items="paginatedAdhkar"
    >
    <div class="actions">
      <ActionButton
        icon="edit"
        variant="primary"
        size="sm"
        @click="editDhikr(item)"
      />
      <ActionButton
        icon="trash"
        variant="danger"
        size="sm"
        @click="confirmDelete(item)"
      />
    </div>
      <template #pagination>
        <Pagination
          :pagination="pagination"
          @page-change="changePage"
        />
      </template>
    </DataTable>

    <!-- Add/Edit Form -->
    <FormModal
      :show="showAddForm"
      :title="editingDhikr ? 'ویرایش ذکر' : 'افزودن ذکر جدید'"
      :submit-text="editingDhikr ? 'ذخیره تغییرات' : 'افزودن ذکر'"
      @close="closeForm"
      @submit="saveDhikr"
    >
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
        <label for="arabic_text">متن عربی</label>
        <textarea
          id="arabic_text"
          v-model="formData.arabic_text"
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
        <label for="collection">مجموعه</label>
        <select
          id="collection"
          v-model="formData.collection_id"
          class="form-select"
          required
        >
          <option value="">انتخاب مجموعه</option>
          <option v-for="collection in collections" :key="collection.id" :value="collection.id">
            {{ collection.name }}
          </option>
        </select>
      </div>

      <div class="form-group">
        <label for="count">تعداد تکرار پیشنهادی</label>
        <input
          id="count"
          v-model.number="formData.count"
          type="number"
          min="1"
          placeholder="تعداد تکرار را وارد کنید"
        />
      </div>
    </FormModal>

    <!-- Delete Confirmation Modal -->
    <FormModal
      :show="showDeleteDialog"
      title="حذف ذکر"
      submit-text="حذف"
      @close="closeDeleteDialog"
      @submit="deleteDhikr"
    >
      <p>آیا از حذف این ذکر اطمینان دارید؟</p>
    </FormModal>

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
import AdminHeader from '@/components/Admin/AdminHeader.vue';
import DataTable from '@/components/Admin/DataTable.vue';
import FormModal from '@/components/Admin/FormModal.vue';
import NotificationToast from '@/components/Admin/NotificationToast.vue';
import Pagination from '@/components/Admin/Pagination.vue';
import ActionButton from '@/components/Admin/ActionButton.vue';


export default {
  name: 'AdminAdhkarView',
  components: {
    AdminHeader,
    DataTable,
    FormModal,
    NotificationToast,
    Pagination,
    ActionButton
  },
  data() {
    return {
      loading: true,
      adhkar: [],
      collections: [],
      showAddForm: false,
      showDeleteDialog: false,
      editingDhikr: null,
      dhikrToDelete: null,
      formData: {
        title: '',
        arabic_text: '',
        translation: '',
        collection_id: '',
        count: null
      },
      notification: {
        type: '',
        message: ''
      },
      pagination: {
        current_page: 1,
        last_page: 1,
        total: 0,
        per_page: 10
      },
      tableColumns: [
        { key: 'title', label: 'عنوان' },
        { key: 'arabic_text', label: 'متن عربی', formatter: text => this.truncateText(text, 50) },
        { 
          key: 'collection_id', 
          label: 'مجموعه',
          formatter: (collection_id) => {
            const collection = this.collections.find(c => c.id === collection_id);
            return collection ? collection.name : '';
          }
        },
        { key: 'count', label: 'تعداد تکرار' }
      ]
    }
  },
  computed: {
    paginatedAdhkar() {
      const startIndex = (this.pagination.current_page - 1) * this.pagination.per_page;
      return this.adhkar.slice(startIndex, startIndex + this.pagination.per_page);
    }
  },
  methods: {
    async loadAdhkar() {
      this.loading = true;
      try {
        const [adhkarResponse, collectionsResponse] = await Promise.all([
          axios.get(`${BASE_API_URL}/admin/adhkar`, {
            headers: {
              Authorization: `Bearer ${this.$store.state.token}`
            }
          }),
          axios.get(`${BASE_API_URL}/admin/collections`, {
            headers: {
              Authorization: `Bearer ${this.$store.state.token}`
            }
          })
        ]);
        
        this.adhkar = adhkarResponse.data.adhkar || [];
        this.collections = collectionsResponse.data.collections || [];
      } catch (error) {
        console.error('Error loading data:', error);
        this.showNotification('خطا در بارگذاری اطلاعات', 'error');
      } finally {
        this.loading = false;
      }
    },
    updatePagination() {
      this.pagination.total = this.adhkar.length;
      this.pagination.last_page = Math.ceil(this.adhkar.length / this.pagination.per_page);
    },
    changePage(page) {
      this.pagination.current_page = page;
      window.scrollTo(0, 0);
    },
    truncateText(text, maxLength) {
      if (!text) return '';
      return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
    },
    resetForm() {
      this.formData = {
        title: '',
        arabic_text: '',
        translation: '',
        collection_id: '',
        count: null
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
    this.updatePagination();
  }
}
</script>

<style scoped>
.admin-adhkar {
  padding: 1.5rem;
  background: var(--admin-bg);
  color: var(--admin-text);
}

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
.form-group textarea,
.form-group select {
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

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
  outline: none;
  border-color: var(--admin-accent);
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.25);
}

.form-group textarea {
  min-height: 100px;
  resize: vertical;
}

.actions {
  display: flex;
  gap: 0.5rem;
  justify-content: flex-end;
}
</style>