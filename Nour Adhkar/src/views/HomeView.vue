<template>
  <Header title="اذکار نور" description="پلتفرم فارسی اذکار و ادعیه اسلامی" />

  <main class="container">

    <RouterLink to="morning">
      <CategoryCard image-src="src/assets/images/morning.png">
        <h2 class="card-text-right">اذکار صبحگاه</h2>
      </CategoryCard>
    </RouterLink>

    <RouterLink to="night">
      <CategoryCard image-src="src/assets/images/night.png">
        <h2 class="card-text-left">اذکار شامگاه</h2>
      </CategoryCard>
    </RouterLink>

    <section class="row">
      <CategoryCard
          image-src="src/assets/images/sleep.jpg"
          size="small"
      >
        <h2 class="card-text-top card-text-right">دعاء قبل خواب</h2>
      </CategoryCard>
      <RouterLink to="istikhara" class="card-sm">
        <CategoryCard image-src="src/assets/images/prayer.png">
          <h2 class="cart-text-bottom card-text-left">دعاء استخاره</h2>
        </CategoryCard>
      </RouterLink>
    </section>

  </main>

  <Footer />
</template>

<script>
import Header from '@/components/Header.vue';
import Footer from '@/components/Footer.vue';
import CategoryCard from "@/components/CategoryCard.vue";

export default {
  components: {
    Header,
    Footer,
    CategoryCard
  }
}
</script>