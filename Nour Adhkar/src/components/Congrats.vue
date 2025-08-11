<style scoped>
a {
    text-decoration: none;
}

#congrats-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.35);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 90;
}

#congrats-card {
    position: relative;
    width: min(100%, 520px);
    max-width: calc(100vw - 24px);
    background-color: rgba(255, 255, 255, 0.95);
    border-radius: 14px;
    box-shadow: 0 10px 28px rgba(0, 0, 0, 0.22);
    padding: 48px 16px 16px 16px;
    text-align: center;
    overflow: visible;
}

body.dark-mode #congrats-card {
    background-color: rgba(15, 23, 42, 0.95);
    box-shadow: 0 12px 36px rgba(0, 0, 0, 0.6);
}

.balloon {
    width: 80px;
    height: 80px;
    position: absolute;
    margin: auto;
    top: -40px;
    left: 0;
    right: 0;
}

.card-title {
    font-size: 28px;
    font-weight: 900;
    color: #9C8466;
    margin-bottom: 6px;
}

body.dark-mode .card-title {
    color: #FDE68A;
}

.card-description {
    font-size: 16px;
    font-weight: 400;
    color: #1E1E1E;
    margin-bottom: 16px;
}

body.dark-mode .card-description {
    color: #E5E7EB;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
    margin: 8px 0 18px 0;
}

.stat-card {
    background: rgba(240, 240, 240, 0.75);
    border-radius: 10px;
    padding: 12px 10px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

body.dark-mode .stat-card {
    background: rgba(255, 255, 255, 0.06);
}

.stat-icon {
    font-size: 16px;
    color: #9C8466;
    margin-bottom: 6px;
}

body.dark-mode .stat-icon {
    color: #FDE68A;
}

.stat-value {
    font-size: 22px;
    font-weight: 800;
    color: #2c2a2a;
}

body.dark-mode .stat-value { color: #fff; }

.stat-label {
    font-size: 11px;
    color: #666;
    margin-top: 2px;
}

body.dark-mode .stat-label { color: #c7c7c7; }

.actions {
    margin-top: 8px;
    display: grid;
    grid-template-columns: 1fr;
    gap: 10px;
}

.button-primary-outline {
    font-size: 16px;
    font-weight: 700;
    color: #2F2F2F;
    display: flex;
    justify-content: center;
    align-items: center;
    border: 2px solid #9C8466;
    border-radius: 10px;
    height: 54px;
    width: 100%;
    background: transparent;
}

body.dark-mode .button-primary-outline {
    color: #F3F4F6;
    border-color: #FDE68A;
}

.button-primary {
    font-size: 16px;
    font-weight: 800;
    color: #F0E9DF;
    display: flex;
    justify-content: center;
    align-items: center;
    background: linear-gradient(135deg, #A79277, #9C8466);
    border-radius: 12px;
    height: 54px;
    width: 100%;
}

body.dark-mode .button-primary {
    background: linear-gradient(135deg, #926D47, #8a633d);
}

/* Transitions */
.modal-fade-enter-active, .modal-fade-leave-active {
    transition: opacity .25s ease;
}
.modal-fade-enter-from, .modal-fade-leave-to { opacity: 0; }

.pop-enter-active, .pop-leave-active { transition: all .25s ease; }
.pop-enter-from { opacity: 0; transform: translateY(8px) scale(0.98); }
.pop-leave-to { opacity: 0; transform: translateY(-6px) scale(1.02); }

.shine {
    position: absolute;
    inset: 0;
    pointer-events: none;
    border-radius: inherit;
    background: radial-gradient( circle at 30% -20%, rgba(255,255,255,0.35), transparent 40%),
                radial-gradient( circle at 70% -10%, rgba(255,255,255,0.2), transparent 45%);
}

@media (min-width: 680px) {
    .stats-grid { grid-template-columns: repeat(4, 1fr); }
}
</style>

<template>
  <transition name="modal-fade">
    <div id="congrats-overlay">
      <section id="congrats-card">
        <div class="shine" />
        <img src="@/assets/images/balloon.png" class="balloon" alt="تبریک پایان ذکر">
        <h1 class="card-title">بارک الله فیکم</h1>
        <p class="card-description">
          خیلی هم عالی👍 یاد الله آرام‌بخش دل‌هاست. امروز فوق‌العاده بود!
        </p>

        <transition-group name="pop" tag="div" class="stats-grid">
          <div class="stat-card" key="streak">
            <div class="stat-icon">
              <font-awesome-icon icon="fa-fire" />
            </div>
            <div class="stat-value">{{ animated.streak }}</div>
            <div class="stat-label">روزهای پیاپی</div>
          </div>

          <div class="stat-card" key="today">
            <div class="stat-icon">
              <font-awesome-icon icon="fa-calendar-check" />
            </div>
            <div class="stat-value">{{ animated.today }}</div>
            <div class="stat-label">ذکر امروز</div>
          </div>

          <div class="stat-card" key="total">
            <div class="stat-icon">
              <font-awesome-icon icon="fa-pray" />
            </div>
            <div class="stat-value">{{ animated.total }}</div>
            <div class="stat-label">کل اذکار</div>
          </div>

          <div class="stat-card" key="heart">
            <div class="stat-icon">
              <font-awesome-icon icon="fa-heart" />
            </div>
            <div class="stat-value">{{ animated.heart }}</div>
            <div class="stat-label">امتیاز قلب</div>
          </div>
        </transition-group>

        <div class="actions">
          <RouterLink to="/counter">
            <div class="button-primary-outline">
              ادامه ذکر گفتن
            </div>
          </RouterLink>
          <RouterLink to="/">
            <div class="button-primary">
              رفتن به صفحه اصلی
            </div>
          </RouterLink>
        </div>
      </section>
    </div>
  </transition>
</template>

<script>
export default {
  name: 'CongratsModal',
  data() {
    return {
      animated: {
        streak: 0,
        today: 0,
        total: 0,
        heart: 0
      },
      animationFrame: null
    };
  },
  computed: {
    user() {
      return this.$store.state.user || {};
    },
    targets() {
      return {
        streak: Number(this.user.streak || 0),
        today: Number(this.user.today_count || 0),
        total: Number(this.user.total_dhikrs || 0),
        heart: Number(this.user.heart_score || 0)
      };
    }
  },
  watch: {
    targets: {
      deep: true,
      handler() {
        this.startAnimation();
      }
    }
  },
  mounted() {
    this.startAnimation();
    if (this.$store.state && this.$store.state.token) {
      this.$store.dispatch('fetchUserStats').catch(() => {});
    }
  },
  beforeUnmount() {
    if (this.animationFrame) cancelAnimationFrame(this.animationFrame);
  },
  methods: {
    startAnimation() {
      const duration = 650;
      const start = performance.now();
      const from = { ...this.animated };
      const to = { ...this.targets };

      const easeOutCubic = (t) => 1 - Math.pow(1 - t, 3);
      const step = (now) => {
        const progress = Math.min(1, (now - start) / duration);
        const eased = easeOutCubic(progress);
        this.animated.streak = Math.round(from.streak + (to.streak - from.streak) * eased);
        this.animated.today = Math.round(from.today + (to.today - from.today) * eased);
        this.animated.total = Math.round(from.total + (to.total - from.total) * eased);
        this.animated.heart = Math.round(from.heart + (to.heart - from.heart) * eased);
        if (progress < 1) {
          this.animationFrame = requestAnimationFrame(step);
        }
      };
      if (this.animationFrame) cancelAnimationFrame(this.animationFrame);
      this.animationFrame = requestAnimationFrame(step);
    }
  }
}
</script>