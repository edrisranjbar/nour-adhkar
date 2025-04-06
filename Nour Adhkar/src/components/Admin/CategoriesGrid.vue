<template>
  <div class="posts-table-container">
    <div v-if="loading" class="loading-container">
      <div class="spinner"></div>
      <p>در حال بارگذاری دسته‌بندی‌ها...</p>
    </div>
    
    <div v-else-if="categories.length === 0" class="no-posts">
      <font-awesome-icon icon="folder-open" class="text-4xl mb-4" />
      <p v-if="searchQuery">هیچ دسته‌بندی‌ای با این عبارت یافت نشد.</p>
      <p v-else>هیچ دسته‌بندی‌ای وجود ندارد.</p>
    </div>
    
    <div v-else>
      <table class="posts-table">
        <thead>
          <tr>
            <th>نام</th>
            <th>شناسه</th>
            <th>دسته‌بندی والد</th>
            <th>توضیحات</th>
            <th>عملیات</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="category in categories" :key="category.id">
            <td>{{ category.name }}</td>
            <td class="font-mono">{{ category.slug }}</td>
            <td>{{ getParentCategoryName(category.parent_id) }}</td>
            <td>{{ category.description || '—' }}</td>
            <td class="actions">
              <button
                @click="$emit('edit', category)"
                class="action-button edit-button"
                title="ویرایش"
              >
                <font-awesome-icon icon="edit" />
              </button>
              <button
                @click="$emit('delete', category)"
                class="action-button delete-button"
                title="حذف"
              >
                <font-awesome-icon icon="trash" />
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <AdminPagination
        v-if="pagination"
        :pagination="pagination"
        @page-change="$emit('page-change', $event)"
      />
    </div>
  </div>
</template>

<script>
import AdminPagination from '@/components/Admin/Pagination.vue'

export default {
  name: 'CategoriesGrid',
  components: {
    AdminPagination
  },
  props: {
    categories: {
      type: Array,
      required: true
    },
    loading: {
      type: Boolean,
      default: false
    },
    searchQuery: {
      type: String,
      default: ''
    },
    pagination: {
      type: Object,
      required: true,
      default: () => ({
        current_page: 1,
        last_page: 1,
        total: 0
      })
    }
  },
  emits: ['edit', 'delete', 'page-change'],
  methods: {
    getParentCategoryName(parentId) {
      if (!parentId) return 'بدون والد'
      const parent = this.categories.find(c => c.id === parentId)
      return parent ? parent.name : 'بدون والد'
    }
  }
}
</script>

<style scoped>
.posts-table-container {
  background-color: white;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 300px;
}

.spinner {
  width: 50px;
  height: 50px;
  border: 5px solid rgba(167, 146, 119, 0.3);
  border-radius: 50%;
  border-top-color: #A79277;
  animation: spin 1s infinite ease;
  margin-bottom: 20px;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.no-posts {
  text-align: center;
  padding: 40px;
  color: #777;
}

.posts-table {
  width: 100%;
  border-collapse: collapse;
}

.posts-table th,
.posts-table td {
  padding: 12px 16px;
  text-align: right;
}

.posts-table th {
  background-color: #f8f8f8;
  font-weight: 600;
  color: #555;
}

.posts-table tr {
  border-bottom: 1px solid #eee;
}

.posts-table tr:last-child {
  border-bottom: none;
}

.actions {
  display: flex;
  gap: 12px;
}

.action-button {
  background: none;
  border: none;
  padding: 0.4rem 0.5rem;
  cursor: pointer;
  font-size: 0.9rem;
  transition: color 0.2s;
  color: #666;
}

.edit-button:hover {
  color: #A79277;
}

.delete-button:hover {
  color: #dc3545;
}

/* Dark mode */
:deep(body.dark-mode) .posts-table-container {
  background-color: #262626;
}

:deep(body.dark-mode) .posts-table th {
  background-color: #333;
  color: #ddd;
}

:deep(body.dark-mode) .posts-table tr {
  border-bottom-color: #444;
}

:deep(body.dark-mode) td {
  color: #eee;
}

:deep(body.dark-mode) .no-posts {
  color: #ddd;
}
</style> 