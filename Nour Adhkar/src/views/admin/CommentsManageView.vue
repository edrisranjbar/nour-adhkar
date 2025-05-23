<template>
  <div class="comments-manage">
    <div class="page-header">
      <h1 class="page-title">مدیریت نظرات</h1>
      
      <div class="filters">
        <select v-model="statusFilter" class="filter-select" @change="fetchComments">
          <option value="">همه نظرات</option>
          <option value="pending">در انتظار تایید</option>
          <option value="approved">تایید شده</option>
          <option value="rejected">رد شده</option>
        </select>
      </div>
    </div>

    <div class="comments-table-container">
      <div v-if="loading" class="loading-state">
        <span class="spinner"></span>
        در حال بارگذاری...
      </div>

      <div v-else-if="!comments.length" class="empty-state">
        <font-awesome-icon icon="fa-solid fa-comments" class="empty-icon" />
        <p>هیچ نظری یافت نشد.</p>
      </div>

      <table v-else class="comments-table">
        <thead>
          <tr>
            <th>نویسنده</th>
            <th>مطلب</th>
            <th>نظر</th>
            <th>تاریخ</th>
            <th>وضعیت</th>
            <th>عملیات</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="comment in comments" :key="comment.id" @click="selectComment(comment)" :class="{'selected': comment === selectedComment}">
            <td class="author-cell">
              <div class="author-info">
                <div class="author-details">
                  <span class="author-name">{{ comment.user?.name || comment.author_name }}</span>
                  <span v-if="comment.author_email" class="author-email">{{ comment.author_email }}</span>
                </div>
              </div>
            </td>
            <td>
              <RouterLink :to="`/blog/${comment.post.slug}`" class="post-link">
                {{ comment.post.title }}
              </RouterLink>
            </td>
            <td class="comment-content">{{ comment.content }}</td>
            <td>{{ formatDate(comment.created_at) }}</td>
            <td>
              <span :class="['status-badge', comment.status]">
                {{ getStatusText(comment.status) }}
              </span>
            </td>
            <td class="actions-cell">
              <div class="action-buttons">
                <button 
                  v-if="comment.status === 'pending'"
                  @click="updateStatus(comment, 'approved')"
                  class="action-button approve"
                  title="تایید"
                >
                  <font-awesome-icon icon="fa-solid fa-check" />
                </button>
                <button 
                  v-if="comment.status === 'pending'"
                  @click="updateStatus(comment, 'rejected')"
                  class="action-button reject"
                  title="رد"
                >
                  <font-awesome-icon icon="fa-solid fa-times" />
                </button>
                <button 
                  @click="deleteComment(comment)"
                  class="action-button delete"
                  title="حذف"
                >
                  <font-awesome-icon icon="fa-solid fa-trash" />
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <div v-if="totalPages > 1" class="pagination">
        <button 
          :disabled="currentPage === 1"
          @click="changePage(currentPage - 1)"
          class="pagination-button"
        >
          <font-awesome-icon icon="fa-solid fa-chevron-right" />
        </button>
        
        <span class="page-info">
          صفحه {{ currentPage }} از {{ totalPages }}
        </span>
        
        <button 
          :disabled="currentPage === totalPages"
          @click="changePage(currentPage + 1)"
          class="pagination-button"
        >
          <font-awesome-icon icon="fa-solid fa-chevron-left" />
        </button>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="modal-overlay">
      <div class="modal-content">
        <h3 class="modal-title">حذف نظر</h3>
        <p class="modal-message">آیا از حذف این نظر اطمینان دارید؟</p>
        <div class="modal-actions">
          <button @click="confirmDelete" class="confirm-button">بله، حذف شود</button>
          <button @click="showDeleteModal = false" class="cancel-button">انصراف</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import { library } from '@fortawesome/fontawesome-svg-core';
import { 
  faComments, 
  faCheck, 
  faTimes, 
  faTrash,
  faChevronLeft,
  faChevronRight
} from '@fortawesome/free-solid-svg-icons';
import { mapGetters } from 'vuex';

library.add(faComments, faCheck, faTimes, faTrash, faChevronLeft, faChevronRight);

