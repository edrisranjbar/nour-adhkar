<template>
  <div class="profile-stats">
    <h1>آمار و پیشرفت</h1>

    <!-- Summary Cards -->
    <div class="stats-grid">
      <div class="stat-card total-dhikr">
        <div class="stat-icon">
          <font-awesome-icon icon="fa-solid fa-calculator" />
        </div>
        <div class="stat-content">
          <h3>کل اذکار</h3>
          <p class="stat-value">{{ totalDhikrCount }}</p>
          <p class="stat-label">مجموع تکرار</p>
        </div>
      </div>

      <div class="stat-card daily-streak">
        <div class="stat-icon">
          <font-awesome-icon icon="fa-solid fa-fire" />
        </div>
        <div class="stat-content">
          <h3>روزهای پیاپی</h3>
          <p class="stat-value">{{ dailyStreak }}</p>
          <p class="stat-label">روز</p>
        </div>
      </div>

      <div class="stat-card completion-rate">
        <div class="stat-icon">
          <font-awesome-icon icon="fa-solid fa-chart-pie" />
        </div>
        <div class="stat-content">
          <h3>نرخ تکمیل</h3>
          <p class="stat-value">{{ completionRate }}%</p>
          <p class="stat-label">میانگین روزانه</p>
        </div>
      </div>

      <div class="stat-card favorite-dhikr">
        <div class="stat-icon">
          <font-awesome-icon icon="fa-solid fa-heart" />
        </div>
        <div class="stat-content">
          <h3>ذکر محبوب</h3>
          <p class="stat-value">{{ favoriteDhikr.title || 'بدون ذکر' }}</p>
          <p class="stat-label">{{ favoriteDhikr.count || 0 }} بار</p>
        </div>
      </div>
    </div>

    <!-- Progress Chart -->
    <div class="chart-section">
      <div class="section-header">
        <h2>نمودار پیشرفت</h2>
        <div class="chart-controls">
          <button 
            v-for="period in ['week', 'month', 'year']" 
            :key="period"
            :class="['chart-period', { active: selectedPeriod === period }]"
            @click="selectedPeriod = period"
          >
            {{ periodLabels[period] }}
          </button>
        </div>
      </div>
      <div class="chart-container">
        <!-- Chart will be rendered here -->
        <canvas ref="progressChart"></canvas>
      </div>
    </div>

    <!-- Category Distribution -->
    <div class="stats-section">
      <h2>توزیع دسته‌بندی‌ها</h2>
      <div class="category-stats">
        <div 
          v-for="category in categoryStats" 
          :key="category.name" 
          class="category-item"
        >
          <div class="category-info">
            <span class="category-name">{{ category.name }}</span>
            <span class="category-count">{{ category.count }}</span>
          </div>
          <div class="progress-bar">
            <div 
              class="progress-fill" 
              :style="{ width: `${category.percentage}%` }"
            ></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Activity -->
    <div class="stats-section">
      <h2>فعالیت‌های اخیر</h2>
      <div class="activity-list">
        <div 
          v-for="activity in recentActivities" 
          :key="activity.id" 
          class="activity-item"
        >
          <div class="activity-icon">
            <font-awesome-icon :icon="activity.icon" />
          </div>
          <div class="activity-content">
            <p class="activity-title">{{ activity.title }}</p>
            <p class="activity-time">{{ formatTime(activity.timestamp) }}</p>
          </div>
          <div class="activity-count" v-if="activity.count">
            {{ activity.count }} بار
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import Chart from 'chart.js/auto'

export default {
  name: 'ProfileStatsView',
  setup() {
    const progressChart = ref(null)
    const selectedPeriod = ref('week')
    const periodLabels = {
      week: 'هفته',
      month: 'ماه',
      year: 'سال'
    }

    return {
      progressChart,
      selectedPeriod,
      periodLabels
    }
  },
  data() {
    return {
      totalDhikrCount: 0,
      dailyStreak: 0,
      completionRate: 0,
      favoriteDhikr: {},
      categoryStats: [],
      recentActivities: [],
      chart: null
    }
  },
  watch: {
    selectedPeriod: {
      handler() {
        this.updateChart()
      }
    }
  },
  methods: {
    async loadStats() {
      try {
        const stats = await this.$store.dispatch('loadUserStats')
        this.totalDhikrCount = stats.totalCount
        this.dailyStreak = stats.streak
        this.completionRate = stats.completionRate
        this.favoriteDhikr = stats.favoriteDhikr
        this.categoryStats = stats.categories
        this.recentActivities = stats.recentActivities
      } catch (error) {
        console.error('Error loading stats:', error)
        // Show error toast
      }
    },
    formatTime(timestamp) {
      const date = new Date(timestamp)
      return new Intl.RelativeTimeFormat('fa').format(
        Math.ceil((date - new Date()) / (1000 * 60 * 60 * 24)),
        'day'
      )
    },
    async updateChart() {
      try {
        const data = await this.$store.dispatch('loadChartData', this.selectedPeriod)
        
        if (this.chart) {
          this.chart.destroy()
        }

        const ctx = this.progressChart.value.getContext('2d')
        this.chart = new Chart(ctx, {
          type: 'line',
          data: {
            labels: data.labels,
            datasets: [{
              label: 'تعداد اذکار',
              data: data.values,
              borderColor: '#A79277',
              backgroundColor: 'rgba(167, 146, 119, 0.1)',
              fill: true,
              tension: 0.4
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                display: false
              }
            },
            scales: {
              y: {
                beginAtZero: true,
                grid: {
                  color: 'rgba(0, 0, 0, 0.1)'
                }
              },
              x: {
                grid: {
                  display: false
                }
              }
            }
          }
        })
      } catch (error) {
        console.error('Error updating chart:', error)
        // Show error toast
      }
    }
  },
  async mounted() {
    await this.loadStats()
    this.updateChart()
  }
}
</script>

