<template>
  <div class="verse-container">
    <div class="verse-card">
      <div class="verse-content">
        <div class="verse-header">
          <font-awesome-icon icon="fa-solid fa-book-quran" class="verse-icon" />
          <h3>{{ title }}</h3>
        </div>
        <div class="verse-arabic">{{ dailyVerse.arabic }}</div>
        <div class="verse-translation">{{ dailyVerse.translation }}</div>
        <div class="verse-reference">{{ dailyVerse.reference }}</div>
      </div>
    </div>
  </div>
</template>

<script>
import { quranVerses } from '@/assets/js/collections/quran';

export default {
  name: "DailyVerse",
  props: {
    title: {
      type: String,
      default: "آیه روزانه"
    }
  },
  data() {
    return {
      dailyVerse: {
        arabic: "",
        translation: "",
        reference: ""
      }
    };
  },
  mounted() {
    this.selectDailyVerse();
  },
  methods: {
    selectDailyVerse() {
      // Get today's date as a string to use as a seed
      const today = new Date();
      const dateString = `${today.getFullYear()}-${today.getMonth() + 1}-${today.getDate()}`;
      
      // Simple hash function to get a consistent index for each day
      const hash = this.hashCode(dateString);
      const index = Math.abs(hash % quranVerses.length);
      
      this.dailyVerse = quranVerses[index];
    },
    hashCode(str) {
      let hash = 0;
      for (let i = 0; i < str.length; i++) {
        hash = ((hash << 5) - hash) + str.charCodeAt(i);
        hash |= 0; // Convert to 32bit integer
      }
      return hash;
    }
  }
};
</script>

<style scoped>
.verse-container {
  margin: 1.5rem 0;
}

.verse-card {
  background: linear-gradient(135deg, #A79277 0%, #9C8466 100%);
  border-radius: 16px;
  color: white;
  padding: 1.5rem;
  box-shadow: 0 6px 12px rgba(167, 146, 119, 0.2);
  position: relative;
  overflow: hidden;
}

.verse-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: url('src/assets/images/pattern.svg') no-repeat;
  background-size: cover;
  opacity: 0.05;
  z-index: 1;
}

.verse-content {
  position: relative;
  z-index: 2;
}

.verse-header {
  display: flex;
  align-items: center;
  margin-bottom: 1rem;
}

.verse-icon {
  font-size: 1.5rem;
  margin-left: 0.5rem;
}

.verse-header h3 {
  margin: 0;
  font-size: 1.4rem;
}

.verse-arabic {
  font-size: 1.5rem;
  line-height: 2;
  margin-bottom: 1rem;
  text-align: center;
  direction: rtl;
}

.verse-translation {
  font-size: 1.1rem;
  line-height: 1.8;
  margin-bottom: 1rem;
  text-align: center;
  direction: rtl;
}

.verse-reference {
  font-size: 0.9rem;
  opacity: 0.9;
  text-align: left;
  padding-top: 0.5rem;
  border-top: 1px solid rgba(255, 255, 255, 0.2);
}

@media (max-width: 768px) {
  .verse-arabic {
    font-size: 1.3rem;
  }
  
  .verse-translation {
    font-size: 1rem;
  }
}

@media (max-width: 480px) {
  .verse-card {
    padding: 1.2rem;
  }
  
  .verse-arabic {
    font-size: 1.2rem;
  }
  
  .verse-translation {
    font-size: 0.9rem;
  }
  
  .verse-header h3 {
    font-size: 1.2rem;
  }
}
</style> 