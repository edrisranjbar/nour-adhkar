<template>
  <section class="social" dir="rtl">
    <div class="page-heading"><div><span class="eyebrow">اذکار نور · محتوای اجتماعی</span><h1>صف اینستاگرام</h1><p>تصویر و کپشن را آماده کنید؛ سپس انتشار دستی یا ارسال زمان‌بندی‌شده را انتخاب کنید.</p></div><div class="actions"><button :disabled="loading || loadFailed" :aria-expanded="settingsOpen" @click="settingsOpen = !settingsOpen">تنظیمات قالب‌ها</button><button :disabled="busy || loading" @click="load">تازه‌سازی</button></div></div>
    <p v-if="error" class="notice error" role="alert">{{ error }}</p>
    <p v-if="message" class="notice" role="status">{{ message }}</p>
    <div class="stats"><div v-for="(label, key) in labels" :key="key"><strong>{{ digits(posts.filter(p => p.status === key).length) }}</strong><span>{{ label }}</span></div></div>
    <p v-if="loading" class="empty" role="status">در حال دریافت صف…</p>
    <div v-else-if="loadFailed" class="empty"><h2>صف دریافت نشد</h2><p>اتصال را بررسی کنید و دوباره تلاش کنید.</p><button @click="load">تلاش دوباره</button></div>
    <template v-else>
      <section v-if="settingsOpen" class="panel template-settings" aria-labelledby="template-heading">
        <div class="settings-heading"><div><h2 id="template-heading">طراحی قالب‌ها</h2><p>رنگ، قلم و چیدمان را تغییر دهید؛ سپس قالب را ذخیره و برای هر پست انتخاب کنید.</p></div><button :disabled="busy" @click="newTemplate">ساخت قالب جدید</button></div>
        <div class="template-list" aria-label="قالب‌های ذخیره‌شده"><button v-for="template in templates" :key="template.id" :aria-pressed="editingTemplateId === template.id" :disabled="busy" @click="editTemplate(template)"><span class="swatch" :style="{ background: template.design.background, color: template.design.text }">آ</span>{{ template.name }}<small v-if="template.is_default">پیش‌فرض</small></button></div>
        <div class="settings-grid">
          <form class="template-form" @submit.prevent="saveTemplate">
            <label>نام قالب<input v-model.trim="templateForm.name" required maxlength="80" :disabled="busy" /></label>
            <div class="color-grid"><label>رنگ پس‌زمینه<input v-model="templateForm.design.background" type="color" aria-label="رنگ پس‌زمینه" :disabled="busy" /><span class="color-value">{{ templateForm.design.background }}</span></label><label>رنگ متن<input v-model="templateForm.design.text" type="color" aria-label="رنگ متن" :disabled="busy" /><span class="color-value">{{ templateForm.design.text }}</span></label><label>رنگ متن فرعی<input v-model="templateForm.design.muted" type="color" aria-label="رنگ متن فرعی" :disabled="busy" /><span class="color-value">{{ templateForm.design.muted }}</span></label></div>
            <div class="field-grid"><label>قلم<select aria-label="قلم" v-model="templateForm.design.font" :disabled="busy"><option v-for="font in fontChoices" :key="font.value" :value="font.value">{{ font.label }}</option></select></label><label>چیدمان<select aria-label="چیدمان" v-model="templateForm.design.layout" :disabled="busy"><option value="centered">متن در مرکز</option><option value="upper">متن در نیمه بالا</option><option value="framed">مرکز با قاب ظریف</option></select></label><label>اندازه متن<input v-model.number="templateForm.design.font_size" type="number" min="36" max="84" required :disabled="busy" /></label><label>فاصله خطوط<input v-model.number="templateForm.design.line_height" type="number" min="1.4" max="2.2" step="0.05" required :disabled="busy" /></label><label>حاشیه طرفین<input v-model.number="templateForm.design.margin" type="number" min="80" max="180" required :disabled="busy" /></label><label>اندازه لوگو<input v-model.number="templateForm.design.logo_size" type="number" min="48" max="120" required :disabled="busy" /></label></div>
            <p class="spec">اندازه‌ها برحسب پیکسل در تصویر خروجی‌اند؛ متن بلند برای جا شدن در تصویر کوچک‌تر می‌شود.</p>
            <p v-if="lowContrast" class="contrast-warning" role="status">کنتراست متن و پس‌زمینه کم است؛ رنگ خواناتری انتخاب کنید.</p>
            <label class="default-check"><input v-model="templateForm.is_default" type="checkbox" :disabled="busy || !!templates.find(t => t.id === editingTemplateId && t.is_default)" />قالب پیش‌فرض برای پست‌های جدید</label>
            <div class="actions"><button :disabled="busy || !templateForm.name || !validTemplateDesign">{{ busy ? 'در حال ذخیره…' : 'ذخیره قالب' }}</button><button type="button" :disabled="busy" @click="duplicateTemplate">کپی به قالب جدید</button><button v-if="editingTemplateId" type="button" :disabled="busy || templateForm.is_default" @click="deleteTemplate">حذف قالب</button></div>
          </form>
          <div class="template-proof"><div class="preview"><img v-if="templatePreviewUrl" :src="templatePreviewUrl" alt="پیش‌نمایش قالب" /><div v-else class="preview-loading" role="status">{{ templatePreviewError ? 'تنظیمات قالب را بررسی کنید' : 'در حال ساخت پیش‌نمایش…' }}</div></div><p class="spec">پیش‌نمایش زنده · تغییر قالب، ظاهر پست‌های ذخیره‌شده را تغییر نمی‌دهد.</p></div>
        </div>
      </section>
      <div class="workspace">
        <div class="panel queue-panel">
          <nav aria-label="وضعیت پست‌ها"><button v-for="(label, key) in labels" :key="key" :aria-pressed="tab === key" @click="tab = key">{{ label }}</button></nav>
          <div v-if="!visible.length" class="empty"><span class="empty-mark" aria-hidden="true">✧</span><h2>{{ tab === 'published' ? 'آرشیو هنوز خالی است' : 'یک یادآوری تازه بسازید' }}</h2><p>{{ tab === 'published' ? 'پس از انتشار در اینستاگرام، لینک پست را ثبت کنید.' : 'از آیه‌های پیشنهادی پایین صفحه شروع کنید.' }}</p></div>
          <article v-for="(post, index) in visible" :key="post.id" :class="['queue-row', { selected: selectedId === post.id }]">
            <button class="row-content" @click="select(post)"><small>{{ verseFor(post).topic }} · {{ verseFor(post).reference }}</small><p>{{ verseFor(post).translation }}</p></button>
            <div v-if="tab === 'queued'" class="order"><button :disabled="busy || index === 0" aria-label="یک جایگاه بالاتر" @click="move(index, -1)">↑</button><button :disabled="busy || index === visible.length - 1" aria-label="یک جایگاه پایین‌تر" @click="move(index, 1)">↓</button></div>
          </article>
        </div>
        <div class="panel editor">
          <template v-if="selected">
            <div class="preview"><img v-if="previewUrl" :src="previewUrl" :alt="verseFor(selected).translation" /><div v-else class="preview-loading" role="status">{{ previewError ? 'ساخت پیش‌نمایش ناموفق بود' : 'در حال ساخت تصویر…' }}<button v-if="previewError" @click="preview">تلاش دوباره</button></div></div>
            <div class="editor-body"><p class="spec">۱۰۸۰ × ۱۳۵۰ · ترجمه فارسی · متن قابل ویرایش</p>
              <label>قالب پست<select aria-label="قالب پست" v-model="form.template_id" :disabled="busy || locked"><option value="">طراحی ذخیره‌شده این پست</option><option v-for="template in templates" :key="template.id" :value="template.id">{{ template.name }}{{ template.is_default ? ' · پیش‌فرض' : '' }}</option></select></label>
              <p v-if="form.template_id" class="spec">برای ثبت این قالب روی پست، تغییرات را ذخیره کنید.</p>
              <label>منبع ترجمه<select v-model="form.translator_key" :disabled="busy || locked" @change="resetImageText"><option value="khorramdel">تفسیر نور · دکتر مصطفی خرمدل</option><option value="rowwad">مرکز ترجمه رواد</option></select></label>
              <label>متن روی تصویر<textarea v-model="form.image_text" maxlength="1200" rows="5" :disabled="busy || locked" /></label>
              <button :disabled="busy || locked" @click="resetImageText">بازگردانی متن منبع</button>
              <p class="spec">متن ویرایش‌شده با عبارت «برگرفته از» نمایش داده می‌شود. کپشن را نیز با متن تصویر هماهنگ کنید.</p>
              <label>کپشن<textarea v-model="form.caption" maxlength="2200" rows="6" :disabled="busy || locked" /></label>
              <a :href="imageVerse.source_url" target="_blank" rel="noopener noreferrer">بررسی ترجمه و متن آیه ↗</a>
              <div class="actions"><button :disabled="busy || !previewUrl || previewing" @click="download">دریافت تصویر</button><button :disabled="busy" @click="copyCaption">کپی کپشن</button></div>
              <template v-if="!locked">
                <div class="actions"><button :disabled="busy || !form.caption.trim()" @click="save()">ذخیره تغییرات</button><button :disabled="busy || !form.caption.trim()" @click="save(selected.status === 'draft' ? 'queued' : 'draft')">{{ selected.status === 'draft' ? 'تأیید و افزودن به صف' : 'بازگشت به پیش‌نویس' }}</button></div>
                <form v-if="selected.status === 'queued'" class="publish" @submit.prevent="save('published')"><label>لینک پست منتشرشده<input v-model.trim="form.instagram_url" type="url" placeholder="https://www.instagram.com/p/…/" required /></label><button :disabled="busy || !form.instagram_url">ثبت انتشار دستی</button></form>
                <form v-if="selected.status === 'queued'" class="publish" @submit.prevent="schedule">
                  <label>زمان ارسال · به وقت تهران<input v-model="form.scheduled_local" type="datetime-local" required :disabled="busy || !publishing.configured" /></label>
                  <p v-if="!publishing.configured" class="spec">ابتدا دسترسی انتشار حساب اینستاگرام را تنظیم کنید؛ ساخت حساب به‌تنهایی اتصال انتشار نیست.</p>
                  <p class="spec">ارسال پس از زمان انتخابی، در اجرای بعدی سرویس انجام می‌شود و ممکن است تأخیر داشته باشد.</p>
                  <button :disabled="busy || !publishing.configured || !form.scheduled_local || !previewUrl || previewing">زمان‌بندی ارسال به اینستاگرام</button>
                </form>
                <button class="remove" :disabled="busy" @click="remove">حذف از صف</button>
              </template>
              <a v-else-if="selected.status === 'published'" :href="selected.instagram_url" target="_blank" rel="noopener noreferrer">مشاهده پست منتشرشده ↗</a>
              <p v-if="selected.scheduled_at" class="spec">زمان ارسال: {{ scheduleTime(selected.scheduled_at) }} · تهران</p>
              <p v-if="selected.publish_error" class="notice error">{{ selected.publish_error }}</p>
              <button v-if="['scheduled', 'failed'].includes(selected.status)" :disabled="busy" @click="cancelSchedule">لغو زمان‌بندی و بازگشت به صف</button>
              <form v-if="selected.status === 'publishing' && selected.publish_started_at && selected.publish_error" class="publish" @submit.prevent="reconcile"><label>لینک پست پس از بررسی انتشار در اینستاگرام<input v-model.trim="form.instagram_url" type="url" required /></label><button :disabled="busy || !form.instagram_url">تأیید انتشار و ثبت لینک</button></form>
              <p v-if="selected.status === 'publishing'" class="spec">برای جلوگیری از انتشار تکراری، این پست قفل است. در صورت خطا، نتیجه ارسال باید در اینستاگرام بررسی شود.</p>
            </div>
          </template>
          <div v-else class="empty"><h2>پیش‌نمایش پست</h2><p>یک پست را برای دیدن تصویر و آماده‌سازی کپشن انتخاب کنید.</p></div>
        </div>
      </div>
      <section class="library"><h2>آیه‌های پیشنهادی</h2><p>گزیده‌ای درباره امید، صبر و یاد الله؛ انتخاب تحریریه، بدون رتبه‌بندی آماری اشتراک‌گذاری.</p><div class="library-grid"><article v-for="verse in catalog" :key="verse.key" class="panel"><span class="tag">{{ verse.topic }}</span><p>{{ verse.translation }}</p><small>{{ verse.reference }}</small><button :disabled="busy || posts.some(p => p.verse_key === verse.key)" @click="add(verse)">{{ posts.some(p => p.verse_key === verse.key) ? 'در فهرست موجود است' : 'ساخت پیش‌نویس' }}</button></article></div></section>
      <p class="footnote">ارسال زمان‌بندی‌شده به دسترسی انتشار حساب حرفه‌ای نیاز دارد. انتشار دستی و ثبت لینک نیز در دسترس است.</p>
    </template>
  </section>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue';
