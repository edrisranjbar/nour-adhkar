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
  min-height: 100vh;
  background: var(--admin-bg);
  color: var(--admin-text);
  padding: 1.5rem;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.page-title {
  margin: 0;
  font-size: 1.5rem;
  color: var(--admin-text);
}

.filters {
  display: flex;
  align-items: center;
}

.filter-select {
  background: rgba(255, 255, 255, 0.05);
  color: var(--admin-text);
  border: 1px solid var(--admin-border);
  border-radius: 8px;
  padding: 0.5rem 0.75rem;
  font-family: inherit;
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.filter-select:focus {
  outline: none;
  border-color: var(--admin-accent);
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.25);
}

.comments-table-container {
  background: var(--admin-surface);
  border-radius: 12px;
  padding: 1.25rem;
  box-shadow: 0 16px 48px rgba(15, 23, 42, 0.45);
  border: 1px solid var(--admin-border);
  position: relative;
}

.loading-state {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 0.75rem;
  padding: 3rem 0;
  color: var(--admin-muted);
}

.spinner {
  width: 28px;
  height: 28px;
  border: 3px solid rgba(59, 130, 246, 0.2);
  border-radius: 50%;
  border-top-color: var(--admin-accent);
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.empty-state {
  text-align: center;
  padding: 3rem 1rem;
  color: var(--admin-muted);
  background: rgba(255, 255, 255, 0.04);
  border-radius: 10px;
  border: 1px dashed var(--admin-border);
}

.empty-icon {
  font-size: 3rem;
  color: var(--admin-accent);
  margin-bottom: 1rem;
}

.comments-table {
  width: 100%;
  border-collapse: collapse;
}

.comments-table th,
.comments-table td {
  padding: 0.85rem;
  text-align: right;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  vertical-align: top;
  color: var(--admin-text);
}

.comments-table th {
  background: rgba(255, 255, 255, 0.05);
  font-weight: 600;
  text-transform: uppercase;
  font-size: 0.75rem;
  letter-spacing: 0.05em;
  color: var(--admin-muted);
}

.comments-table tr.selected {
  background: rgba(59, 130, 246, 0.08);
}

.comments-table tr:hover {
  background: rgba(59, 130, 246, 0.05);
}

.author-name {
  color: var(--admin-text);
  font-weight: 600;
}

.author-email {
  color: var(--admin-muted);
  font-size: 0.85rem;
}

.post-link {
  color: var(--admin-accent);
  text-decoration: none;
}

.post-link:hover {
  text-decoration: underline;
}

.comment-content {
  max-width: 360px;
  white-space: pre-wrap;
  line-height: 1.6;
}

.status-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0.25rem 0.75rem;
  border-radius: 999px;
  font-size: 0.8rem;
  font-weight: 600;
  border: 1px solid transparent;
}

.status-badge.pending {
  background: rgba(250, 204, 21, 0.12);
  color: #facc15;
  border-color: rgba(250, 204, 21, 0.35);
}

.status-badge.approved {
  background: rgba(74, 222, 128, 0.12);
  color: #4ade80;
  border-color: rgba(74, 222, 128, 0.35);
}

.status-badge.rejected {
  background: rgba(248, 113, 113, 0.12);
  color: #f87171;
  border-color: rgba(248, 113, 113, 0.35);
}

.actions-cell {
  width: 140px;
}

.action-buttons {
  display: flex;
  gap: 0.5rem;
  justify-content: flex-end;
}

.action-button {
  background: none;
  border: none;
  color: var(--admin-muted);
  padding: 0.4rem;
  border-radius: 6px;
  cursor: pointer;
  transition: color 0.2s ease, background-color 0.2s ease, transform 0.2s ease;
  font-size: 0.95rem;
  font-family: inherit;
}

.action-button:hover {
  color: var(--admin-accent);
  background: rgba(59, 130, 246, 0.12);
  transform: translateY(-1px);
}

.action-button.approve:hover {
  color: #4ade80;
  background: rgba(74, 222, 128, 0.12);
}

.action-button.reject:hover {
  color: #facc15;
  background: rgba(250, 204, 21, 0.15);
}

.action-button.delete:hover {
  color: #f87171;
  background: rgba(248, 113, 113, 0.12);
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 0.75rem;
  margin-top: 1.5rem;
}

.pagination-button {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 38px;
  height: 38px;
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid var(--admin-border);
  color: var(--admin-text);
  cursor: pointer;
  transition: all 0.2s ease;
}

.pagination-button:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.pagination-button:hover:not(:disabled) {
  background: var(--admin-accent);
  border-color: transparent;
  color: #fff;
}

.page-info {
  color: var(--admin-muted);
}

.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(8, 11, 19, 0.75);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1100;
}

.modal-content {
  background: var(--admin-surface);
  padding: 2rem;
  border-radius: 12px;
  width: 95%;
  max-width: 420px;
  box-shadow: 0 24px 60px rgba(15, 23, 42, 0.5);
  border: 1px solid var(--admin-border);
  text-align: center;
}

.modal-title {
  font-size: 1.3rem;
  color: var(--admin-text);
  margin: 0 0 1rem;
}

.modal-message {
  color: var(--admin-muted);
  margin-bottom: 1.5rem;
}

.modal-actions {
  display: flex;
  justify-content: center;
  gap: 1rem;
}

.confirm-button,
.cancel-button {
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  font-size: 1rem;
  cursor: pointer;
  border: none;
  transition: transform 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
  font-family: inherit;
}

.confirm-button {
  background: #f87171;
  color: #fff;
}

.confirm-button:hover {
  background: #ef4444;
  transform: translateY(-1px);
  box-shadow: 0 10px 20px rgba(239, 68, 68, 0.25);
}

.cancel-button {
  background: rgba(148, 163, 184, 0.2);
  color: var(--admin-muted);
}

.cancel-button:hover {
  background: rgba(148, 163, 184, 0.3);
  transform: translateY(-1px);
}

@media (max-width: 768px) {
  .page-header {
    flex-direction: column;
    align-items: flex-start;
  }

  .filters {
    width: 100%;
  }

  .comments-table td,
  .comments-table th {
    font-size: 0.85rem;
  }
}
</style> 