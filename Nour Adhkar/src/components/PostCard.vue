<template>
  <div class="post-card">
    <RouterLink :to="`/blog/${post.slug}`" class="post-link">
      <div class="post-image">
        <img v-if="post.featured_image" :src="post.featured_image" :alt="post.title">
        <div v-else class="placeholder-image"></div>
      </div>
      <div class="post-content">
        <h3 class="post-title">{{ post.title }}</h3>
        <p v-if="displayExcerpt && post.excerpt" class="post-excerpt">{{ post.excerpt }}</p>
        <p v-else-if="displayExcerpt" class="post-excerpt">{{ truncateContent(post.content) }}</p>
        <div class="post-meta">
          <span class="post-date">{{ formatDate(post.published_at) }}</span>
          <span v-if="displayReadMore" class="read-more">ادامه مطلب</span>
        </div>
      </div>
    </RouterLink>
  </div>
</template>

<script>
export default {
  name: 'PostCard',
  props: {
    post: {
      type: Object,
      required: true
    },
    displayExcerpt: {
      type: Boolean,
      default: true
    },
    displayAuthor: {
      type: Boolean,
      default: true
    },
    displayReadMore: {
      type: Boolean,
      default: true
    },
    compact: {
      type: Boolean,
      default: false
    }
  },
  methods: {
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
    truncateContent(content) {
      if (!content) return '';
      
      // Remove HTML tags and truncate
      const plainText = content.replace(/<[^>]*>/g, '');
      return plainText.length > 150 ? plainText.substring(0, 150) + '...' : plainText;
    }
  }
}
</script>

<style scoped>
.post-card {
  background-color: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
  transition: transform 0.3s, box-shadow 0.3s;
  height: 100%;
  display: flex;
  flex-direction: column;
}

.post-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
}

.post-link {
  text-decoration: none;
  color: inherit;
  display: flex;
  flex-direction: column;
  height: 100%;
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
  flex-grow: 1;
  display: flex;
  flex-direction: column;
}

.post-title {
  font-size: 1.3rem;
  margin-top: 0;
  margin-bottom: 12px;
  font-weight: 600;
  color: #333;
  line-height: 1.4;
}

.post-excerpt {
  color: #666;
  font-size: 0.95rem;
  line-height: 1.6;
  margin-bottom: 16px;
  display: -webkit-box;
  -webkit-line-clamp: 3;
  line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
  flex-grow: 1;
}

.post-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.9rem;
  color: #888;
  margin-top: auto;
}

.post-author {
  display: flex;
  align-items: center;
  gap: 5px;
}

.author-icon {
  color: #A79277;
  font-size: 0.9rem;
}

.post-date {
  font-size: 0.85rem;
}

.read-more {
  color: #A79277;
  font-weight: 500;
  transition: color 0.2s;
}

.post-card:hover .read-more {
  color: #8a7660;
}

/* Compact mode for related posts */
.post-card.compact .post-image {
  height: 150px;
}

.post-card.compact .post-title {
  font-size: 1.1rem;
  margin-bottom: 8px;
}

.post-card.compact .post-content {
  padding: 12px;
}

/* Dark mode */
body.dark-mode .post-card {
  background-color: #262626;
}

body.dark-mode .post-title {
  color: #f0f0f0;
}

body.dark-mode .post-excerpt {
  color: #ccc;
}

body.dark-mode .post-meta {
  color: #aaa;
}

body.dark-mode .placeholder-image {
  background-color: #333;
  background-image: linear-gradient(45deg, #444 25%, transparent 25%, transparent 75%, #444 75%, #444),
                    linear-gradient(45deg, #444 25%, transparent 25%, transparent 75%, #444 75%, #444);
}

/* Responsive adjustments */
@media (max-width: 767px) {
  .post-image {
    height: 180px;
  }
  
  .post-title {
    font-size: 1.1rem;
  }
  
  .post-card.compact .post-image {
    height: 120px;
  }
}
</style> 