import { instagramApi as axios } from '@/services/instagramApi';
import { renderInstagramImage, fontChoices, defaultDesign, normalizeDesign } from '@/services/instagramImage';

const labels = { draft: 'پیش‌نویس', queued: 'آماده انتشار', scheduled: 'زمان‌بندی‌شده', publishing: 'در حال ارسال', failed: 'ارسال ناموفق', published: 'منتشرشده' };
const posts = ref([]), catalog = ref([]), tab = ref('queued'), selectedId = ref(null);
const loading = ref(true), loadFailed = ref(false), busy = ref(false), error = ref(''), message = ref('');
const previewUrl = ref(''), previewError = ref(false), previewing = ref(false);
const form = ref({ template_id: '', caption: '', instagram_url: '' });
const publishing = ref({ configured: false });
const locked = computed(() => selected.value && !['draft', 'queued'].includes(selected.value.status));
const templates = ref([]), settingsOpen = ref(false), editingTemplateId = ref(null);
const templateForm = ref({ name: 'قالب جدید', is_default: false, design: { ...defaultDesign } });
const templatePreviewUrl = ref(''), templatePreviewError = ref(false);
const selected = computed(() => posts.value.find(p => p.id === selectedId.value));
const visible = computed(() => posts.value.filter(p => p.status === tab.value));
const digits = value => new Intl.NumberFormat('fa-IR').format(value);
const sourceVerse = post => catalog.value.find(v => v.key === post.verse_key);
const verseFor = post => {
  const verse = sourceVerse(post);
  const source = verse.translations[post.translator_key || 'rowwad'];
  return { ...verse, ...source, translation: post.image_text || source.translation,
    credit: post.image_text && post.image_text !== source.translation ? `برگرفته از ${source.translator}` : `ترجمه: ${source.translator}` };
};
const imageVerse = computed(() => selected.value ? verseFor({ ...selected.value, image_text: form.value.image_text, translator_key: form.value.translator_key }) : null);
function resetImageText() { form.value.image_text = sourceVerse(selected.value).translations[form.value.translator_key].translation; }
const scheduleTime = value => value ? new Intl.DateTimeFormat('fa-IR', { timeZone: 'Asia/Tehran', dateStyle: 'medium', timeStyle: 'short' }).format(new Date(value.includes('T') ? value : value.replace(' ', 'T') + 'Z')) : '';
let previewGeneration = 0;
let templateGeneration = 0;
const postDesign = computed(() => form.value.template_id
  ? templates.value.find(t => t.id === Number(form.value.template_id))?.design
  : selected.value?.design || normalizeDesign(selected.value?.theme));
