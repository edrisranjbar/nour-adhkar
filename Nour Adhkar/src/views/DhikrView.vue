<style scoped>
.container {
  position: unset;
  top: unset;
  transform: unset;
}

header {
  height: 80px;
}
</style>
<template>

  <CongratsModal v-if="showCongratsModal"/>

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
import { mapState } from 'vuex';
import axios from 'axios';
import CongratsModal from "@/components/Congrats.vue";
import Collection from "@/models/collection.js";
import tapSound from "@/assets/audios/click.mp3"

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
      counter: 0,
      dhikrIndex: 0,
      openedDhikr: this.openedCollection.adhkar[0],
      tapSoundAudioPath: tapSound,
      touchstartX: 0,
      touchendX: 0,
      touchstartY: 0,
      touchendY: 0 // Added missing Y coordinates
    }
  },
  computed: {
    ...mapState(['user']),
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
      if (result) this.updateHeartScore();
      return result;
    }
  },
  methods: {
    count(event) {
      if (event && event.target.id === 'share-button') {
        return;
      }
      if (this.counter == this.openedDhikr.count) {
        return;
      }
      this.counter++;
      if (this.counter >= this.openedDhikr.count && this.isThereANextDhikr) {
        this.gotoNextDhikr();
      }
      this.playAudio(this.tapSoundAudioPath);
    },
    gotoPrevDhikr() {
      if (!this.isFirstDhikr) {
        this.counter = 0;
        this.openedDhikr = this.openedCollection.adhkar[--this.dhikrIndex];
      }
    },
    gotoNextDhikr() {
      if (this.isThereANextDhikr) {
        this.counter = 0;
        try {
          if ("vibrate" in navigator) this.vibrate();
        } catch {
          console.error('Cannot vibrate!');
        }
        this.openedDhikr = this.openedCollection.adhkar[++this.dhikrIndex];
      }
    },
    async updateHeartScore() {
      try {
        const response = await axios.patch('http://localhost:8000/api/user/heart', {
          score: this.$store.state.user.heart_score + 10,
        }, {
          headers: {
            Authorization: `Bearer ${this.$store.state.token}`
          }
        });

        // Update local state
        if (response.data.success) {
          this.$store.commit('updateHeartScore', response.data.newScore);
        }

      } catch (error) {
        console.error('Error updating heart score:', error);
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
        // Consider adding toast/notification here
      } catch (err) {
        console.error('Failed to copy:', err);
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
    }
  },
  mounted() {
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