<template>
  <section class="inbox" dir="rtl">
    <h1>پیام‌های برنامه</h1>
    <p v-if="error" role="alert">{{ error }}</p>
    <div class="panel">
      <h2>ارسال اطلاعیه</h2>
      <form @submit.prevent="save">
        <label>عنوان <input v-model.trim="form.title" required maxlength="160" /></label>
        <label>متن پیام <textarea v-model.trim="form.message" required maxlength="5000" rows="5" /></label>
        <label class="check"><input v-model="form.published" type="checkbox" /> انتشار برای کاربران</label>
        <button :disabled="saving || !form.title || !form.message">{{ saving ? 'در حال ذخیره…' : 'ذخیره پیام' }}</button>
      </form>
    </div>
    <div class="panel">
      <h2>اطلاعیه‌ها</h2>
      <div v-for="notice in notices" :key="notice.id" class="entry">
        <strong>{{ notice.title }}</strong> <span>{{ notice.published ? 'منتشر شده' : 'پیش‌نویس' }}</span>
        <p>{{ notice.message }}</p>
        <button @click="toggle(notice)">{{ notice.published ? 'لغو انتشار' : 'انتشار' }}</button>
      </div>
      <p v-if="!notices.length">پیامی ثبت نشده است.</p>
    </div>
    <div class="panel">
      <h2>بازخورد کاربران</h2>
      <div v-for="item in feedback" :key="item.id" class="entry">
        <strong>{{ types[item.type] || item.type }}</strong> <small>{{ item.created_at }}</small>
        <p>{{ item.message }}</p>
      </div>
      <p v-if="!feedback.length">بازخوردی ثبت نشده است.</p>
    </div>
  </section>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const notices = ref([]);
const feedback = ref([]);
const error = ref('');
const saving = ref(false);
const form = ref({ title: '', message: '', published: true });
const types = { suggestion: 'پیشنهاد', criticism: 'انتقاد', other: 'سایر' };
async function load() {
  try {
    const [n, f] = await Promise.all([axios.get('admin/app-notices'), axios.get('admin/app-feedback')]);
    notices.value = n.data.data;
    feedback.value = f.data.data;
    error.value = '';
  } catch { error.value = 'دریافت پیام‌ها ناموفق بود.'; }
}
async function save() {
  saving.value = true;
  try {
    await axios.post('admin/app-notices', form.value);
    form.value = { title: '', message: '', published: true };
    await load();
  } catch { error.value = 'ذخیره پیام ناموفق بود.'; }
  finally { saving.value = false; }
}
async function toggle(notice) {
  try { await axios.put(`admin/app-notices/${notice.id}`, { published: !notice.published }); await load(); }
  catch { error.value = 'تغییر وضعیت ناموفق بود.'; }
}
onMounted(load);
</script>

<style scoped>
.inbox { color: var(--admin-text); max-width: 900px; padding: 24px; margin: auto; }
.panel { background: var(--admin-surface); border: 1px solid var(--admin-border); border-radius: 16px; padding: 20px; margin: 20px 0; }
label { display: block; margin: 14px 0; }
input:not([type=checkbox]), textarea { display: block; width: 100%; box-sizing: border-box; padding: 12px; margin-top: 7px; border-radius: 8px; border: 1px solid var(--admin-border); background: var(--admin-bg); color: var(--admin-text); font: inherit; }
.check { display: flex; align-items: center; gap: 8px; }
button { background: var(--admin-accent); color: white; border: 0; border-radius: 8px; padding: 10px 18px; cursor: pointer; }
button:disabled { opacity: .6; }
.entry { border-top: 1px solid var(--admin-border); padding: 14px 0; white-space: pre-wrap; overflow-wrap: anywhere; }
.entry span, .entry small { color: var(--admin-muted); margin-right: 10px; }
</style>
