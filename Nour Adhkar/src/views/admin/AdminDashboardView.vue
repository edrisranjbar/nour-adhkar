<template>
  <div class="admin-dashboard">
    <h1 class="dashboard-title">پیشخوان مدیریت</h1>
    
    <div class="stats-container">
      <div class="stat-card">
        <div class="stat-icon blog-icon">
          <font-awesome-icon icon="fa-solid fa-newspaper" />
        </div>
        <div class="stat-content">
          <span class="stat-value">{{ stats.blogPostsCount || 0 }}</span>
          <span class="stat-label">مقالات</span>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon user-icon">
          <font-awesome-icon icon="fa-solid fa-users" />
        </div>
        <div class="stat-content">
          <span class="stat-value">{{ stats.usersCount || 0 }}</span>
          <span class="stat-label">کاربران</span>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon view-icon">
          <font-awesome-icon icon="fa-solid fa-eye" />
        </div>
        <div class="stat-content">
          <span class="stat-value">{{ formatNumber(stats.totalViews || 0) }}</span>
          <span class="stat-label">بازدیدها</span>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon comment-icon">
          <font-awesome-icon icon="fa-solid fa-comments" />
        </div>
        <div class="stat-content">
          <span class="stat-value">{{ stats.commentsCount || 0 }}</span>
          <span class="stat-label">نظرات</span>
        </div>
      </div>
    </div>
    
    <div class="dashboard-sections">
      <div class="recent-posts section">
        <div class="section-header">
          <h2 class="section-title">آخرین مقالات</h2>
          <RouterLink to="/admin/blog" class="view-all-link">
            همه مقالات
            <font-awesome-icon icon="fa-solid fa-arrow-left" />
          </RouterLink>
        </div>
        
        <div class="section-content">
          <div v-if="loading" class="loading-spinner">
            <span class="spinner"></span>
            در حال بارگذاری...
          </div>
          
          <div v-else-if="!recentPosts.length" class="empty-state">
            <font-awesome-icon icon="fa-solid fa-newspaper" class="empty-icon" />
            <p>هنوز مقاله‌ای ثبت نشده است.</p>
            <RouterLink to="/admin/blog/new" class="action-button">
              ایجاد مقاله جدید
            </RouterLink>
          </div>
          
          <table v-else class="data-table">
            <thead>
              <tr>
                <th>عنوان</th>
                <th>وضعیت</th>
                <th>تاریخ انتشار</th>
                <th>بازدید</th>
                <th>عملیات</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="post in recentPosts" :key="post.id">
                <td class="post-title">
                  <div class="title-content">
                    <div v-if="post.featured" class="featured-badge">ویژه</div>
                    <span>{{ post.title }}</span>
                  </div>
                </td>
                <td>
                  <span :class="['status-badge', getStatusClass(post)]">
                    {{ getStatusText(post) }}
                  </span>
                </td>
                <td>{{ formatDate(post.published_at) }}</td>
                <td>{{ post.views || 0 }}</td>
                <td class="actions-cell">
                  <RouterLink 
                    :to="`/blog/${post.slug}`" 
                    target="_blank" 
                    class="action-button view-button" 
                    title="مشاهده"
                  >
                    <font-awesome-icon icon="fa-solid fa-eye" />
                  </RouterLink>
                  <RouterLink 
                    :to="`/admin/blog/edit/${post.id}`" 
                    class="action-button edit-button" 
                    title="ویرایش"
                  >
                    <font-awesome-icon icon="fa-solid fa-pen" />
                  </RouterLink>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      
      <div class="quick-actions section">
        <h2 class="section-title">عملیات سریع</h2>
        
        <div class="actions-grid">
          <div class="action-card" @click="goTo('/admin/blog/new')">
            <div class="action-icon">
              <font-awesome-icon icon="fa-solid fa-plus" />
            </div>
            <span class="action-label">ایجاد مقاله جدید</span>
          </div>
          
          <div class="action-card" @click="goTo('/admin/blog')">
            <div class="action-icon">
              <font-awesome-icon icon="fa-solid fa-list" />
            </div>
            <span class="action-label">مدیریت مقالات</span>
          </div>
          
          <div class="action-card" @click="goTo('/admin/users')">
            <div class="action-icon">
              <font-awesome-icon icon="fa-solid fa-users" />
            </div>
            <span class="action-label">مدیریت کاربران</span>
          </div>
          
          <!-- Future quick actions can be added here -->
          <div class="action-card" @click="refreshStats">
            <div class="action-icon">
              <font-awesome-icon icon="fa-solid fa-sync" />
            </div>
            <span class="action-label">بروزرسانی آمار</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import { BASE_API_URL } from '@/config';
