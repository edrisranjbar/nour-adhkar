<template>
  <div class="blog-manage-container">
    <AppHeader title="مدیریت مقالات" description="ایجاد و ویرایش مقالات" />
    
    <main class="container">
      <div class="admin-controls">
        <h2>مدیریت مقالات</h2>
        <RouterLink to="/admin/blog/new" class="new-post-button">
          <font-awesome-icon icon="fa-solid fa-plus" />
          ایجاد مقاله جدید
        </RouterLink>
      </div>
      
      <div v-if="loading" class="loading-container">
        <div class="spinner"></div>
        <p>در حال بارگذاری مقالات...</p>
      </div>
      
      <div v-else-if="error" class="error-message">
        <p>{{ error }}</p>
        <button @click="fetchPosts" class="retry-button">تلاش مجدد</button>
      </div>
      
      <div v-else class="posts-table-container">
        <div v-if="posts.length === 0" class="no-posts">
          <p>هنوز مقاله‌ای ایجاد نشده است.</p>
          <RouterLink to="/admin/blog/new" class="create-post-link">ایجاد اولین مقاله</RouterLink>
        </div>
        
        <table v-else class="posts-table">
          <thead>
            <tr>
              <th>عنوان</th>
              <th>وضعیت</th>
              <th>تاریخ انتشار</th>
              <th>عملیات</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="post in posts" :key="post.id">
              <td>{{ post.title }}</td>
              <td>
                <span :class="['status-badge', post.status === 'published' ? 'published' : 'draft']">
                  {{ post.status === 'published' ? 'منتشر شده' : 'پیش‌نویس' }}
                </span>
              </td>
              <td>{{ post.published_at ? formatDate(post.published_at) : '—' }}</td>
              <td class="actions">
                <RouterLink :to="`/admin/blog/edit/${post.id}`" class="action-button edit-button" title="ویرایش">
                  <font-awesome-icon icon="fa-solid fa-pen" />
                </RouterLink>
                <RouterLink :to="`/blog/${post.slug}`" target="_blank" class="action-button view-button" title="مشاهده">
                  <font-awesome-icon icon="fa-solid fa-eye" />
                </RouterLink>
                <button @click="confirmDelete(post)" class="action-button delete-button" title="حذف">
                  <font-awesome-icon icon="fa-solid fa-trash" />
                </button>
              </td>
            </tr>
          </tbody>
        </table>
        
        <!-- Pagination -->
        <div v-if="pagination.last_page > 1" class="pagination">
          <button 
            :disabled="pagination.current_page === 1" 
            @click="changePage(pagination.current_page - 1)"
            class="pagination-button"
          >
            قبلی
          </button>
          
          <div class="page-numbers">
            <button 
              v-for="page in displayedPages" 
              :key="page" 
              :class="['page-number', { active: pagination.current_page === page }]"
              @click="changePage(page)"
            >
              {{ page }}
            </button>
          </div>
          
          <button 
            :disabled="pagination.current_page === pagination.last_page" 
            @click="changePage(pagination.current_page + 1)"
            class="pagination-button"
          >
            بعدی
          </button>
        </div>
      </div>
    </main>
    
    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="modal-overlay">
      <div class="modal-content">
        <h3>آیا از حذف این مقاله اطمینان دارید؟</h3>
        <p>این عملیات غیرقابل بازگشت است.</p>
        <div class="modal-buttons">
          <button @click="deletePost" class="delete-confirm-button">بله، حذف شود</button>
          <button @click="showDeleteModal = false" class="cancel-button">انصراف</button>
        </div>
      </div>
    </div>
    
    <!-- Toast notification -->
    <div class="toast-notification" v-if="showToast">
      <p>{{ toastMessage }}</p>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import { BASE_API_URL } from '@/config';
import { mapGetters } from 'vuex';

