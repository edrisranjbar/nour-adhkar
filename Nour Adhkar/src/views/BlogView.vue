<template>
  <div class="blog-container">
    <AppHeader title="مقالات و نوشته‌ها" description="مطالب آموزشی درباره اذکار و فضیلت آن‌ها" />
    
    <main class="container">
      <div v-if="loading" class="loading-container">
        <div class="spinner"></div>
        <p>در حال بارگذاری مقالات...</p>
      </div>
      
      <div v-else-if="error" class="error-message">
        <p>{{ error }}</p>
        <button @click="fetchPosts" class="retry-button">تلاش مجدد</button>
      </div>
      
      <div v-else class="blog-posts">
        <div v-if="posts.length === 0" class="no-posts">
          <p>هنوز مقاله‌ای منتشر نشده است.</p>
        </div>
        
        <div v-else class="post-grid">
          <div v-for="post in posts" :key="post.id" class="post-card">
            <div class="post-image">
              <img v-if="post.image" :src="post.image" :alt="post.title">
              <div v-else class="placeholder-image"></div>
            </div>
            <div class="post-content">
              <h2 class="post-title">{{ post.title }}</h2>
              <p class="post-excerpt">{{ post.excerpt || truncateContent(post.content) }}</p>
              <div class="post-meta">
                <span class="post-date">{{ formatDate(post.published_at) }}</span>
                <RouterLink :to="`/blog/${post.slug}`" class="read-more">ادامه مطلب</RouterLink>
              </div>
            </div>
          </div>
        </div>
        
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
    
    <AppFooter />
  </div>
</template>

<script>
import AppHeader from '@/components/Header.vue';
import AppFooter from '@/components/Footer.vue';
import axios from 'axios';
import { BASE_API_URL } from '@/config';

export default {
  components: {
    AppHeader,
    AppFooter
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
      }
    };
  },
  mounted() {
    this.fetchPosts();
  },
  methods: {
    async fetchPosts(page = 1) {
      this.loading = true;
      this.error = null;
      
      try {
        const response = await axios.get(`${BASE_API_URL}/blog`, {
          params: { page }
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
      // Scroll to top when page changes
      window.scrollTo(0, 0);
    },
    truncateContent(content) {
      // Remove HTML tags and truncate
      const plainText = content.replace(/<[^>]*>/g, '');
      return plainText.length > 150 ? plainText.substring(0, 150) + '...' : plainText;
    },
    formatDate(dateString) {
      const date = new Date(dateString);
      return new Intl.DateTimeFormat('fa-IR', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      }).format(date);
    }
  }
};
</script>

<style scoped>
.blog-container {
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

.post-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 24px;
}

.post-card {
  background-color: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
  transition: transform 0.3s, box-shadow 0.3s;
}

.post-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
}

.post-image {
  height: 200px;
  overflow: hidden;
  background-color: #f8f8f8;
}

.post-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.5s;
}

.post-card:hover .post-image img {
  transform: scale(1.05);
}

.placeholder-image {
  width: 100%;
  height: 100%;
  background-color: #e9e9e9;
  background-image: linear-gradient(45deg, #ddd 25%, transparent 25%, transparent 75%, #ddd 75%, #ddd),
                    linear-gradient(45deg, #ddd 25%, transparent 25%, transparent 75%, #ddd 75%, #ddd);
  background-size: 20px 20px;
  background-position: 0 0, 10px 10px;
}

.post-content {
  padding: 16px;
}

.post-title {
  font-size: 1.3rem;
  margin-bottom: 12px;
  font-weight: 600;
  color: #333;
}

.post-excerpt {
  color: #666;
  font-size: 0.95rem;
  line-height: 1.6;
  margin-bottom: 16px;
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.post-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.9rem;
  color: #888;
}

.read-more {
  color: #A79277;
  text-decoration: none;
  font-weight: 500;
  transition: color 0.2s;
}

.read-more:hover {
  color: #8a7660;
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  margin-top: 40px;
  gap: 16px;
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

.no-posts {
  text-align: center;
  padding: 60px 0;
  color: #888;
}

/* Dark mode */
body.dark-mode .post-card {
  background-color: #262626;
}

body.dark-mode .post-title {
  color: #eee;
}

body.dark-mode .post-excerpt {
  color: #ccc;
}

body.dark-mode .pagination-button:disabled {
  background-color: #555;
}

body.dark-mode .placeholder-image {
  background-color: #333;
  background-image: linear-gradient(45deg, #444 25%, transparent 25%, transparent 75%, #444 75%, #444),
                    linear-gradient(45deg, #444 25%, transparent 25%, transparent 75%, #444 75%, #444);
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .post-grid {
    grid-template-columns: 1fr;
  }
}
</style> 