<template>
  <div class="blog-post-container">
    <AppHeader :title="post.title || 'مقاله'" description="مطالب آموزشی درباره اذکار و فضیلت آن‌ها" />
    
    <main class="container">
      <div v-if="loading" class="loading-container">
        <div class="spinner"></div>
        <p>در حال بارگذاری مقاله...</p>
      </div>
      
      <div v-else-if="error" class="error-message">
        <p>{{ error }}</p>
        <button @click="fetchPost" class="retry-button">تلاش مجدد</button>
        <RouterLink to="/blog" class="back-to-blog">بازگشت به مقالات</RouterLink>
      </div>
      
      <article v-else class="post">
        <div class="post-header">
          <h1>{{ post.title }}</h1>
          <div class="post-meta">
            <div class="author" v-if="post.user">
              <font-awesome-icon icon="fa-solid fa-user" class="author-icon" />
              <div class="author-info">
                <span class="author-label">نویسنده:</span>
                <span class="author-name">{{ post.user.name }}</span>
              </div>
            </div>
            <span class="post-date">{{ formatDate(post.published_at) }}</span>
          </div>
        </div>
        
        <div v-if="post.image" class="post-featured-image">
          <img :src="post.image" :alt="post.title">
        </div>
        
        <div class="post-content" v-html="post.content"></div>
        
        <div class="post-footer">
          <RouterLink to="/blog" class="back-button">
            <font-awesome-icon icon="fa-solid fa-arrow-right" />
            بازگشت به مقالات
          </RouterLink>
          
          <div class="share-buttons">
            <button @click="shareFacebook" class="share-button facebook">
              <font-awesome-icon icon="fa-brands fa-facebook" />
            </button>
            <button @click="shareTwitter" class="share-button twitter">
              <font-awesome-icon icon="fa-brands fa-twitter" />
            </button>
            <button @click="shareWhatsapp" class="share-button whatsapp">
              <font-awesome-icon icon="fa-brands fa-whatsapp" />
            </button>
            <button @click="shareLink" class="share-button link">
              <font-awesome-icon icon="fa-solid fa-link" />
            </button>
          </div>
        </div>
      </article>
      
      <div v-if="!loading && !error" class="related-posts">
        <h3>مقالات مرتبط</h3>
        <div v-if="relatedPosts.length === 0" class="no-related-posts">
          <p>مقاله مرتبطی یافت نشد.</p>
        </div>
        <div v-else class="related-posts-grid">
          <div v-for="relatedPost in relatedPosts" :key="relatedPost.id">
            <PostCard 
              :post="relatedPost" 
              :compact="true" 
              :displayExcerpt="false" 
              :displayAuthor="false"
              :displayReadMore="false"
            />
          </div>
        </div>
      </div>
    </main>
    
    <AppFooter />
    
    <!-- Toast notification for link copied -->
    <div class="toast-notification" v-if="showToast">
      <p>{{ toastMessage }}</p>
    </div>
  </div>
</template>

<script>
import AppHeader from '@/components/Header.vue';
import AppFooter from '@/components/Footer.vue';
import axios from 'axios';
import { BASE_API_URL } from '@/config';
import PostCard from '@/components/PostCard.vue';

