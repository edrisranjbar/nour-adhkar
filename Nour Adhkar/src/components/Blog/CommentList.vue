<template>
  <div class="comments-section">
    <h3 class="section-title">نظرات ({{ comments.length }})</h3>
    
    <div v-if="loading" class="loading-state">
      <span class="spinner"></span>
      در حال بارگذاری نظرات...
    </div>
    
    <div v-else-if="!comments.length" class="empty-state">
      <font-awesome-icon icon="fa-solid fa-comments" class="empty-icon" />
      <p>هنوز نظری ثبت نشده است.</p>
    </div>
    
    <div v-else class="comments-list">
      <div v-for="comment in comments" :key="comment.id" class="comment-item">
        <div class="comment-header">
          <div class="comment-author">
            <img 
              :src="comment.user?.avatar || '/images/default-avatar.png'" 
              :alt="comment.user?.name || comment.author_name"
              class="author-avatar"
            />
            <div class="author-info">
              <span class="author-name">{{ comment.user?.name || comment.author_name }}</span>
              <span class="comment-date">{{ formatDate(comment.created_at) }}</span>
            </div>
          </div>
        </div>
        
        <div class="comment-content">
          {{ comment.content }}
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'CommentList',
  props: {
    postId: {
      type: [Number, String],
      required: true
    }
  },
  data() {
    return {
      comments: [],
      loading: true
    };
  },
  methods: {
    async fetchComments() {
      this.loading = true;
      try {
        const response = await axios.get(`/api/posts/${this.postId}/comments`);
        this.comments = response.data.comments;
      } catch (error) {
        console.error('Error fetching comments:', error);
        this.$toast.error('خطا در دریافت نظرات');
      } finally {
        this.loading = false;
      }
    },
    formatDate(dateString) {
      const date = new Date(dateString);
      return new Intl.DateTimeFormat('fa-IR', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      }).format(date);
    }
  },
  mounted() {
    this.fetchComments();
  }
};
</script>

<style scoped>
.comments-section {
  margin-top: 2rem;
}

.section-title {
  font-size: 1.25rem;
  margin-bottom: 1.5rem;
  color: #333;
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
  padding: 3rem 0;
  color: #666;
}

.empty-icon {
  font-size: 3rem;
  color: #ddd;
  margin-bottom: 1rem;
}

.comments-list {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.comment-item {
  background-color: #f8f9fa;
  border-radius: 8px;
  padding: 1.25rem;
}

.comment-header {
  margin-bottom: 1rem;
}

.comment-author {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.author-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
}

.author-info {
  display: flex;
  flex-direction: column;
}

.author-name {
  font-weight: 500;
  color: #333;
}

.comment-date {
  font-size: 0.875rem;
  color: #666;
}

.comment-content {
  color: #444;
  line-height: 1.6;
}

/* Dark mode styles */
body.dark-mode .section-title {
  color: #eee;
}

body.dark-mode .loading-state {
  color: #bbb;
}

body.dark-mode .empty-state {
  color: #bbb;
}

body.dark-mode .empty-icon {
  color: #444;
}

body.dark-mode .comment-item {
  background-color: #2a2a2a;
}

body.dark-mode .author-name {
  color: #eee;
}

body.dark-mode .comment-date {
  color: #bbb;
}

body.dark-mode .comment-content {
  color: #ddd;
}
</style> 