export default {
  name: 'CommentsManageView',
  data() {
    return {
      comments: [],
      loading: true,
      currentPage: 1,
      totalPages: 1,
      statusFilter: '',
      showDeleteModal: false,
      commentToDelete: null,
      perPage: 10,
      searchQuery: '',
      sortBy: 'created_at',
      sortOrder: 'desc',
      selectedComment: null
    };
  },
  computed: {
    ...mapGetters(['isAuthenticated', 'isAdmin'])
  },
  methods: {
    async fetchComments() {
      if (!this.isAuthenticated || !this.isAdmin) {
        this.$router.push('/login');
        return;
      }

      try {
        this.loading = true;
        const response = await axios.get('/admin/comments', {
          params: {
            page: this.currentPage,
            per_page: this.perPage,
            status: this.statusFilter,
            search: this.searchQuery,
            sort_by: this.sortBy,
            sort_order: this.sortOrder
          }
        });

        if (response.data.success) {
          this.comments = response.data.comments.data;
          this.totalPages = response.data.comments.last_page;
        }
      } catch (error) {
        console.error('Error fetching comments:', error);
        if (error.response?.status === 401) {
          this.$toast.error('لطفا مجددا وارد شوید');
          this.$store.dispatch('logoutUser');
          this.$router.push('/login');
        } else {
          this.$toast.error(error.response?.data?.message || 'خطا در دریافت نظرات');
        }
      } finally {
        this.loading = false;
      }
    },
    
    async updateStatus(comment, status) {
      if (!this.isAuthenticated || !this.isAdmin) {
        this.$router.push('/login');
        return;
      }

      try {
        const endpoint = status === 'approved' ? 'approve' : 'reject';
        const response = await axios.put(`/admin/comments/${comment.id}/${endpoint}`);
        
        if (response.data.success) {
          this.$toast.success('وضعیت نظر با موفقیت بروزرسانی شد');
          this.fetchComments();
        }
      } catch (error) {
        console.error('Error updating comment status:', error);
        if (error.response?.status === 401) {
          this.$toast.error('لطفا مجددا وارد شوید');
          this.$store.dispatch('logoutUser');
          this.$router.push('/login');
        } else {
          this.$toast.error(error.response?.data?.message || 'خطا در بروزرسانی وضعیت نظر');
        }
      }
    },
    
    deleteComment(comment) {
      this.commentToDelete = comment;
      this.showDeleteModal = true;
    },
    
    async confirmDelete() {
      if (!this.isAuthenticated || !this.isAdmin) {
        this.$router.push('/login');
        return;
      }

      if (!this.commentToDelete) return;
      
      try {
        const response = await axios.delete(`/admin/comments/${this.commentToDelete.id}`);
        
        if (response.data.success) {
          this.$toast.success('نظر با موفقیت حذف شد');
          this.fetchComments();
        }
      } catch (error) {
        console.error('Error deleting comment:', error);
        if (error.response?.status === 401) {
          this.$toast.error('لطفا مجددا وارد شوید');
          this.$store.dispatch('logoutUser');
          this.$router.push('/login');
        } else {
          this.$toast.error(error.response?.data?.message || 'خطا در حذف نظر');
        }
      } finally {
        this.showDeleteModal = false;
        this.commentToDelete = null;
      }
    },
    
    changePage(page) {
      this.currentPage = page;
      this.fetchComments();
    },
    
    formatDate(dateString) {
      const date = new Date(dateString);
      return new Intl.DateTimeFormat('fa-IR', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      }).format(date);
    },
    
    getStatusText(status) {
      const statusMap = {
        pending: 'در انتظار تایید',
        approved: 'تایید شده',
        rejected: 'رد شده'
      };
      return statusMap[status] || status;
    },
    
    selectComment(comment) {
      this.selectedComment = comment;
    }
  },
  mounted() {
    if (!this.isAuthenticated || !this.isAdmin) {
      this.$router.push('/login');
      return;
    }
    this.fetchComments();
  }
};
</script>

<style scoped>
.comments-manage {
  padding: 1.5rem;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.page-title {
  font-size: 1.5rem;
  color: #333;
  margin: 0;
}

.filters {
  display: flex;
  gap: 1rem;
}

.filter-select {
  padding: 0.5rem 1rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  background-color: white;
  color: #333;
  font-size: 0.95rem;
}

.comments-table-container {
  background-color: white;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  overflow: hidden;
}

.loading-state {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  color: #666;
  gap: 0.5rem;
}

.spinner {
  width: 1.5rem;
  height: 1.5rem;
  border: 2px solid #f3f3f3;
  border-top: 2px solid #A79277;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 3rem;
  color: #666;
}

.empty-icon {
  font-size: 3rem;
  color: #ddd;
  margin-bottom: 1rem;
}

.comments-table {
  width: 100%;
  border-collapse: collapse;
}

.comments-table th,
.comments-table td {
  padding: 1.25rem 1rem;
  text-align: right;
  border-bottom: 1px solid #eee;
}

.comments-table th {
  background-color: #f8f9fa;
  font-weight: 600;
  color: #555;
}

.comments-table tr:hover {
  background-color: #f5f5f5;
}

.author-cell {
  min-width: 200px;
}

.author-info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.author-avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  object-fit: cover;
}

.author-details {
  display: flex;
  flex-direction: column;
}

.author-name {
  font-weight: 500;
  color: #333;
}

