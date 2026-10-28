<script setup>
import { onMounted, ref } from 'vue'
import axios from 'axios'
import { BASE_API_URL } from '../config'

const releases = ref([])
const loading = ref(false)
const error = ref('')
const page = ref(0)
const lastPage = ref(1)

async function loadReleases() {
  if (loading.value) return
  loading.value = true
  error.value = ''
  try {
    const { data } = await axios.get(`${BASE_API_URL}/app-versions`, {
      params: { page: page.value + 1 }
    })
    releases.value.push(...data.data)
    page.value = data.current_page
    lastPage.value = data.last_page
  } catch {
    error.value = 'دریافت تاریخچهٔ به‌روزرسانی‌ها ممکن نشد. دوباره تلاش کنید.'
  } finally {
    loading.value = false
  }
}

function releaseDate(value) {
  const date = new Date(`${value}T12:00:00Z`)
  if (Number.isNaN(date.getTime())) return value
  return new Intl.DateTimeFormat('fa-IR', {
    year: 'numeric', month: 'long', day: 'numeric', timeZone: 'UTC'
  }).format(date)
}

function changelogLines(value) {
  return value.split(/\r?\n/).map(line => line.trim()).filter(Boolean)
}

onMounted(loadReleases)
</script>

<template>
  <main class="updates" dir="rtl" lang="fa">
    <div class="updates-wrap">
      <header class="updates-head">
        <router-link to="/" class="updates-brand">
          <img src="@/assets/icons/logo.png" alt="" width="44" height="44" />
          <span>اذکار نور</span>
        </router-link>
        <h1>تازه‌ها و به‌روزرسانی‌ها</h1>
        <p>تغییرات، بهبودها و رفع اشکال‌های نسخه‌های منتشرشدهٔ اذکار نور را اینجا دنبال کنید.</p>
      </header>

      <section :aria-busy="loading" aria-label="تاریخچهٔ نسخه‌ها">
        <article v-for="release in releases" :key="release.versionCode" class="release">
          <div class="release-head">
            <h2>نسخهٔ <bdi>{{ release.versionName }}</bdi></h2>
            <time :datetime="release.releaseDate">{{ releaseDate(release.releaseDate) }}</time>
          </div>
          <ul>
            <li v-for="(line, index) in changelogLines(release.changelog)" :key="index">{{ line }}</li>
          </ul>
        </article>

        <p v-if="loading" role="status" class="updates-state">در حال دریافت به‌روزرسانی‌ها…</p>
        <div v-else-if="error" role="alert" class="updates-state">
          <p>{{ error }}</p>
          <button type="button" @click="loadReleases">تلاش دوباره</button>
        </div>
        <p v-else-if="!releases.length" class="updates-state">هنوز به‌روزرسانی‌ای منتشر نشده است.</p>
        <button v-else-if="page < lastPage" type="button" class="load-more" @click="loadReleases">
          نمایش نسخه‌های قدیمی‌تر
        </button>
      </section>

      <footer><router-link to="/">بازگشت به صفحهٔ اصلی</router-link></footer>
    </div>
  </main>
</template>

<style scoped>
.updates { min-height: 100vh; background: #f7f6f1; color: #282c24; padding: 40px 20px; }
.updates-wrap { max-width: 800px; margin: auto; }
.updates-head { margin-bottom: 36px; }
.updates-brand { display: inline-flex; align-items: center; gap: 10px; font-weight: 700; text-decoration: none; }
.updates-brand img { border-radius: 12px; }
h1 { font-size: clamp(1.6rem, 5vw, 2.4rem); font-weight: 800; margin: 24px 0 12px; }
.updates-head p, time { color: #5c6455; }
.updates-head p, li { line-height: 2; }
.release { background: #fff; border: 1px solid #dee3d8; border-radius: 20px; padding: 24px; margin-bottom: 20px; }
.release-head { display: flex; align-items: baseline; justify-content: space-between; flex-wrap: wrap; gap: 12px; }
h2 { font-size: 1.2rem; font-weight: 700; margin: 0; }
time { font-size: 0.9rem; }
ul { padding-inline-start: 24px; margin: 20px 0 0; }
li { margin-bottom: 8px; white-space: pre-wrap; overflow-wrap: anywhere; }
button { background: #355f30; color: #fff; border: 0; border-radius: 12px; padding: 12px 20px; font: inherit; cursor: pointer; min-height: 44px; }
button:focus-visible, a:focus-visible { outline: 3px solid #91b680; outline-offset: 4px; }
.updates-state, footer { text-align: center; padding: 24px 0; }
.load-more { display: block; margin: 24px auto; }
a { color: #355f30; }
@media (max-width: 480px) { .release { padding: 20px 16px; } }
@media (prefers-color-scheme: dark) {
  .updates { background: #101610; color: #ecf0e6; }
  .release { background: #1b231b; border-color: #354032; }
  .updates-head p, time { color: #bbc6b3; }
  a { color: #b1d59e; }
}
</style>