import { library } from '@fortawesome/fontawesome-svg-core';
import { 
  faNewspaper, 
  faUsers, 
  faEye, 
  faComments, 
  faPlus, 
  faList, 
  faSync, 
  faPen,
  faArrowLeft
} from '@fortawesome/free-solid-svg-icons';

// Add icons to the library
library.add(
  faNewspaper, 
  faUsers, 
  faEye, 
  faComments, 
  faPlus, 
  faList, 
  faSync, 
  faPen,
  faArrowLeft
);

export default {
  name: 'AdminDashboardView',
  data() {
    return {
      loading: true,
      recentPosts: [],
      stats: {
        blogPostsCount: 0,
        usersCount: 0,
        totalViews: 0,
        commentsCount: 0
      }
    };
  },
  computed: {
    token() {
      return this.$store.state.token;
    }
  },
  mounted() {
    this.fetchDashboardData();
  },
  methods: {
    async fetchDashboardData() {
      this.loading = true;
      try {
        await Promise.all([
          this.fetchStats(),
          this.fetchRecentPosts()
        ]);
      } catch (error) {
        console.error('Error fetching dashboard data:', error);
      } finally {
        this.loading = false;
      }
    },
    
    async fetchStats() {
      try {
        // Try to get blog post count
        const blogCountResponse = await this.getBlogPostsCount();
        this.stats.blogPostsCount = blogCountResponse;
        
        // Get users count 
        const usersResponse = await this.getUsersCount();
        this.stats.usersCount = usersResponse;
        
        // Use mock data for metrics that don't have specific endpoints yet
        this.stats.totalViews = 150;
        this.stats.commentsCount = 8;
      } catch (error) {
        console.error('Error fetching stats:', error);
      }
    },
    
    async getBlogPostsCount() {
      try {
        const response = await axios.get(`${BASE_API_URL}/admin/blog`, {
          headers: {
            Authorization: `Bearer ${this.token}`
          }
        });
        
        if (response.data.success && response.data.posts) {
          return response.data.posts.total || 0;
        }
        return 0;
      } catch (error) {
        console.error('Error getting blog posts count:', error);
        return 0;
      }
    },
    
    async getUsersCount() {
      try {
        const response = await axios.get(`${BASE_API_URL}/admin/users`, {
          params: {
            per_page: 1
          },
          headers: {
            Authorization: `Bearer ${this.token}`
          }
        });
        
        if (response.data.success && response.data.users) {
          return response.data.users.total || 0;
        }
        return 0;
      } catch (error) {
        console.error('Error getting users count:', error);
        return 0;
      }
    },
    
    async fetchRecentPosts() {
      try {
        const response = await axios.get(`${BASE_API_URL}/admin/blog`, {
          params: {
            per_page: 5
          },
          headers: {
            Authorization: `Bearer ${this.token}`
          }
        });
        
        if (response.data.success && response.data.posts && response.data.posts.data) {
          this.recentPosts = response.data.posts.data;
        }
      } catch (error) {
        console.error('Error fetching recent posts:', error);
        this.recentPosts = [];
      }
    },
    
    formatDate(dateString) {
      if (!dateString) return 'منتشر نشده';
      
      // Convert to Persian date
      const date = new Date(dateString);
      return new Intl.DateTimeFormat('fa-IR', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      }).format(date);
    },
    
    formatNumber(num) {
      return new Intl.NumberFormat('fa-IR').format(num);
    },
    
    getStatusClass(post) {
      if (post.status === 'draft') return 'draft';
      if (post.featured) return 'featured';
      return 'published';
    },
    
    getStatusText(post) {
      if (post.status === 'draft') return 'پیش‌نویس';
      if (post.featured) return 'ویژه';
      return 'منتشر شده';
    },
    
    goTo(path) {
      this.$router.push(path);
    },
    
    refreshStats() {
      this.fetchDashboardData();
    }
  }
};
</script>

<style scoped>
.admin-dashboard {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1rem;
}

.dashboard-title {
  margin-bottom: 2rem;
  font-size: 1.8rem;
  color: #333;
}