export default {
  components: {
    // Only keep necessary components
  },
  data() {
    return {
      posts: [],
      loading: true,
      error: null,
      pagination: {
        current_page: 1,
        last_page: 1,
        per_page: 10,
        total: 0
      },
      showDeleteModal: false,
      postToDelete: null,
      showToast: false,
      toastMessage: ''
    };
  },
  computed: {
    ...mapGetters(['isAuthenticated']),
    displayedPages() {
      const displayCount = 5;
      let start = Math.max(1, this.pagination.current_page - Math.floor(displayCount / 2));
      let end = Math.min(this.pagination.last_page, start + displayCount - 1);
      
      // Adjust start if end is at max pages
      if (end === this.pagination.last_page) {
        start = Math.max(1, end - displayCount + 1);
      }
      
      const pages = [];
      for (let i = start; i <= end; i++) {
        pages.push(i);
      }
      return pages;
    }
  },
  mounted() {
    // Remove the redirection check since it's handled by the guard
    this.fetchPosts();
  },
  methods: {
    async fetchPosts(page = 1) {
      this.loading = true;
      this.error = null;
      
      try {
        const token = this.$store.state.token;
        const response = await axios.get(`${BASE_API_URL}/admin/posts`, {
          params: { page },
          headers: {
            Authorization: `Bearer ${token}`
          }
        });
        
        if (response.data.success) {
          this.posts = response.data.posts.data;
          this.pagination = {
            current_page: response.data.posts.current_page,
            last_page: response.data.posts.last_page,
            per_page: response.data.posts.per_page,
            total: response.data.posts.total
          };
        } else {
          this.error = 'خطا در دریافت مقالات';
        }
      } catch (error) {
        console.error('Error fetching blog posts:', error);
        this.error = 'خطا در ارتباط با سرور';
      } finally {
        this.loading = false;
      }
    },
    changePage(page) {
      this.fetchPosts(page);
      window.scrollTo(0, 0);
    },
    formatDate(dateString) {
      const date = new Date(dateString);
      return new Intl.DateTimeFormat('fa-IR', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      }).format(date);
    },
    confirmDelete(post) {
      this.postToDelete = post;
      this.showDeleteModal = true;
    },
    async deletePost() {
      if (!this.postToDelete) return;
      
      try {
        const token = this.$store.state.token;
        const response = await axios.delete(`${BASE_API_URL}/admin/posts/${this.postToDelete.id}`, {
          headers: {
            Authorization: `Bearer ${token}`
          }
        });
        
        if (response.data.success) {
          this.posts = this.posts.filter(post => post.id !== this.postToDelete.id);
          this.showToastMessage('مقاله با موفقیت حذف شد');
        } else {
          this.showToastMessage('خطا در حذف مقاله');
        }
      } catch (error) {
        console.error('Error deleting post:', error);
        this.showToastMessage('خطا در ارتباط با سرور');
      } finally {
        this.showDeleteModal = false;
        this.postToDelete = null;
      }
    },
    showToastMessage(message) {
      this.toastMessage = message;
      this.showToast = true;
      setTimeout(() => {
        this.showToast = false;
      }, 3000);
    }
  }
};
</script>

<style scoped>
.blog-manage-container {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  background: var(--admin-bg);
  color: var(--admin-text);
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px 16px;
  flex: 1;
}

