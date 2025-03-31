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
      <!-- Day Labels -->
      <div v-for="day in weekDays" :key="day" class="day-label">
        {{ day }}
      </div>

      <!-- Calendar Days -->
      <div v-for="day in calendarDays" :key="day.date" class="calendar-day" :class="{
        'completed': day.completed,
        'today': day.isToday,
        'future': day.isFuture
      }" :title="day.persianDate">
        <span class="day-number">{{ day.dayNumber }}</span>
        <div class="completion-indicator"></div>
      </div>
    </div>
  </div>
</template>

<script>
import jalaali from 'jalaali-js';

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
      calendarDays: [],
      weekDays: ['شنبه', 'یکشنبه', 'دوشنبه', 'سه‌شنبه', 'چهارشنبه', 'پنج‌شنبه', 'جمعه']
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
    toJalaali(date) {
      return jalaali.toJalaali(date.getFullYear(), date.getMonth() + 1, date.getDate());
    },
    formatJalaaliDate(jd) {
      return `${jd.jd}/${jd.jm}/${jd.jy}`;
    },
    generateCalendarDays() {
      const today = new Date();

      // Get the last 30 days for the calendar
      const days = [];
      for (let i = 29; i >= 0; i--) {
        const date = new Date(today);
        date.setDate(date.getDate() - i);

        const jd = this.toJalaali(date);
        const isToday = date.toDateString() === today.toDateString();
        const isFuture = date > today;

        // Check if this day has completed dhikrs
        const completed = this.user?.completed_dates?.includes(
          date.toISOString().split('T')[0]
        ) || false;

        days.push({
          date: date.toISOString().split('T')[0],
          persianDate: this.formatJalaaliDate(jd),
          dayNumber: jd.jd,
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

.current-streak,
.total-days {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.streak-count,
.days-count {
  font-size: 1.5rem;
  font-weight: bold;
  color: var(--primary-color);
}

.streak-label,
.days-label {
  font-size: 0.8rem;
  color: var(--text-secondary);
}

.calendar-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 8px;
}

.day-label {
  text-align: center;
  font-size: 0.9rem;
  color: var(--text-secondary);
  padding: 8px 0;
  font-weight: 500;
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

  .day-label {
    font-size: 0.8rem;
    padding: 4px 0;
  }
}
</style>