.author-email {
  font-size: 0.875rem;
  color: #666;
}

.post-link {
  color: #A79277;
  text-decoration: none;
}

.post-link:hover {
  text-decoration: underline;
}

.comment-content {
  max-width: 300px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.status-badge {
  padding: 0.25rem 0.6rem;
  border-radius: 12px;
  font-size: 0.8rem;
  font-weight: 500;
  white-space: nowrap;
  min-width: 90px;
  display: inline-block;
}

.status-badge.pending {
  background-color: #ffeeba;
  color: #856404;
  border: 1px solid #ffe08a;
}

.status-badge.approved {
  background-color: #d4edda;
  color: #155724;
  border: 1px solid #a3e6c1;
}

.status-badge.rejected {
  background-color: #f8d7da;
  color: #721c24;
  border: 1px solid #f5b7b1;
}

.actions-cell {
  white-space: nowrap;
}

.action-buttons {
  display: flex;
  gap: 1rem;
}

.action-button {
  background: none;
  border: none;
  padding: 0.4rem;
  cursor: pointer;
  font-size: 0.9rem;
  transition: color 0.2s;
  color: #666;
}

.action-button.approve:hover {
  color: #28a745;
}

.action-button.reject:hover {
  color: #dc3545;
}

.action-button.delete:hover {
  color: #dc3545;
}

.pagination {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1.5rem;
  gap: 2rem;
}

.pagination-button {
  background: none;
  border: 1.5px solid #ddd;
  padding: 0.7rem 1.2rem;
  border-radius: 6px;
  cursor: pointer;
  color: #666;
  font-size: 1.1rem;
  transition: all 0.2s;
}

.pagination-button:hover:not(:disabled) {
  background-color: #f8f9fa;
  border-color: #A79277;
  color: #A79277;
}

.pagination-button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.page-info {
  color: #666;
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.65);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background-color: white;
  padding: 2.5rem 2rem;
  border-radius: 10px;
  max-width: 420px;
  width: 95%;
  box-shadow: 0 8px 32px rgba(0,0,0,0.18);
}

.modal-title {
  font-size: 1.25rem;
  color: #333;
  margin: 0 0 1rem;
}

.modal-message {
  color: #666;
  margin-bottom: 1.5rem;
}

.modal-actions {
  display: flex;
  gap: 1rem;
}

.confirm-button,
.cancel-button {
  padding: 0.75rem 1.5rem;
  border-radius: 4px;
  font-size: 1rem;
  cursor: pointer;
  transition: background-color 0.2s;
}

.confirm-button {
  background-color: #dc3545;
  color: white;
  border: none;
}

.confirm-button:hover {
  background-color: #c82333;
}

.cancel-button {
  background-color: #f8f9fa;
  color: #333;
  border: 1px solid #ddd;
}

.cancel-button:hover {
  background-color: #e9ecef;
}

/* Dark mode styles */
body.dark-mode .page-title {
  color: #eee;
}

body.dark-mode .filter-select {
  background-color: #333;
  border-color: #444;
  color: #eee;
}

body.dark-mode .comments-table-container {
  background-color: #333;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
}

body.dark-mode .comments-table th {
  background-color: #2a2a2a;
  color: #bbb;
}

body.dark-mode .comments-table td {
  border-bottom-color: #444;
}

body.dark-mode .author-name {
  color: #eee;
}

body.dark-mode .author-email {
  color: #bbb;
}

body.dark-mode .post-link {
  color: #A79277;
}

body.dark-mode .comments-table tr:hover {
  background-color: #232323;
}

body.dark-mode .status-badge.pending {
  background-color: #332701;
  color: #ffd700;
  border: 1px solid #ffd700;
}

body.dark-mode .status-badge.approved {
  background-color: #1a3b29;
  color: #4ade80;
  border: 1px solid #4ade80;
}

body.dark-mode .status-badge.rejected {
  background-color: #3b1a1a;
  color: #f87171;
  border: 1px solid #f87171;
}

body.dark-mode .action-button {
  color: #bbb;
}

body.dark-mode .pagination-button {
  border-color: #444;
  color: #bbb;
}

body.dark-mode .pagination-button:hover:not(:disabled) {
  background-color: #2a2a2a;
  border-color: #A79277;
  color: #A79277;
}

body.dark-mode .page-info {
  color: #bbb;
}

body.dark-mode .modal-content {
  background-color: #232323;
  box-shadow: 0 8px 32px rgba(0,0,0,0.28);
}

body.dark-mode .modal-title {
  color: #eee;
}

body.dark-mode .modal-message {
  color: #bbb;
}

body.dark-mode .cancel-button {
  background-color: #2a2a2a;
  color: #eee;
  border-color: #444;
}

body.dark-mode .cancel-button:hover {
  background-color: #333;
}
</style> 