export default {
  components: {
    AppHeader,
    AppFooter,
    PostCard
  },
  data() {
    return {
      post: {},
      loading: true,
      error: null,
      showToast: false,
      toastMessage: '',
      relatedPosts: []
    };
  },
  computed: {
    currentUrl() {
      return window.location.href;
    }
  },
  mounted() {
    this.fetchPost();
  },
  watch: {
    // Watch for route changes (when navigating between blog posts)
    '$route.params.slug'() {
      this.fetchPost();
    }
  },
  methods: {
    async fetchPost() {
      this.loading = true;
      this.error = null;
      
      try {
        const slug = this.$route.params.slug;
        const response = await axios.get(`${BASE_API_URL}/blog/${slug}`);
        
        if (response.data.success) {
          this.post = response.data.post;
          
          // Update meta tags for SEO
          document.title = `${this.post.title} | اذکار نور`;
          
          // Update meta description if excerpt exists
          if (this.post.excerpt) {
            const metaDescription = document.querySelector('meta[name="description"]');
            if (metaDescription) {
              metaDescription.setAttribute('content', this.post.excerpt);
            }
          }
          
          // Fetch related posts
          this.fetchRelatedPosts();
        } else {
          this.error = 'خطا در دریافت مقاله';
        }
      } catch (error) {
        console.error('Error fetching blog post:', error);
        this.error = 'مقاله مورد نظر یافت نشد';
      } finally {
        this.loading = false;
      }
    },
    async fetchRelatedPosts() {
      try {
        const response = await axios.get(`${BASE_API_URL}/blog/related/${this.post.id}`);
        if (response.data.success) {
          this.relatedPosts = response.data.posts;
        }
      } catch (error) {
        console.error('Error fetching related posts:', error);
        this.relatedPosts = [];
      }
    },
    formatDate(dateString) {
      if (!dateString) return '';
      
      const date = new Date(dateString);
      
      // For Persian date formatting using browser's intl API
      return new Intl.DateTimeFormat('fa-IR', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      }).format(date);
    },
    shareTwitter() {
      const url = `https://twitter.com/intent/tweet?url=${encodeURIComponent(this.currentUrl)}&text=${encodeURIComponent(this.post.title)}`;
      window.open(url, '_blank');
    },
    shareFacebook() {
      const url = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(this.currentUrl)}`;
      window.open(url, '_blank');
    },
    shareWhatsapp() {
      const url = `https://api.whatsapp.com/send?text=${encodeURIComponent(this.post.title + ' - ' + this.currentUrl)}`;
      window.open(url, '_blank');
    },
    async shareLink() {
      try {
        await navigator.clipboard.writeText(this.currentUrl);
        this.showToastMessage('لینک مقاله کپی شد');
      } catch (err) {
        console.error('Failed to copy link:', err);
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
.blog-post-container {
  min-height: 100vh;
  max-width: 1000px;
  margin: auto;
  display: flex;
  flex-direction: column;
}

.container {
  max-width: 1000px;
  margin: 0 auto;
  padding: 20px 0;
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
  display: flex;
  flex-direction: column;
  align-items: center;
}

.retry-button, .back-to-blog {
  background-color: #A79277;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 4px;
  cursor: pointer;
  margin-top: 10px;
  text-decoration: none;
}

.back-to-blog {
  background-color: #666;
  margin-top: 8px;
}

.post {
  background-color: white;
  border-radius: 12px;
  padding: 30px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05);
}

.post-header {
  margin-bottom: 24px;
}

.post-header h1 {
  font-size: 2.2rem;
  margin-bottom: 16px;
  line-height: 1.3;
  color: #333;
}

.post-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
  color: #777;
  font-size: 0.95rem;
}

.author {
  display: flex;
  align-items: center;
  gap: 8px;
}

.author-icon {
  color: #A79277;
  font-size: 1.1rem;
}

.author-info {
  display: flex;
  align-items: center;
  gap: 5px;
}

.author-label {
  color: #888;
  font-size: 0.9rem;
}

.author-name {
  font-weight: 500;
}

.post-featured-image {
  margin-bottom: 24px;
  border-radius: 12px;
  overflow: hidden;
}

.post-featured-image img {
  width: 100%;
  height: auto;
  display: block;
}

.post-content {
  line-height: 1.8;
  color: #444;
  font-size: 1.1rem;
}

.post-content :deep(h2) {
  font-size: 1.5rem;
  margin: 30px 0 15px;
  color: #333;
}

.post-content :deep(h3) {
  font-size: 1.3rem;
  margin: 25px 0 15px;
  color: #444;
}

.post-content :deep(p) {
  margin-bottom: 16px;
}

.post-content :deep(blockquote) {
  border-right: 4px solid #A79277;
  padding: 10px 20px;
  margin: 20px 0;
  background-color: #f9f9f9;
  font-style: italic;
}

.post-content :deep(ul), .post-content :deep(ol) {
  margin: 20px 0;
  padding-right: 20px;
}

.post-content :deep(li) {
  margin-bottom: 8px;
}

