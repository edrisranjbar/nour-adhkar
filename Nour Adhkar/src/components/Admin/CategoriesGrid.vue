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
            <th>عملیات</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="category in categories" :key="category.id">
            <td>{{ category.name }}</td>
            <td class="font-mono">{{ category.slug }}</td>
            <td>{{ getParentCategoryName(category.parent_id) }}</td>
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
  background-color: var(--admin-surface);
  border-radius: 10px;
  overflow: hidden;
  box-shadow: 0 12px 32px rgba(15, 23, 42, 0.35);
  border: 1px solid var(--admin-border);
}

.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 300px;
  color: var(--admin-muted);
}

.spinner {
  width: 50px;
  height: 50px;
  border: 5px solid rgba(59, 130, 246, 0.2);
  border-radius: 50%;
  border-top-color: var(--admin-accent);
  animation: spin 1s infinite ease;
  margin-bottom: 20px;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.no-posts {
  text-align: center;
  padding: 40px;
  color: var(--admin-muted);
}

.posts-table {
  width: 100%;
  border-collapse: collapse;
}

.posts-table th,
.posts-table td {
  padding: 12px 16px;
  text-align: right;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  color: var(--admin-text);
}

.posts-table th {
  background-color: rgba(255, 255, 255, 0.05);
  font-weight: 600;
  color: var(--admin-muted);
  text-transform: uppercase;
  font-size: 0.8rem;
  letter-spacing: 0.05em;
}

.posts-table tr:last-child td {
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
  transition: color 0.2s ease;
  color: var(--admin-muted);
}

.action-button:hover {
  color: var(--admin-accent);
}

.action-button.delete-button:hover {
  color: #f87171;
}
</style> 