const validTemplateDesign = computed(() => {
  const d = templateForm.value.design;
  return ['background', 'text', 'muted'].every(key => /^#[0-9a-f]{6}$/i.test(d[key]))
    && fontChoices.some(f => f.value === d.font) && ['centered', 'upper', 'framed'].includes(d.layout)
    && Number.isInteger(d.font_size) && d.font_size >= 36 && d.font_size <= 84
    && Number.isFinite(d.line_height) && d.line_height >= 1.4 && d.line_height <= 2.2
    && Number.isInteger(d.margin) && d.margin >= 80 && d.margin <= 180
    && Number.isInteger(d.logo_size) && d.logo_size >= 48 && d.logo_size <= 120;
});
function luminance(hex) {
  const values = hex.slice(1).match(/../g).map(v => parseInt(v, 16) / 255).map(v => v <= .04045 ? v / 12.92 : ((v + .055) / 1.055) ** 2.4);
  return values[0] * .2126 + values[1] * .7152 + values[2] * .0722;
}
const lowContrast = computed(() => {
  if (!validTemplateDesign.value) return false;
  const a = luminance(templateForm.value.design.background), b = luminance(templateForm.value.design.text);
  return (Math.max(a, b) + .05) / (Math.min(a, b) + .05) < 4.5;
});

function clearPreview() { if (previewUrl.value) URL.revokeObjectURL(previewUrl.value); previewUrl.value = ''; }
function select(post) {
  selectedId.value = post.id;
  form.value = { template_id: '', caption: post.caption, instagram_url: post.instagram_url || '', image_text: post.image_text, translator_key: post.translator_key, scheduled_local: '' };
}
async function preview() {
  const generation = ++previewGeneration;
  clearPreview();
  previewError.value = false;
  if (!selected.value) return;
  previewing.value = true;
  try {
    const blob = await renderInstagramImage(imageVerse.value, postDesign.value);
    if (generation === previewGeneration) previewUrl.value = URL.createObjectURL(blob);
  } catch {
    if (generation === previewGeneration) previewError.value = true;
  } finally { if (generation === previewGeneration) previewing.value = false; }
}
watch([selectedId, postDesign, imageVerse], preview, { deep: true });
watch(tab, () => { if (selected.value?.status !== tab.value) { if (visible.value.length) select(visible.value[0]); else selectedId.value = null; } });
async function load() {
  loading.value = true; error.value = '';
  try {
    const { data } = await axios.get('admin/instagram-queue');
    posts.value = data.data; catalog.value = data.catalog; templates.value = data.templates; publishing.value = data.publishing; loadFailed.value = false;
    if (!editingTemplateId.value && templates.value.length && !settingsOpen.value) editTemplate(templates.value.find(t => t.is_default) || templates.value[0]);
    if (!selected.value && visible.value.length) select(visible.value[0]);
  } catch { loadFailed.value = true; error.value = 'دریافت صف ناموفق بود.'; }
  finally { loading.value = false; }
}
async function mutate(action) {
  busy.value = true; error.value = ''; message.value = '';
  try { await action(); }
  catch (e) { error.value = e.response?.status === 409 ? 'صف تغییر کرده است؛ تازه‌سازی کنید.' : 'عملیات انجام نشد؛ اطلاعات را بررسی و دوباره تلاش کنید.'; }
  finally { busy.value = false; }
}
async function add(verse) {
  await mutate(async () => { const { data } = await axios.post('admin/instagram-queue', { verse_key: verse.key }); await load(); tab.value = data.data.status; select(data.data); });
}
async function save(status) {
  await mutate(async () => {
    const payload = { caption: form.value.caption, image_text: form.value.image_text, translator_key: form.value.translator_key };
    if (form.value.template_id) payload.template_id = Number(form.value.template_id);
    if (status) payload.status = status;
    if (status === 'published') payload.instagram_url = form.value.instagram_url;
    const { data } = await axios.put(`admin/instagram-queue/${selectedId.value}`, payload);
    await load(); tab.value = data.data.status; select(data.data); message.value = 'تغییرات ذخیره شد.';
  });
}
async function schedule() {
  await mutate(async () => {
    const payload = { caption: form.value.caption, image_text: form.value.image_text, translator_key: form.value.translator_key };
    if (form.value.template_id) payload.template_id = Number(form.value.template_id);
    const { data } = await axios.put(`admin/instagram-queue/${selectedId.value}`, payload);
    const body = new FormData();
    body.append('scheduled_local', form.value.scheduled_local);
    body.append('render_token', data.data.render_token);
    body.append('image', await renderInstagramImage(verseFor(data.data), data.data.design), 'post.jpg');
    await axios.post(`admin/instagram-queue/${selectedId.value}/schedule`, body);
    tab.value = 'scheduled'; await load(); select(posts.value.find(p => p.id === data.data.id));
    message.value = 'زمان‌بندی ثبت شد؛ ارسال در اجرای بعدی سرویس پس از زمان انتخابی انجام می‌شود.';
  });
}
async function reconcile() {
  await mutate(async () => { const id = selectedId.value; await axios.post(`admin/instagram-queue/${id}/reconcile`, { instagram_url: form.value.instagram_url }); tab.value = 'published'; await load(); select(posts.value.find(p => p.id === id)); });
}
async function cancelSchedule() {
  await mutate(async () => { const id = selectedId.value; await axios.delete(`admin/instagram-queue/${id}/schedule`); tab.value = 'queued'; await load(); select(posts.value.find(p => p.id === id)); });
}
async function move(index, delta) {
  const ids = visible.value.map(p => p.id);
  [ids[index], ids[index + delta]] = [ids[index + delta], ids[index]];
  await mutate(async () => { await axios.put('admin/instagram-queue/order', { ids }); await load(); });
}
async function remove() {
  if (!window.confirm('این پیش‌نویس از فهرست حذف شود؟')) return;
  await mutate(async () => { await axios.delete(`admin/instagram-queue/${selectedId.value}`); selectedId.value = null; await load(); });
}
function download() {
  const link = document.createElement('a'); link.href = previewUrl.value;
  link.download = `nour-adhkar-${selected.value.verse_key.replace(':', '-')}.jpg`;
  document.body.appendChild(link); link.click(); link.remove();
}
async function copyCaption() {
  try { await navigator.clipboard.writeText(form.value.caption); message.value = 'کپشن کپی شد.'; }
  catch { error.value = 'کپی خودکار در دسترس نیست؛ متن کپشن را انتخاب و کپی کنید.'; }
}
function editTemplate(template) {
  editingTemplateId.value = template.id;
  templateForm.value = { name: template.name, is_default: template.is_default, design: { ...template.design } };
}
function newTemplate() {
  editingTemplateId.value = null;
  templateForm.value = { name: 'قالب جدید', is_default: false, design: { ...defaultDesign } };
}
function duplicateTemplate() {
  editingTemplateId.value = null;
  templateForm.value = { name: `${templateForm.value.name} (کپی)`.slice(0, 80), is_default: false, design: { ...templateForm.value.design } };
}
async function saveTemplate() {
  await mutate(async () => {
    const response = editingTemplateId.value
      ? await axios.put(`admin/instagram-templates/${editingTemplateId.value}`, templateForm.value)
      : await axios.post('admin/instagram-templates', templateForm.value);
    const saved = response.data.data;
    const exists = templates.value.some(t => t.id === saved.id);
    templates.value = templates.value.map(t => t.id === saved.id ? saved : saved.is_default ? { ...t, is_default: false } : t);
    if (!exists) templates.value.push(saved);
    editTemplate(saved);
    message.value = 'قالب ذخیره شد؛ برای استفاده روی پست، آن را از فهرست قالب پست انتخاب کنید.';
  });
}
async function deleteTemplate() {
  if (!window.confirm('قالب حذف شود؟ طراحی ذخیره‌شده پست‌ها حفظ می‌شود.')) return;
  await mutate(async () => {
    const id = editingTemplateId.value;
    await axios.delete(`admin/instagram-templates/${id}`);
    templates.value = templates.value.filter(t => t.id !== id);
    if (Number(form.value.template_id) === id) form.value.template_id = '';
    editTemplate(templates.value.find(t => t.is_default) || templates.value[0]);
    message.value = 'قالب حذف شد.';
  });
}
async function previewTemplate() {
  const generation = ++templateGeneration;
  if (templatePreviewUrl.value) URL.revokeObjectURL(templatePreviewUrl.value);
  templatePreviewUrl.value = ''; templatePreviewError.value = false;
  if (!settingsOpen.value) return;
  if (!validTemplateDesign.value) { templatePreviewError.value = true; return; }
  const verse = selected.value ? imageVerse.value : catalog.value[0];
  if (!verse) return;
  try {
    const blob = await renderInstagramImage(verse, { ...templateForm.value.design });
    if (generation === templateGeneration) templatePreviewUrl.value = URL.createObjectURL(blob);
  } catch { if (generation === templateGeneration) templatePreviewError.value = true; }
}
watch([settingsOpen, () => templateForm.value.design, selectedId], previewTemplate, { deep: true });
onMounted(load);
onBeforeUnmount(() => { ++previewGeneration; ++templateGeneration; clearPreview(); if (templatePreviewUrl.value) URL.revokeObjectURL(templatePreviewUrl.value); });
</script>

<style scoped>
.social{max-width:1280px;margin:auto;padding:clamp(16px,3vw,36px);color:var(--admin-text)}.page-heading{display:flex;align-items:center;justify-content:space-between;gap:24px}h1{font-size:30px;margin:8px 0}h2{font-size:20px}.eyebrow,.spec,.footnote,small,.page-heading p,.library>p{color:var(--admin-muted)}p{line-height:1.9}.stats{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin:26px 0}.stats>div{background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:16px;padding:18px;display:flex;align-items:center;gap:14px}.stats strong{font-size:26px}.workspace{display:grid;grid-template-columns:minmax(0,1fr) minmax(300px,420px);gap:24px;align-items:start}.panel{background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:20px;overflow:hidden}nav{display:flex;gap:8px;flex-wrap:wrap;padding:18px;border-bottom:1px solid var(--admin-border)}button{font:inherit;cursor:pointer;border:1px solid var(--admin-border);border-radius:10px;background:var(--admin-bg);color:var(--admin-text);padding:10px 14px;min-height:44px}button:disabled{opacity:.5;cursor:default}nav button[aria-pressed=true],.actions button:first-child{background:var(--admin-accent);color:white}.queue-row{display:flex;align-items:center;border-bottom:1px solid var(--admin-border);padding:12px;gap:8px}.queue-row.selected{background:var(--admin-bg)}.row-content{border:0;background:transparent;text-align:right;flex:1;min-width:0}.row-content p{margin:7px 0;overflow-wrap:anywhere}.order{display:flex;flex-direction:column;gap:6px}.preview{aspect-ratio:4/5;background:#F6F3EC}.preview img{width:100%;height:100%;display:block;object-fit:contain}.preview-loading{height:100%;display:flex;align-items:center;justify-content:center;flex-direction:column;gap:16px;color:#243d34}.editor-body{padding:20px}label{display:block;margin:16px 0}select,textarea,input{font:inherit;width:100%;display:block;margin-top:8px;border:1px solid var(--admin-border);border-radius:10px;background:var(--admin-bg);color:var(--admin-text);padding:10px;box-sizing:border-box}input[type=url]{direction:ltr;text-align:left}.actions{display:flex;flex-wrap:wrap;gap:8px;margin-top:16px}.publish{border-top:1px solid var(--admin-border);margin-top:20px;padding-top:4px}.remove{margin-top:20px;color:#bc5555}.library{margin-top:36px}.library-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px}.library article{padding:22px;display:flex;flex-direction:column;align-items:start}.library article p{flex:1}.library article button{margin-top:18px}.tag{font-size:12px;color:var(--admin-muted)}a{color:var(--admin-accent);display:inline-block;line-height:1.8}.empty{text-align:center;padding:48px 24px;color:var(--admin-muted)}.empty h2{color:var(--admin-text)}.empty-mark{font-size:44px}.notice{padding:14px 18px;background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:12px}.error{color:#bc5555}.footnote{font-size:13px;margin-top:28px}button:focus-visible,a:focus-visible{outline:2px solid var(--admin-accent);outline-offset:3px}@media(max-width:900px){.workspace{grid-template-columns:1fr}.library-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.editor{max-width:480px;width:100%;justify-self:center}}@media(max-width:540px){.page-heading{align-items:start;flex-direction:column}.stats{gap:8px}.stats>div{flex-direction:column;align-items:start;padding:12px;gap:2px;font-size:12px}.library-grid{grid-template-columns:1fr}}

.template-settings{padding:24px;margin-bottom:24px}.settings-heading{display:flex;justify-content:space-between;align-items:center;gap:16px}.settings-heading p{color:var(--admin-muted)}.template-list{display:flex;flex-wrap:wrap;gap:10px;margin:20px 0}.template-list button{display:flex;align-items:center;gap:10px}.template-list button[aria-pressed=true]{border-color:var(--admin-accent)}.template-list small{font-size:11px}.swatch{width:32px;height:36px;border-radius:6px;display:inline-flex;align-items:center;justify-content:center;border:1px solid var(--admin-border)}.settings-grid{display:grid;grid-template-columns:minmax(0,1fr) 280px;gap:28px}.field-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:0 16px}.color-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px}input[type=color]{height:48px;padding:4px;cursor:pointer}.color-value{display:block;font-size:12px;color:var(--admin-muted);direction:ltr;margin-top:6px}.default-check{display:flex;gap:10px;align-items:center}.default-check input{width:20px;height:20px;margin:0}.template-proof .preview{border-radius:12px;overflow:hidden}.contrast-warning{padding:10px;border:1px solid #b38b50;border-radius:8px;color:var(--admin-text)}@media(max-width:700px){.settings-grid{grid-template-columns:1fr}.template-proof{max-width:300px;width:100%;justify-self:center}.settings-heading{align-items:start;flex-direction:column}.template-settings{padding:16px}.color-grid{grid-template-columns:1fr}.color-grid label{display:grid;grid-template-columns:1fr 64px;align-items:center;gap:8px}.color-value{grid-column:1/-1}.field-grid{grid-template-columns:1fr}}
</style>
