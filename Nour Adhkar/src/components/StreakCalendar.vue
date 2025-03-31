<template>
  <div class="streak-calendar">
    <div class="streak-header">
      <h3>تداوم در اذکار</h3>
      <div class="streak-stats">
        <div class="current-streak">
          <span class="streak-count">{{ currentStreak }}</span>
          <span class="streak-label">روز متوالی</span>
        </div>
        <div class="total-days">
          <span class="days-count">{{ totalDays }}</span>
          <span class="days-label">روز در این ماه</span>
        </div>
      </div>
    </div>
    
    <div class="calendar-grid">
      <div v-for="day in calendarDays" 
           :key="day.date" 
           class="calendar-day"
           :class="{
             'completed': day.completed,
             'today': day.isToday,
             'future': day.isFuture
           }"
           :title="day.date">
        <span class="day-number">{{ day.dayNumber }}</span>
        <div class="completion-indicator"></div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'StreakCalendar',
  props: {
    user: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
      currentDate: new Date(),
      calendarDays: []
    }
  },
  computed: {
    currentStreak() {
      return this.user?.streak || 0;
    },
    totalDays() {
      return this.calendarDays.filter(day => day.completed).length;
    }
  },
  methods: {
    generateCalendarDays() {
      const today = new Date();
      const currentMonth = today.getMonth();
      const currentYear = today.getFullYear();
      
      // Get the first day of the current month
      const firstDay = new Date(currentYear, currentMonth, 1);
      // Get the last day of the current month
      const lastDay = new Date(currentYear, currentMonth + 1, 0);
      
      // Get the last 30 days for the calendar
      const days = [];
      for (let i = 29; i >= 0; i--) {
        const date = new Date(today);
        date.setDate(date.getDate() - i);
        
        const isToday = date.toDateString() === today.toDateString();
        const isFuture = date > today;
        
        // Check if this day has completed dhikrs
        const completed = this.user?.completed_dates?.includes(
          date.toISOString().split('T')[0]
        ) || false;
        
        days.push({
          date: date.toISOString().split('T')[0],
          dayNumber: date.getDate(),
          completed,
          isToday,
          isFuture
        });
      }
      
      this.calendarDays = days;
    }
  },
  watch: {
    user: {
      handler() {
        this.generateCalendarDays();
      },
      immediate: true
    }
  }
}
</script>

<style scoped>
.streak-calendar {
  background: var(--surface-color);
  border-radius: 16px;
  padding: 20px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.streak-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.streak-header h3 {
  margin: 0;
  color: var(--text-color);
  font-size: 1.2rem;
}

.streak-stats {
  display: flex;
  gap: 20px;
}

.current-streak, .total-days {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.streak-count, .days-count {
  font-size: 1.5rem;
  font-weight: bold;
  color: var(--primary-color);
}

.streak-label, .days-label {
  font-size: 0.8rem;
  color: var(--text-secondary);
}

.calendar-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 8px;
}

.calendar-day {
  aspect-ratio: 1;
  position: relative;
  border-radius: 8px;
  background: var(--surface-color-secondary);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
}

.calendar-day.completed {
  background: var(--primary-color);
}

.calendar-day.today {
  border: 2px solid var(--primary-color);
}

.calendar-day.future {
  opacity: 0.5;
}

.day-number {
  font-size: 0.9rem;
  color: var(--text-color);
  margin-bottom: 4px;
}

.completion-indicator {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: var(--primary-color);
  opacity: 0;
  transition: opacity 0.3s ease;
}

.calendar-day.completed .completion-indicator {
  opacity: 1;
}

.calendar-day.completed .day-number {
  color: white;
}

@media (max-width: 480px) {
  .streak-calendar {
    padding: 15px;
  }
  
  .calendar-grid {
    gap: 4px;
  }
  
  .day-number {
    font-size: 0.8rem;
  }
}
</style> 