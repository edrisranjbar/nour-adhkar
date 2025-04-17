<template>
  <div class="not-found">
    <div class="not-found-content">
      <h1>404</h1>
      <h2>صفحه مورد نظر پیدا نشد</h2>
      <p>متأسفانه صفحه‌ای که به دنبال آن هستید وجود ندارد یا به آدرس دیگری منتقل شده است.</p>
      <RouterLink to="/" class="home-button">
        <font-awesome-icon icon="fa-solid fa-home" />
        بازگشت به صفحه اصلی
      </RouterLink>
    </div>
  </div>
</template>

<script>
export default {
  name: 'NotFoundView',
  metaInfo: {
    title: 'صفحه یافت نشد | اذکار نور',
    meta: [
      { name: 'robots', content: 'noindex, nofollow' }
    ]
  }
}
</script>

<style scoped>
.not-found {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  padding: 2rem;
  text-align: center;
}

.not-found-content {
  max-width: 600px;
}

h1 {
  font-size: 8rem;
  margin: 0;
  color: #A79277;
  line-height: 1;
  opacity: 0.8;
}

h2 {
  font-size: 2rem;
  margin: 0 0 1.5rem 0;
  color: #333;
}

p {
  margin: 0 0 2rem 0;
  font-size: 1.1rem;
  color: #666;
  line-height: 1.6;
}

.home-button {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  background-color: #A79277;
  color: white;
  border-radius: 8px;
  text-decoration: none;
  font-size: 1rem;
  transition: background-color 0.2s ease;
}

.home-button:hover {
  background-color: #8a7660;
}

/* Dark mode styles */
body.dark-mode {
  .not-found {
    background-color: #222;
  }

  h2 {
    color: #eee;
  }

  p {
    color: #bbb;
  }
}
</style> 