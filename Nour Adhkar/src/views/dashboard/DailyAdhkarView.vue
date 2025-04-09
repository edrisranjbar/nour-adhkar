<template>
  <div class="daily-adhkar">
    <h1>اذکار روزانه</h1>
    <div class="adhkar-progress">
      <div class="progress-card">
        <div class="progress-header">
          <h3>پیشرفت امروز</h3>
          <span class="progress-percentage">{{ progress }}%</span>
        </div>
        <div class="progress-bar">
          <div class="progress-fill" :style="{ width: `${progress}%` }"></div>
        </div>
      </div>
    </div>

    <div class="adhkar-list">
      <div v-for="dhikr in dailyAdhkar" :key="dhikr.id" class="dhikr-card">
        <div class="dhikr-header">
          <h3>{{ dhikr.title }}</h3>
          <span class="dhikr-count">{{ dhikr.count }}/{{ dhikr.target }}</span>
        </div>
        <p class="dhikr-text">{{ dhikr.text }}</p>
        <div class="dhikr-actions">
          <button class="action-button" @click="incrementCount(dhikr)">
            <font-awesome-icon icon="fa-solid fa-plus" />
            افزودن
          </button>
          <button class="action-button" @click="resetCount(dhikr)">
            <font-awesome-icon icon="fa-solid fa-redo" />
            شروع مجدد
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'DailyAdhkarView',
  data() {
    return {
      dailyAdhkar: [
        {
          id: 1,
          title: 'سبحان الله',
          text: 'سبحان الله',
          count: 0,
          target: 33
        },
        {
          id: 2,
          title: 'الحمد لله',
          text: 'الحمد لله',
          count: 0,
          target: 33
        },
        {
          id: 3,
          title: 'الله اکبر',
          text: 'الله اکبر',
          count: 0,
          target: 33
        }
      ]
    }
  },
  computed: {
    progress() {
      const totalCount = this.dailyAdhkar.reduce((sum, dhikr) => sum + dhikr.count, 0);
      const totalTarget = this.dailyAdhkar.reduce((sum, dhikr) => sum + dhikr.target, 0);
      return Math.round((totalCount / totalTarget) * 100);
    }
  },
  methods: {
    incrementCount(dhikr) {
      if (dhikr.count < dhikr.target) {
        dhikr.count++;
      }
    },
    resetCount(dhikr) {
      dhikr.count = 0;
    }
  }
}
</script>

<style scoped>
.daily-adhkar {
  padding: 1rem;
}

.adhkar-progress {
  margin: 2rem 0;
}

.progress-card {
  background: white;
  border-radius: 8px;
  padding: 1.5rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.progress-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.progress-header h3 {
  margin: 0;
  font-size: 1.1rem;
  color: #333;
}

.progress-percentage {
  font-size: 1.2rem;
  font-weight: 600;
  color: #A79277;
}

.progress-bar {
  height: 8px;
  background-color: #eee;
  border-radius: 4px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background-color: #A79277;
  transition: width 0.3s ease;
}

.adhkar-list {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 1rem;
}

.dhikr-card {
  background: white;
  border-radius: 8px;
  padding: 1.5rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.dhikr-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.dhikr-header h3 {
  margin: 0;
  font-size: 1.1rem;
  color: #333;
}

.dhikr-count {
  font-size: 1rem;
  color: #666;
}

.dhikr-text {
  font-size: 1.5rem;
  text-align: center;
  margin: 1rem 0;
  color: #333;
}

.dhikr-actions {
  display: flex;
  gap: 0.5rem;
}

.action-button {
  flex: 1;
  padding: 0.75rem;
  border: none;
  border-radius: 4px;
  background-color: #A79277;
  color: white;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  transition: all 0.2s ease;
}

.action-button:hover {
  background-color: #8a7660;
}

body.dark-mode {
  .progress-card,
  .dhikr-card {
    background-color: #333;
  }

  .progress-header h3,
  .dhikr-header h3,
  .dhikr-text {
    color: #fff;
  }

  .progress-bar {
    background-color: #444;
  }

  .dhikr-count {
    color: #aaa;
  }

  .action-button {
    background-color: #A79277;
  }

  .action-button:hover {
    background-color: #8a7660;
  }
}

@media (max-width: 768px) {
  .adhkar-list {
    grid-template-columns: 1fr;
  }
}
</style> 