.admin-controls {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.admin-controls h2 {
  font-size: 1.5rem;
  color: var(--admin-text);
  margin: 0;
}

.new-post-button {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background-color: var(--admin-accent);
  color: #fff;
  padding: 8px 16px;
  border-radius: 6px;
  text-decoration: none;
  font-weight: 500;
  transition: background-color 0.2s ease, transform 0.2s ease;
}

.new-post-button:hover {
  background-color: rgba(59, 130, 246, 0.85);
  transform: translateY(-1px);
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

.error-message {
  text-align: center;
  color: #fca5a5;
  padding: 30px;
  background-color: rgba(239, 68, 68, 0.1);
  border: 1px solid rgba(239, 68, 68, 0.2);
  border-radius: 8px;
  margin: 20px 0;
}

.retry-button {
  background-color: var(--admin-accent);
  color: #fff;
  border: none;
  padding: 8px 16px;
  border-radius: 4px;
  cursor: pointer;
  margin-top: 10px;
  transition: background-color 0.2s ease;
}

.retry-button:hover {
  background-color: rgba(59, 130, 246, 0.85);
}

.posts-table-container {
  background-color: var(--admin-surface);
  border-radius: 10px;
  overflow: hidden;
  box-shadow: 0 12px 32px rgba(15, 23, 42, 0.35);
  border: 1px solid var(--admin-border);
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
  text-transform: uppercase;
  font-size: 0.75rem;
  letter-spacing: 0.05em;
  color: var(--admin-muted);
}

.posts-table tr:last-child td {
  border-bottom: none;
}

.status-badge {
  display: inline-block;
  padding: 4px 10px;
  border-radius: 999px;
  font-size: 0.85rem;
  font-weight: 500;
}

.status-badge.published {
  background-color: rgba(34, 197, 94, 0.15);
  color: #4ade80;
}

.status-badge.draft {
  background-color: rgba(148, 163, 184, 0.15);
  color: var(--admin-muted);
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

.view-button:hover {
  color: #60a5fa;
}

.edit-button:hover {
  color: var(--admin-accent);
}

.delete-button:hover {
  color: #f87171;
}

.no-posts {
  text-align: center;
  padding: 40px;
  color: var(--admin-muted);
}

.create-post-link {
  display: inline-block;
  margin-top: 10px;
  color: var(--admin-accent);
  font-weight: 500;
  text-decoration: none;
}

.create-post-link:hover {
  text-decoration: underline;
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  margin-top: 20px;
  gap: 0.75rem;
}

.pagination-button {
  padding: 8px 16px;
  background-color: rgba(255, 255, 255, 0.05);
  border: 1px solid var(--admin-border);
  border-radius: 6px;
  cursor: pointer;
  color: var(--admin-text);
  transition: background-color 0.2s ease, border-color 0.2s ease;
}

.pagination-button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.page-numbers {
  display: flex;
  gap: 0.5rem;
}

.page-number {
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 1px solid var(--admin-border);
  background-color: rgba(255, 255, 255, 0.03);
  cursor: pointer;
  border-radius: 6px;
  color: var(--admin-text);
}

.page-number.active {
  background-color: var(--admin-accent);
  color: #fff;
  border-color: transparent;
}

.page-number:hover {
  border-color: var(--admin-accent);
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(8, 11, 19, 0.75);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background-color: var(--admin-surface);
  border-radius: 12px;
  padding: 24px;
  width: 90%;
  max-width: 400px;
  box-shadow: 0 16px 48px rgba(15, 23, 42, 0.45);
  border: 1px solid var(--admin-border);
  color: var(--admin-text);
}

.modal-content h3 {
  margin-top: 0;
  color: var(--admin-text);
}

.modal-content p {
  color: var(--admin-muted);
  margin-bottom: 20px;
}

.modal-buttons {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
}

.delete-confirm-button {
  background-color: #f87171;
  color: #fff;
  border: none;
  padding: 8px 16px;
  border-radius: 6px;
  cursor: pointer;
  transition: background-color 0.2s ease;
}

.delete-confirm-button:hover {
  background-color: #ef4444;
}

.cancel-button {
  background-color: rgba(148, 163, 184, 0.2);
  color: var(--admin-muted);
  border: none;
  padding: 8px 16px;
  border-radius: 6px;
  cursor: pointer;
  transition: background-color 0.2s ease;
}

.cancel-button:hover {
  background-color: rgba(148, 163, 184, 0.3);
}

.toast-notification {
  position: fixed;
  bottom: 20px;
  left: 50%;
  transform: translateX(-50%);
  background-color: rgba(17, 24, 39, 0.95);
  color: #fff;
  padding: 12px 20px;
  border-radius: 8px;
  z-index: 1100;
  box-shadow: 0 12px 24px rgba(15, 23, 42, 0.45);
  animation: fadeInOut 3s ease-in-out forwards;
}

@keyframes fadeInOut {
  0% { opacity: 0; transform: translate(-50%, 20px); }
  10% { opacity: 1; transform: translate(-50%, 0); }
  90% { opacity: 1; transform: translate(-50%, 0); }
  100% { opacity: 0; transform: translate(-50%, 20px); }
}

@media (max-width: 768px) {
  .admin-controls {
    flex-direction: column;
    gap: 1rem;
    align-items: flex-start;
  }

  .pagination {
    flex-direction: column;
    gap: 0.5rem;
  }
}
</style> 