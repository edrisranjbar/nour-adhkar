<style scoped>
.container {
  position: unset;
  top: unset;
  transform: unset;
}

header {
  height: 80px;
}

section#morning {
  padding-bottom: 70px;
  display: flex;
  flex-direction: column;
  height: 100vh; /* Full viewport height */
  overflow: hidden; /* Prevent scrolling on the section itself */
}

.modal-container {
  flex: 1; /* Take up all available space between header and footer */
  overflow-y: auto;
  padding: 20px;
  height: calc(100vh - 80px - 70px); /* viewport height minus header and footer heights */
  display: flex;
  flex-direction: column;
}

@media (max-width: 767px) {
  .bottom-nav-bar {
    z-index: 999;
    position: relative;
  }
}

.toast-notification {
  position: fixed;
  top: 20px;
  left: 50%;
  transform: translateX(-50%);
  background-color: var(--brand-secondary);
  color: var(--text-light);
  padding: 12px 20px;
  border-radius: 8px;
  z-index: 1100;
  box-shadow: 0 4px 12px var(--ui-shadow-light);
  text-align: center;
  font-weight: 500;
  animation: fadeInOut 3s ease-in-out forwards;
}

.share-button {
  cursor: pointer;
}

@keyframes fadeInOut {
  0% { opacity: 0; transform: translate(-50%, -20px); }
  10% { opacity: 1; transform: translate(-50%, 0); }
  90% { opacity: 1; transform: translate(-50%, 0); }
  100% { opacity: 0; transform: translate(-50%, -20px); }
}
</style>
<template>

  <CongratsModal v-if="showCongratsModal"/>

  <div class="toast-notification" v-if="showToast">
    <p>{{ toastMessage }}</p>
  </div>

  <section id="morning" class="modal" v-if="!showCongratsModal">

    <header>
      <div class="d-flex">
        <RouterLink to="/" class="d-flex align-items-center">
          <img class="appbar-action-button" src="@/assets/icons/back-button.svg" alt="برگشتن">
        </RouterLink>
        <h1 id="modal-title">{{ title }}</h1>
      </div>
      <span class="progressbar-container" v-if="openedCollection.adhkar.length > 1">
        <span class="progressbar-fill" :style="{ width: totalProgress + '%' }"></span>
      </span>
    </header>

    <main class="modal-container" @click="handleDhikrBodyClick">
      <div class="content-top-bar">
        <h2 id="dhikr-title"></h2>
        <img id="share-button" class="share-button" src="@/assets/icons/share.svg" alt="اشتراک گذاری" @click="share()">
      </div>
      <p id="dhikr-prefix">{{ openedDhikr.prefix }}</p>
      <p id="dhikr-text">{{ openedDhikr.text }}</p>
      <hr>
      <p id="dhikr-translation">{{ openedDhikr.translation }}</p>
    </main>

    <footer class="bottom-nav-bar">
      <span id="dhikr-count">{{ openedDhikr.count }} مرتبه</span>
      <span class="counter-button" @click="count()">{{ counter }}</span>
      <span id="dhikr-progress-details">{{ counter }} از {{ openedDhikr.count }} مرتبه</span>
    </footer>

  </section>
</template>

<script>
import { mapState, mapGetters } from 'vuex';
import axios from 'axios';
import CongratsModal from "@/components/Congrats.vue";
import Collection from "@/models/collection.js";
import tapSound from "@/assets/audios/click.mp3"
import { BASE_API_URL } from '@/config';