<style scoped>
.profile-stats {
  padding: 1rem;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 1rem;
  margin: 2rem 0;
}

.stat-card {
  background: white;
  border-radius: 8px;
  padding: 1.5rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.stat-icon {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background-color: rgba(167, 146, 119, 0.1);
  color: #A79277;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
}

.stat-content {
  flex: 1;
}

.stat-content h3 {
  margin: 0;
  font-size: 0.875rem;
  color: #666;
}

.stat-value {
  margin: 0.5rem 0;
  font-size: 1.5rem;
  font-weight: bold;
  color: #333;
}

.stat-label {
  margin: 0;
  font-size: 0.875rem;
  color: #666;
}

.chart-section {
  background: white;
  border-radius: 8px;
  padding: 1.5rem;
  margin: 2rem 0;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.section-header h2 {
  margin: 0;
  font-size: 1.25rem;
  color: #333;
}

.chart-controls {
  display: flex;
  gap: 0.5rem;
}

.chart-period {
  padding: 0.5rem 1rem;
  border: 1px solid #dee2e6;
  border-radius: 4px;
  background: none;
  color: #666;
  cursor: pointer;
  transition: all 0.2s ease;
}

.chart-period.active {
  background-color: #A79277;
  border-color: #A79277;
  color: white;
}

.chart-container {
  height: 300px;
}

.stats-section {
  background: white;
  border-radius: 8px;
  padding: 1.5rem;
  margin: 2rem 0;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.stats-section h2 {
  margin: 0 0 1.5rem;
  font-size: 1.25rem;
  color: #333;
}

.category-stats {
  display: grid;
  gap: 1rem;
}

.category-item {
  display: grid;
  gap: 0.5rem;
}

.category-info {
  display: flex;
  justify-content: space-between;
  align-items: center;
  color: #333;
}

.category-count {
  color: #666;
  font-size: 0.875rem;
}

.progress-bar {
  height: 8px;
  background-color: #f8f9fa;
  border-radius: 4px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background-color: #A79277;
  border-radius: 4px;
  transition: width 0.3s ease;
}

.activity-list {
  display: grid;
  gap: 1rem;
}

.activity-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  background-color: #f8f9fa;
  border-radius: 4px;
}

.activity-icon {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background-color: rgba(167, 146, 119, 0.1);
  color: #A79277;
  display: flex;
  align-items: center;
  justify-content: center;
}

.activity-content {
  flex: 1;
}

.activity-title {
  margin: 0;
  color: #333;
}

.activity-time {
  margin: 0.25rem 0 0;
  font-size: 0.875rem;
  color: #666;
}

.activity-count {
  padding: 0.25rem 0.75rem;
  background-color: rgba(167, 146, 119, 0.1);
  color: #A79277;
  border-radius: 999px;
  font-size: 0.875rem;
}

body.dark-mode {
  .stat-card,
  .chart-section,
  .stats-section {
    background-color: #333;
  }

  .stat-content h3,
  .stat-label,
  .category-count,
  .activity-time {
    color: #aaa;
  }

  .stat-value,
  .section-header h2,
  .stats-section h2,
  .category-info,
  .activity-title {
    color: #fff;
  }

  .chart-period {
    border-color: #555;
    color: #aaa;
  }

  .chart-period.active {
    background-color: #A79277;
    border-color: #A79277;
    color: white;
  }

  .progress-bar {
    background-color: #444;
  }

  .activity-item {
    background-color: #444;
  }
}

@media (max-width: 768px) {
  .stats-grid {
    grid-template-columns: 1fr;
  }

  .chart-section {
    margin: 1rem 0;
  }

  .section-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
  }

  .chart-container {
    height: 250px;
  }
}
</style> 