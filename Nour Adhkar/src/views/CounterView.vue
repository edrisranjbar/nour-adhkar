<style scoped>
.counter-aya {
  font-weight: 100;
  font-size: 20px;
  text-align: center;
  margin-top: 99px;
  color: #000000;
}

.counter-action-buttons {
  background-color: transparent;
  padding: 4px 16px;
  color: #2F2F2F;
  font-weight: 400;
  font-size: 20px;
  border-radius: 16px;
  margin-top: 32px;
  margin-left: 8px;
  border: 2px solid #9C8466;
  text-decoration: none;
  cursor: pointer;
}

</style>
<template>

  <header>
    <div class="d-flex">
      <RouterLink to="/" class="d-flex">
        <img class="appbar-action-button" src="@/assets/icons/back-button.svg" alt="برگشتن">
      </RouterLink>
      <h1 id="modal-title">ذکر شمار</h1>
    </div>
  </header>

  <p class="counter-aya">
    « الَّذِينَ آمَنُوا وَتَطْمَئِنُّ قُلُوبُهُمْ بِذِكْرِ اللَّهِ
    <br>
    أَلَا بِذِكْرِ اللَّهِ تَطْمَئِنُّ الْقُلُوبُ »
  </p>

  <CounterButton ref="counterButton" />

  <div class="row justify-content-center">

    <span class="counter-action-buttons" @click="this.$refs.counterButton.counter=0">
      شروع دوباره
    </span>

    <RouterLink to="/" class="counter-action-buttons">
      پایان
    </RouterLink>

  </div>

  <br>
  <Footer />


</template>

<script>
import CounterButton from '@/components/CounterButton.vue';
import Footer from '@/components/Footer.vue';


export default {
  components: {
    CounterButton,
    Footer,
  },
}
</script>