export default {
  components: {
    CongratsModal
  },
  props: {
    title: String,
    openedCollection: Collection
  },
  data() {
    return {
      counters: {}, // Object to store counter for each dhikr by its text (used as key)
      dhikrIndex: 0,
      openedDhikr: this.openedCollection.adhkar[0],
      tapSoundAudioPath: tapSound,
      touchstartX: 0,
      touchendX: 0,
      touchstartY: 0,
      touchendY: 0,
      showToast: false,
      toastMessage: ''
    }
  },
  watch: {
    '$route'() {
      // Reset to first dhikr when route changes
      this.dhikrIndex = 0;
      this.openedDhikr = this.openedCollection.adhkar[0];
      // Initialize counters for the new collection
      this.initializeCounters();
    }
  },
  computed: {
    ...mapState(['user']),
    ...mapGetters(['isAuthenticated']),
    counter() {
      // Get counter for current dhikr, default to 0 if not set
      return this.counters[this.openedDhikr.text] || 0;
    },
    isFirstDhikr() {
      return this.openedCollection.adhkar[0].text === this.openedDhikr.text;
    },
    isThereANextDhikr() {
      return this.openedCollection.adhkar[this.dhikrIndex + 1] !== undefined;
    },
    totalProgress() {
      const total = this.openedCollection.adhkar.length;
      const currentDhikrIndex = this.openedCollection.adhkar.findIndex(
        (element) => element.text === this.openedDhikr.text
      ) + 1;
      return Math.max((currentDhikrIndex / total) * 100, 5);
    },
    showCongratsModal() {
      const result = !this.isThereANextDhikr && this.counter == this.openedDhikr.count;
      if (result) {
        this.updateHeartScore();
        this.storeDhikr();
      }
      return result;
    }
  },
  methods: {
    initializeCounters() {
      // Create an object with a counter for each dhikr in the collection
      const newCounters = {};
      this.openedCollection.adhkar.forEach(dhikr => {
        newCounters[dhikr.text] = this.counters[dhikr.text] || 0;
      });
      this.counters = newCounters;
    },
    count(event) {
      if (event && event.target.id === 'share-button') {
        return;
      }
      
      const currentCount = this.counters[this.openedDhikr.text] || 0;
      if (currentCount >= this.openedDhikr.count) {
        return;
      }
      
      // Update the counter for the current dhikr
      this.counters[this.openedDhikr.text] = currentCount + 1;
      
      if (currentCount + 1 >= this.openedDhikr.count && this.isThereANextDhikr) {
        this.gotoNextDhikr();
      }
      this.playAudio(this.tapSoundAudioPath);
    },
    gotoPrevDhikr() {
      if (!this.isFirstDhikr) {
        this.openedDhikr = this.openedCollection.adhkar[--this.dhikrIndex];
      }
    },
    gotoNextDhikr() {
      if (this.isThereANextDhikr) {
        try {
          if ("vibrate" in navigator) this.vibrate();
        } catch {
          console.error('Cannot vibrate!');
        }
        this.openedDhikr = this.openedCollection.adhkar[++this.dhikrIndex];
      }
    },
    async updateHeartScore() {
      if (!this.isAuthenticated) {
        return;
      }
      let heart_score = Math.min(this.$store.state.user.heart_score + 10, 100);
      try {
        const response = await axios.patch(`${BASE_API_URL}/user/heart`, {
          heart_score: heart_score,
        }, {
          headers: {
            Authorization: `Bearer ${this.$store.state.token}`
          }
        });

        if (response.data.success) {
          this.$store.commit('updateHeartScore', heart_score);
          if (response.data.user) {
            this.$store.commit('setUser', response.data.user);
          }
        }

      } catch (error) {
        console.error('Error updating heart score:', error);
      }
    },
    async storeDhikr() {
      if (!this.isAuthenticated) return;
      
      try {
        const response = await axios.post(`${BASE_API_URL}/dhikr`, {}, {
          headers: {
            Authorization: `Bearer ${this.$store.state.token}`
          }
        });

        if (response.data.success) {
          this.$store.commit('setUser', response.data.user);
        }
      } catch (error) {
        console.error('Error storing dhikr:', error);
      }
    },
    share() {
      this.copyDhikr(this.openedDhikr);
    },
    async copyDhikr(openedDhikr) {
      const text = `${openedDhikr.name}\n
      ${openedDhikr.prefix}\n
      ${openedDhikr.text}\n
      ${openedDhikr.translation}\n
      منبع: ${window.location.href}`;

      try {
        await navigator.clipboard.writeText(text);
        console.log('Dhikr copied!');
        this.showToastMessage('متن ذکر کپی شد');
      } catch (err) {
        console.error('Failed to copy:', err);
        this.showToastMessage('خطا در کپی کردن متن');
      }
    },
    vibrate() {
      window.navigator.vibrate([200]);
    },
    playAudio(audioPath) {
      const audio = new Audio(audioPath);
      audio.play().catch(e => console.error('Audio play failed:', e));
    },
    handleKeydown(event) {
      if (event.code === 'Space') {
        this.count();
      } else if (event.code === 'Comma' || event.code === 'ArrowLeft') {
        this.gotoPrevDhikr();
      } else if (event.code === 'Period' || event.code === 'ArrowRight') {
        this.gotoNextDhikr();
      }
    },
    handleDhikrBodyClick(event) {
      if (event.target.id !== 'share-button') {
        this.count();
      }
    },
    handleTouchStart(event) {
      this.touchstartX = event.changedTouches[0].screenX;
      this.touchstartY = event.changedTouches[0].screenY;
    },
    handleTouchEnd(event) {
      this.touchendX = event.changedTouches[0].screenX;
      this.touchendY = event.changedTouches[0].screenY;
      this.checkDirection();
    },
    checkDirection() {
      const swipeThreshold = 30;
      const verticalThreshold = 50;

      const diffX = this.touchendX - this.touchstartX;
      const diffY = this.touchendY - this.touchstartY;

      if (Math.abs(diffX) > swipeThreshold && Math.abs(diffY) < verticalThreshold) {
        if (diffX < 0) {
          this.gotoPrevDhikr();
        } else if (diffX > 0) {
          this.gotoNextDhikr();
        }
      }
    },
    showToastMessage(message) {
      this.toastMessage = message;
      this.showToast = true;
      setTimeout(() => {
        this.showToast = false;
      }, 3000);
    }
  },
  mounted() {
    this.initializeCounters();
    window.addEventListener('keydown', this.handleKeydown);
    document.addEventListener('touchstart', this.handleTouchStart);
    document.addEventListener('touchend', this.handleTouchEnd);
  },
  beforeUnmount() {
    window.removeEventListener('keydown', this.handleKeydown);
    document.removeEventListener('touchstart', this.handleTouchStart);
    document.removeEventListener('touchend', this.handleTouchEnd);
  }
}
</script>