.stats-container {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1.5rem;
  margin-bottom: 2.5rem;
}

@media (max-width: 992px) {
  .stats-container {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 576px) {
  .stats-container {
    grid-template-columns: 1fr;
  }
}

.stat-card {
  background-color: white;
  border-radius: 8px;
  padding: 1.25rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  display: flex;
  align-items: center;
  gap: 1rem;
}

.stat-icon {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  color: white;
}

.blog-icon {
  background-color: #A79277;
}

.user-icon {
  background-color: #4a6fa5;
}

.view-icon {
  background-color: #28a745;
}

.comment-icon {
  background-color: #fd7e14;
}

.stat-content {
  display: flex;
  flex-direction: column;
}

.stat-value {
  font-size: 1.5rem;
  font-weight: 600;
  color: #333;
}

.stat-label {
  font-size: 0.95rem;
  color: #777;
}

.dashboard-sections {
  display: grid;
  grid-template-columns: 3fr 1fr;
  gap: 1.5rem;
}

@media (max-width: 992px) {
  .dashboard-sections {
    grid-template-columns: 1fr;
  }
}

.section {
  background-color: white;
  border-radius: 8px;
  padding: 1.5rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.25rem;
}

.section-title {
  font-size: 1.25rem;
  color: #333;
  margin: 0;
}

.view-all-link {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #A79277;
  text-decoration: none;
  font-size: 0.95rem;
}

.view-all-link:hover {
  text-decoration: underline;
}

.loading-spinner {
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 2rem;
  color: #777;
}

.spinner {
  display: inline-block;
  width: 1.5rem;
  height: 1.5rem;
  border: 2px solid #f3f3f3;
  border-top: 2px solid #A79277;
  border-radius: 50%;
  margin-left: 1rem;
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
  color: #777;
}

.empty-icon {
  font-size: 3rem;
  color: #ddd;
  margin-bottom: 1rem;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.95rem;
}

.data-table th,
.data-table td {
  padding: 0.75rem;
  text-align: right;
  border-bottom: 1px solid #eee;
}

.data-table th {
  font-weight: 600;
  color: #555;
}

.post-title {
  font-weight: 500;
}

.title-content {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.featured-badge {
  background-color: #A79277;
  color: white;
  font-size: 0.75rem;
  padding: 0.15rem 0.45rem;
  border-radius: 12px;
}

.status-badge {
  padding: 0.25rem 0.6rem;
  border-radius: 12px;
  font-size: 0.8rem;
  font-weight: 500;
}

.status-badge.draft {
  background-color: #f2f2f2;
  color: #777;
}

.status-badge.published {
  background-color: #e6f7ed;
  color: #28a745;
}

.status-badge.featured {
  background-color: #e6f0f9;
  color: #4a6fa5;
}

.actions-cell {
  white-space: nowrap;
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

.actions-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
  gap: 1rem;
  margin-top: 1.5rem;
}

.action-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  background-color: #f8f8f8;
  border-radius: 8px;
  padding: 1.25rem;
  cursor: pointer;
  transition: all 0.2s;
}

.action-card:hover {
  background-color: #f0f0f0;
  transform: translateY(-2px);
}

.action-icon {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background-color: #A79277;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
  margin-bottom: 0.75rem;
}

.action-label {
  font-size: 0.85rem;
  text-align: center;
  color: #555;
}

/* Dark mode styles */
body.dark-mode .dashboard-title {
  color: #eee;
}

body.dark-mode .stat-card,
body.dark-mode .section {
  background-color: #333;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
}

body.dark-mode .stat-value {
  color: #eee;
}

body.dark-mode .stat-label,
body.dark-mode .section-title {
  color: #bbb;
}

body.dark-mode .data-table th {
  color: #bbb;
}

body.dark-mode .data-table td {
  border-bottom-color: #444;
}

body.dark-mode .status-badge.draft {
  background-color: #444;
  color: #aaa;
}

body.dark-mode .status-badge.published {
  background-color: #1a3b29;
  color: #4ade80;
}

body.dark-mode .status-badge.featured {
  background-color: #1a2c42;
  color: #60a5fa;
}

body.dark-mode .action-button {
  color: #aaa;
}

body.dark-mode .action-card {
  background-color: #3a3a3a;
}

body.dark-mode .action-card:hover {
  background-color: #444;
}

body.dark-mode .action-label {
  color: #ccc;
}
</style> 