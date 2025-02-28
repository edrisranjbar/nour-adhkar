<style scoped>
.container {
  position: unset;
  top: unset;
  transform: unset;
}
header { height: 80px; }
</style>
<template>

  <CongratsModal v-if="showCongratsModal" />

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
  computed: {
    isFirstDhikr: function () {
      return this.openedCollection.adhkar[0].text === this.openedDhikr.text;
    },
    isThereANextDhikr: function () {
      return this.openedCollection.adhkar[this.dhikrIndex + 1] !== undefined;
    },
    totalProgress: function () {
      const total = this.openedCollection.adhkar.length;
      const currentDhikrIndex = this.openedCollection.adhkar.findIndex(
          (element) => element.text === this.openedDhikr.text
      ) + 1;
      return Math.max((currentDhikrIndex / total) * 100, 5);
    },
    showCongratsModal: function () {
      return !this.isThereANextDhikr && this.counter == this.openedDhikr.count;
    }
  },
  methods: {
    count: function (event) {
      if (event && event.target.id === 'share-button') {
        return
      }
      if (this.counter == this.openedDhikr.count) { return }
      this.counter++;
      if (this.counter >= this.openedDhikr.count && this.isThereANextDhikr) {
        this.gotoNextDhikr();
      }
      this.playAudio(this.tapSoundAudioPath);
    },
    gotoPrevDhikr: function () {
      if (!this.isFirstDhikr) {
        this.counter = 0;
        this.openedDhikr = this.openedCollection.adhkar[--this.dhikrIndex];
      }
    },
    gotoNextDhikr: function () {
      if (this.isThereANextDhikr) {
        this.counter = 0;
        try {
          if ("vibrate" in navigator) this.vibrate();
        }
        catch {
          console.error('Can not vibrate!');
        }
        this.openedDhikr = this.openedCollection.adhkar[++this.dhikrIndex];
      }
    },
    share: function () {
      this.copyDhikr(this.openedDhikr);
    },
    copyDhikr: function async(openedDhikr) {
      const text = `${openedDhikr.name}\n
    ${openedDhikr.prefix}\n
    ${openedDhikr.text}\n
    ${openedDhikr.translation}\n
    منبع: ${window.location.href}
    `;
      navigator.clipboard.writeText(text).then(() => console.log('Dhikr copied!'));
      // todo: show a success snackbar to say this dhikr has been copied.
    },
    vibrate: function () {
      window.navigator.vibrate([200]);
    },
    playAudio: function (audioPath) {
      const audio = new Audio(audioPath);
      audio.play();
    },
    handleKeydown: function (event) {
      if (event.code === 'Space') {
        this.count();
      } else if (event.code === 'Comma' || event.code === 'ArrowLeft') {
        this.gotoNextDhikr();
      } else if (event.code === 'Period' || event.code === 'ArrowRight') {
        this.gotoPrevDhikr();
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
      const swipeThreshold = 30; // Minimum distance (in pixels) for a swipe to be considered valid
      const verticalThreshold = 50; // Maximum vertical movement allowed

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
  },
  data() {
    return {
      counter: 0,
      dhikrIndex: 0,
      openedDhikr: this.openedCollection.adhkar[0],
      tapSoundAudioPath: tapSound,
      touchstartX: 0,
      touchendX: 0,
    }
  },
  mounted() {
    window.addEventListener('keydown', this.handleKeydown);
    document.addEventListener('touchstart', (e) => this.handleTouchStart(e));
    document.addEventListener('touchend', e => this.handleTouchEnd(e));
  },
  beforeUnmount() {
    window.removeEventListener('keydown', this.handleKeydown);
    document.removeEventListener('touchstart', (e) => this.handleTouchStart(e));
    document.removeEventListener('touchend', (e) => this.handleTouchEnd(e));
  },
}
</script>