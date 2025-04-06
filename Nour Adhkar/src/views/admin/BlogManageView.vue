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
          
          <span class="page-info">صفحه {{ pagination.current_page }} از {{ pagination.last_page }}</span>
          
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
    ...mapGetters(['isAuthenticated'])
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
        const response = await axios.get(`${BASE_API_URL}/admin/blog`, {
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
        const response = await axios.delete(`${BASE_API_URL}/blog/${this.postToDelete.id}`, {
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
  color: #333;
}

.new-post-button {
  display: flex;
  align-items: center;
  gap: 8px;
  background-color: #A79277;
  color: white;
  padding: 8px 16px;
  border-radius: 6px;
  text-decoration: none;
  font-weight: 500;
  transition: background-color 0.2s;
}

.new-post-button:hover {
  background-color: #8a7660;
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

.error-message {
  text-align: center;
  color: #e74c3c;
  padding: 30px;
  background-color: #fef2f2;
  border-radius: 8px;
  margin: 20px 0;
}

.retry-button {
  background-color: #A79277;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 4px;
  cursor: pointer;
  margin-top: 10px;
}

.posts-table-container {
  background-color: white;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
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

.status-badge {
  display: inline-block;
  padding: 4px 8px;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 500;
}

.status-badge.published {
  background-color: #d4edda;
  color: #28a745;
}

.status-badge.draft {
  background-color: #f8f9fa;
  color: #6c757d;
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

.view-button:hover {
  color: #4a6fa5;
}

.edit-button:hover {
  color: #A79277;
}

.delete-button:hover {
  color: #dc3545;
}

.no-posts {
  text-align: center;
  padding: 40px;
  color: #777;
}

.create-post-link {
  display: inline-block;
  margin-top: 10px;
  color: #A79277;
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
  gap: 16px;
  padding: 16px;
}

.pagination-button {
  background-color: #A79277;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 4px;
  cursor: pointer;
  transition: background-color 0.2s;
}

.pagination-button:disabled {
  background-color: #d1d1d1;
  cursor: not-allowed;
}

.pagination-button:not(:disabled):hover {
  background-color: #8a7660;
}

.page-info {
  color: #666;
}

/* Modal */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background-color: white;
  border-radius: 8px;
  padding: 24px;
  width: 90%;
  max-width: 400px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
}

.modal-content h3 {
  margin-top: 0;
  color: #333;
}

.modal-content p {
  color: #666;
  margin-bottom: 20px;
}

.modal-buttons {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
}

.delete-confirm-button {
  background-color: #dc3545;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 4px;
  cursor: pointer;
}

.cancel-button {
  background-color: #6c757d;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 4px;
  cursor: pointer;
}

.toast-notification {
  position: fixed;
  bottom: 20px;
  left: 50%;
  transform: translateX(-50%);
  background-color: #333;
  color: white;
  padding: 12px 20px;
  border-radius: 8px;
  z-index: 1100;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
  animation: fadeInOut 3s ease-in-out forwards;
}

@keyframes fadeInOut {
  0% { opacity: 0; transform: translate(-50%, 20px); }
  10% { opacity: 1; transform: translate(-50%, 0); }
  90% { opacity: 1; transform: translate(-50%, 0); }
  100% { opacity: 0; transform: translate(-50%, 20px); }
}

/* Dark mode */
body.dark-mode .posts-table-container {
  background-color: #262626;
}

body.dark-mode .posts-table th {
  background-color: #333;
  color: #ddd;
}

body.dark-mode .posts-table tr {
  border-bottom-color: #444;
}

body.dark-mode .admin-controls h2 {
  color: #eee;
}

body.dark-mode .modal-content {
  background-color: #262626;
}

body.dark-mode .modal-content h3 {
  color: #eee;
}

body.dark-mode .modal-content p {
  color: #ccc;
}
</style> 