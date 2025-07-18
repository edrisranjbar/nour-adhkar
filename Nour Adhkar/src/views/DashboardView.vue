<template>
  <div class="dashboard-bg">
    <div class="dashboard-container">
      <!-- Profile -->
      <section class="profile-card">
        <div class="profile-avatar">
          <img :src="user?.avatar || defaultAvatar" alt="avatar" />
        </div>
        <div class="profile-info">
          <div class="profile-name">{{ user?.name || 'کاربر' }}</div>
          <div class="profile-score-badge">{{ user?.xp || 0 }}</div>
        </div>
      </section>
      <!-- Stats -->
      <section class="stats-row">
        <div class="stat-card streak">
          <span class="stat-icon">🔥</span>
          <div class="stat-label">استریک</div>
          <div class="stat-value">{{ user?.streak || 0 }}</div>
        </div>
        <div class="stat-card points">
          <span class="stat-icon">⭐</span>
          <div class="stat-label">امتیاز</div>
          <div class="stat-value">{{ user?.xp || 0 }}</div>
        </div>
      </section>
      <!-- Weekly Activity -->
      <section class="activity-card">
        <div class="activity-title">فعالیت هفتگی</div>
        <div class="activity-calendar">
          <div
            v-for="(day, i) in lastWeek"
            :key="i"
            class="activity-day"
            :class="{
              active: day.completed,
              today: day.isToday,
              missed: !day.completed && !day.isToday
            }"
            :title="day.label"
          >
            <span v-if="day.completed">✔️</span>
            <span v-else-if="day.isToday">●</span>
            <span v-else>○</span>
          </div>
        </div>
        <div class="activity-progress-bar">
          <div class="activity-progress" :style="{ width: weekProgress + '%' }"></div>
        </div>
      </section>
    </div>
  </div>
</template>

<script>
import { computed } from 'vue'
import { useStore } from 'vuex'
import defaultAvatar from '@/assets/icons/back-button.svg' // Replace with a real default avatar

export default {
  name: 'DashboardView',
  setup() {
    const store = useStore()
    const user = computed(() => store.state.user)
    // Dummy data for lastWeek, replace with real logic
    const lastWeek = computed(() => {
      const days = []
      const today = new Date()
      for (let i = 6; i >= 0; i--) {
        const date = new Date(today)
        date.setDate(date.getDate() - i)
        days.push({
          label: date.toLocaleDateString('fa-IR', { weekday: 'long', day: 'numeric', month: 'short' }),
          completed: Math.random() > 0.3, // Replace with real completion logic
          isToday: i === 0
        })
      }
      return days
    })
    const weekProgress = computed(() => {
      const completed = lastWeek.value.filter(d => d.completed).length
      return Math.round((completed / 7) * 100)
    })
    return { user, lastWeek, weekProgress, defaultAvatar }
  }
}
</script>

<style>
@import '@/assets/css/fonts.css';
.dashboard-bg {
  min-height: 100vh;
  background: linear-gradient(135deg, #f7fafd 0%, #e0f7fa 100%);
  display: flex;
  justify-content: center;
  align-items: flex-start;
  font-family: 'Vazirmatn', Arial, sans-serif;
  padding: 0 0 40px 0;
}
.dashboard-container {
  width: 100%;
  max-width: 420px;
  margin: 0 auto;
  padding: 24px 0 0 0;
  display: flex;
  flex-direction: column;
  gap: 24px;
}
.profile-card {
  background: #fff;
  border-radius: 24px;
  box-shadow: 0 4px 24px rgba(60,80,120,0.10);
  display: flex;
  align-items: center;
  gap: 18px;
  padding: 20px 24px;
}
.profile-avatar {
  width: 56px; height: 56px;
  border-radius: 50%;
  background: #e0f7fa;
  box-shadow: 0 2px 8px rgba(60,80,120,0.10);
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}
.profile-avatar img {
  width: 100%; height: 100%; object-fit: cover;
  border-radius: 50%;
}
.profile-info {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 4px;
}
.profile-name {
  font-size: 1.2rem;
  font-weight: 900;
  color: #222;
}
.profile-score-badge {
  background: #e0f7fa;
  color: #009688;
  font-size: 1.1rem;
  font-weight: 900;
  border-radius: 12px;
  padding: 2px 12px;
  display: inline-block;
  margin-top: 2px;
}
.stats-row {
  display: flex;
  gap: 16px;
  justify-content: space-between;
}
.stat-card {
  flex: 1;
  background: #fff;
  border-radius: 18px;
  box-shadow: 0 2px 12px rgba(60,80,120,0.10);
  padding: 16px 0 12px 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  transition: box-shadow 0.2s, transform 0.2s;
  min-width: 0;
}
.stat-icon {
  font-size: 1.7rem;
  margin-bottom: 2px;
  display: block;
}
.stat-card.streak .stat-icon { color: #f59e42; }
.stat-card.points .stat-icon { color: #58cc02; }
.stat-label {
  font-size: 1rem;
  color: #888;
  font-weight: 700;
  margin-bottom: 2px;
}
.stat-value {
  font-size: 1.3rem;
  font-weight: 900;
  color: #222;
}
.activity-card {
  background: #fff;
  border-radius: 24px;
  box-shadow: 0 4px 24px rgba(60,80,120,0.10);
  padding: 18px 14px 14px 14px;
  display: flex;
  flex-direction: column;
  align-items: stretch;
  gap: 10px;
}
.activity-title {
  font-size: 1.1rem;
  font-weight: 900;
  color: #1cb0f6;
  margin-bottom: 8px;
}
.activity-calendar {
  display: flex;
  gap: 8px;
  justify-content: space-between;
  margin-bottom: 2px;
}
.activity-day {
  width: 32px; height: 32px;
  border-radius: 50%;
  background: #f3f4f6;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.1rem;
  color: #bbb;
  border: 2px solid transparent;
  transition: background 0.2s, border 0.2s;
  cursor: pointer;
  font-weight: 900;
}
.activity-day.active {
  background: #bbf7d0;
  color: #58cc02;
  border-color: #58cc02;
}
.activity-day.today {
  background: #dbeafe;
  color: #1cb0f6;
  border-color: #1cb0f6;
}
.activity-day.missed {
  background: #ffe066;
  color: #f59e42;
  border-color: #ffe066;
}
.activity-progress-bar {
  height: 8px;
  background: #e5e7eb;
  border-radius: 8px;
  overflow: hidden;
  margin-top: 6px;
}
.activity-progress {
  height: 100%;
  background: linear-gradient(90deg, #58cc02 0%, #1cb0f6 100%);
  border-radius: 8px;
  transition: width 0.5s;
}
@media (min-width: 600px) {
  .dashboard-container { max-width: 540px; }
  .profile-card, .activity-card { padding: 28px 32px; }
  .stats-row { gap: 24px; }
  .activity-calendar { gap: 14px; }
  .activity-day { width: 40px; height: 40px; font-size: 1.2rem; }
}
@media (min-width: 900px) {
  .dashboard-container { max-width: 800px; gap: 32px; }
  .profile-card, .activity-card { padding: 36px 48px; }
  .stats-row { gap: 32px; }
  .activity-calendar { gap: 22px; }
  .activity-day { width: 52px; height: 52px; font-size: 1.5rem; }
}
</style>