.post-content :deep(a) {
  color: #A79277;
  text-decoration: none;
}

.post-content :deep(a:hover) {
  text-decoration: underline;
}

.post-content :deep(img) {
  max-width: 100%;
  height: auto;
  border-radius: 8px;
  margin: 20px 0;
}

.post-content :deep(code) {
  background-color: #f4f4f4;
  padding: 2px 4px;
  border-radius: 4px;
  font-family: monospace;
}

.post-content :deep(pre) {
  background-color: #f4f4f4;
  padding: 15px;
  border-radius: 8px;
  overflow-x: auto;
  margin: 20px 0;
}

.post-footer {
  margin-top: 40px;
  padding-top: 20px;
  border-top: 1px solid #eee;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.back-button {
  display: flex;
  align-items: center;
  color: #A79277;
  text-decoration: none;
  font-weight: 500;
  gap: 8px;
}

.back-button:hover {
  color: #8a7660;
}

.share-buttons {
  display: flex;
  gap: 10px;
}

.share-button {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  border: none;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  cursor: pointer;
  transition: transform 0.2s;
}

.share-button:hover {
  transform: translateY(-3px);
}

.facebook {
  background-color: #3b5998;
}

.twitter {
  background-color: #1da1f2;
}

.whatsapp {
  background-color: #25d366;
}

.link {
  background-color: #777;
}

/* Related Posts */
.related-posts {
  margin-top: 40px;
}

.related-posts h3 {
  font-size: 1.5rem;
  margin-bottom: 20px;
  color: #333;
  border-right: 4px solid #A79277;
  padding-right: 10px;
}

.related-posts-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 20px;
}

.no-related-posts {
  text-align: center;
  padding: 20px;
  color: #777;
  font-style: italic;
}

.toast-notification {
  position: fixed;
  bottom: 80px;
  left: 50%;
  transform: translateX(-50%);
  background-color: #A79277;
  color: white;
  padding: 12px 20px;
  border-radius: 8px;
  z-index: 1000;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  animation: fadeInOut 3s ease-in-out forwards;
}

@keyframes fadeInOut {
  0% { opacity: 0; transform: translate(-50%, 20px); }
  10% { opacity: 1; transform: translate(-50%, 0); }
  90% { opacity: 1; transform: translate(-50%, 0); }
  100% { opacity: 0; transform: translate(-50%, 20px); }
}

/* Dark mode */
body.dark-mode .post {
  background-color: #262626;
}

body.dark-mode .post-header h1 {
  color: #f5f5f5;
}

body.dark-mode .post-meta {
  color: #aaa;
}

body.dark-mode .post-content {
  color: #ddd;
}

body.dark-mode .post-content :deep(h2),
body.dark-mode .post-content :deep(h3) {
  color: #f0f0f0;
}

body.dark-mode .post-content :deep(blockquote) {
  background-color: #333;
  border-color: #C5B192;
}

body.dark-mode .post-content :deep(code),
body.dark-mode .post-content :deep(pre) {
  background-color: #333;
  color: #f0f0f0;
}

body.dark-mode .post-footer {
  border-top-color: #444;
}

body.dark-mode .related-posts h3 {
  color: #f0f0f0;
}

body.dark-mode .related-post-card {
  background-color: #262626;
}

body.dark-mode .related-post-content h4 {
  color: #f0f0f0;
}

body.dark-mode .related-post-date {
  color: #aaa;
}

body.dark-mode .placeholder-image {
  background-color: #333;
  background-image: linear-gradient(45deg, #444 25%, transparent 25%, transparent 75%, #444 75%, #444),
                    linear-gradient(45deg, #444 25%, transparent 25%, transparent 75%, #444 75%, #444);
}

body.dark-mode .error-message {
  background-color: #452726;
  color: #ff8f8f;
}

/* Responsive */
@media (max-width: 768px) {
  .post {
    padding: 20px;
  }
  
  .post-header h1 {
    font-size: 1.8rem;
  }
  
  .post-footer {
    flex-direction: column;
    gap: 20px;
  }
  
  .post-meta {
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
  }
  
  .related-posts-grid {
    grid-template-columns: 1fr;
  }
}
</style> 