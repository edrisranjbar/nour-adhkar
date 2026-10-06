<template>
  <section class="social" dir="rtl">
    <header class="page-heading">
      <div><span class="eyebrow">استودیوی محتوا · اذکار نور</span><h1>صف اینستاگرام</h1><p>یادآوری بعدی را آماده کنید.</p></div>
      <div class="heading-actions"><button class="quiet" :disabled="busy || loading" @click="load">تازه‌سازی</button><button class="primary" :disabled="busy || loading || loadFailed" @click="openPage('library')"><span aria-hidden="true">＋</span> پست جدید</button></div>
    </header>
    <nav class="page-nav" aria-label="بخش‌های استودیو">
      <button :aria-pressed="pageMode === 'queue'" :disabled="busy" @click="openPage('queue')">پست‌ها <span class="count">{{ digits(posts.length) }}</span></button>
      <button :aria-pressed="pageMode === 'templates'" :disabled="busy || loading || loadFailed" @click="openPage('templates')">قالب‌ها</button>
      <button :aria-pressed="pageMode === 'library'" :disabled="busy || loading || loadFailed" @click="openPage('library')">آیه‌های پیشنهادی</button>
    </nav>
    <p v-if="error" class="notice error" role="alert">{{ error }}</p><p v-if="message" class="notice" role="status">{{ message }}</p>
    <div v-if="loading" class="loading-shell" role="status"><span class="loading-dot"></span>در حال دریافت پست‌ها…</div>
    <div v-else-if="loadFailed" class="panel empty"><span class="empty-mark" aria-hidden="true">↻</span><h2>صف دریافت نشد</h2><p>اتصال را بررسی کنید و دوباره تلاش کنید.</p><button @click="load">تلاش دوباره</button></div>
    <template v-else>
      <section v-if="settingsOpen" class="panel template-settings" aria-labelledby="template-heading">
        <div class="settings-heading"><div><h2 id="template-heading">طراحی قالب‌ها</h2><p>رنگ، قلم و چیدمان را تغییر دهید؛ سپس قالب را ذخیره و برای هر پست انتخاب کنید.</p></div><button :disabled="busy" @click="newTemplate">ساخت قالب جدید</button></div>
        <div class="template-list" aria-label="قالب‌های ذخیره‌شده"><button v-for="template in templates" :key="template.id" :aria-pressed="editingTemplateId === template.id" :disabled="busy" @click="editTemplate(template)"><span class="swatch" :style="{ background: template.design.background, color: template.design.text }">آ</span>{{ template.name }}<small v-if="template.is_default">پیش‌فرض</small></button></div>
        <div class="settings-grid">
          <form class="template-form" @submit.prevent="saveTemplate">
            <label>نام قالب<input v-model.trim="templateForm.name" required maxlength="80" :disabled="busy" /></label>
            <div class="color-grid"><label>رنگ پس‌زمینه<input v-model="templateForm.design.background" type="color" aria-label="رنگ پس‌زمینه" :disabled="busy" /><span class="color-value">{{ templateForm.design.background }}</span></label><label>رنگ متن<input v-model="templateForm.design.text" type="color" aria-label="رنگ متن" :disabled="busy" /><span class="color-value">{{ templateForm.design.text }}</span></label><label>رنگ متن فرعی<input v-model="templateForm.design.muted" type="color" aria-label="رنگ متن فرعی" :disabled="busy" /><span class="color-value">{{ templateForm.design.muted }}</span></label></div>
            <div class="field-grid"><label>قلم<select aria-label="قلم" v-model="templateForm.design.font" :disabled="busy"><option v-for="font in fontChoices" :key="font.value" :value="font.value">{{ font.label }}</option></select></label><label>چیدمان<select aria-label="چیدمان" v-model="templateForm.design.layout" :disabled="busy"><option value="centered">متن در مرکز</option><option value="upper">متن در نیمه بالا</option><option value="framed">مرکز با قاب ظریف</option></select></label></div><details class="advanced"><summary>اندازه و فاصله‌ها</summary><div class="field-grid"><label>اندازه متن<input v-model.number="templateForm.design.font_size" type="number" min="36" max="84" required :disabled="busy" /></label><label>فاصله خطوط<input v-model.number="templateForm.design.line_height" type="number" min="1.4" max="2.2" step="0.05" required :disabled="busy" /></label><label>حاشیه طرفین<input v-model.number="templateForm.design.margin" type="number" min="80" max="180" required :disabled="busy" /></label><label>اندازه لوگو<input v-model.number="templateForm.design.logo_size" type="number" min="48" max="120" required :disabled="busy" /></label></div>
            </details><p class="spec">اندازه‌ها برحسب پیکسل در تصویر خروجی‌اند؛ متن بلند برای جا شدن در تصویر کوچک‌تر می‌شود.</p>
            <p v-if="lowContrast" class="contrast-warning" role="status">کنتراست متن و پس‌زمینه کم است؛ رنگ خواناتری انتخاب کنید.</p>
            <label class="default-check"><input v-model="templateForm.is_default" type="checkbox" :disabled="busy || !!templates.find(t => t.id === editingTemplateId && t.is_default)" />قالب پیش‌فرض برای پست‌های جدید</label>
            <div class="actions"><button class="primary" :disabled="busy || !templateForm.name || !validTemplateDesign">{{ busy ? 'در حال ذخیره…' : 'ذخیره قالب' }}</button><button type="button" :disabled="busy" @click="duplicateTemplate">کپی به قالب جدید</button><button v-if="editingTemplateId" type="button" :disabled="busy || templateForm.is_default" @click="deleteTemplate">حذف قالب</button></div>
          </form>
          <div class="template-proof"><div class="preview"><img v-if="templatePreviewUrl" :src="templatePreviewUrl" alt="پیش‌نمایش قالب" /><div v-else class="preview-loading" role="status">{{ templatePreviewError ? 'تنظیمات قالب را بررسی کنید' : 'در حال ساخت پیش‌نمایش…' }}</div></div><p class="spec">پیش‌نمایش زنده · تغییر قالب، ظاهر پست‌های ذخیره‌شده را تغییر نمی‌دهد.</p></div>
        </div>
      </section>

      <template v-if="pageMode === 'queue'">
        <nav class="status-nav" aria-label="وضعیت پست‌ها"><button v-for="(label, key) in labels" :key="key" :aria-pressed="tab === key" :disabled="busy" @click="switchTab(key)">{{ label }}<span class="count">{{ digits(posts.filter(p => p.status === key).length) }}</span></button></nav>
        <div class="workspace">
          <section class="panel queue-panel" aria-label="فهرست پست‌ها">
            <div class="list-heading"><h2>{{ labels[tab] }}</h2><span class="muted">{{ digits(visible.length) }} پست</span></div>
            <div v-if="!visible.length" class="empty"><span class="empty-mark" aria-hidden="true">✧</span><h3>{{ tab === 'published' ? 'آرشیو هنوز خالی است' : 'هنوز پستی اینجا نیست' }}</h3><p>{{ tab === 'draft' ? 'یک آیه انتخاب کنید و پیش‌نویس بسازید.' : 'با انتخاب وضعیت دیگر، پست‌هایتان را ببینید.' }}</p><button v-if="['draft', 'queued'].includes(tab)" @click="openPage('library')">انتخاب آیه</button></div>
            <div class="queue-list">
              <article v-for="(post, index) in visible" :key="post.id" :class="['queue-row', { selected: selectedId === post.id }]">
                <button class="row-content" :aria-pressed="selectedId === post.id" :disabled="busy" @click="choosePost(post)"><span class="row-meta"><span>{{ verseFor(post).topic }}</span><span class="row-number">{{ digits(index + 1) }}</span></span><h3>{{ persianText(verseFor(post).reference) }}</h3><p>{{ verseFor(post).translation }}</p><small v-if="post.scheduled_at">{{ scheduleTime(post.scheduled_at) }}</small></button>
                <div v-if="tab === 'queued' && visible.length > 1" class="order"><button :disabled="busy || index === 0" aria-label="یک جایگاه بالاتر" @click="move(index, -1)">↑</button><button :disabled="busy || index === visible.length - 1" aria-label="یک جایگاه پایین‌تر" @click="move(index, 1)">↓</button></div>
              </article>
            </div>
          </section>
          <section class="panel editor" aria-label="ویرایش پست">
            <template v-if="selected">
              <header class="editor-heading"><div><span class="eyebrow">{{ verseFor(selected).topic }}</span><h2>{{ persianText(verseFor(selected).reference) }}</h2></div><span :class="['status-badge', selected.status]">{{ labels[selected.status] }}</span></header>
              <div class="editor-grid">
                <div class="preview-column"><div class="preview"><img v-if="previewUrl" :src="previewUrl" :alt="imageVerse.translation" /><div v-else class="preview-loading" role="status">{{ previewError ? 'پیش‌نمایش آماده نشد؛ متن و اتصال را بررسی کنید.' : 'در حال ساخت تصویر…' }}<button v-if="previewError" @click="preview">تلاش دوباره</button></div></div><p class="spec preview-spec">۱۰۸۰ × ۱۳۵۰ · تصویر پست</p><button class="download" :disabled="busy || !previewUrl || previewing" @click="download">دریافت تصویر</button></div>
                <div class="editor-body">
                  <nav class="editor-tabs" aria-label="مراحل آماده‌سازی"><button :aria-pressed="editorTab === 'content'" @click="editorTab = 'content'">متن و ظاهر</button><button :aria-pressed="editorTab === 'publish'" @click="editorTab = 'publish'">انتشار</button></nav>
                  <div v-show="editorTab === 'content'" class="content-fields">
                    <div class="field-header"><label for="image-text">متن روی تصویر</label><span class="muted">{{ digits((form.image_text || '').length) }} / ۱۲۰۰</span></div><textarea id="image-text" v-model="form.image_text" maxlength="1200" rows="5" :disabled="busy || locked" />
                    <div class="field-grid"><label>منبع ترجمه<select v-model="form.translator_key" :disabled="busy || locked" @change="resetImageText"><option value="khorramdel">تفسیر نور · خرمدل</option><option value="rowwad">مرکز ترجمه رواد</option></select></label><label>قالب پست<select aria-label="قالب پست" v-model="form.template_id" :disabled="busy || locked"><option value="">طراحی ذخیره‌شده</option><option v-for="template in templates" :key="template.id" :value="template.id">{{ template.name }}</option></select></label></div>
                    <details class="source-details"><summary>متن اصلی و منبع</summary><p>{{ sourceVerse(selected).translations[form.translator_key].translation }}</p><div class="actions"><button class="text-button" :disabled="busy || locked" @click="resetImageText">بازگردانی متن منبع</button><a :href="imageVerse.source_url" target="_blank" rel="noopener noreferrer">بررسی منبع ↗</a></div><p class="spec">متن ویرایش‌شده با «برگرفته از» نمایش داده می‌شود.</p></details>
                    <div class="field-header caption-heading"><label for="post-caption">کپشن</label><button class="text-button" :disabled="busy" @click="copyCaption">کپی</button></div><textarea id="post-caption" v-model="form.caption" maxlength="2200" rows="5" :disabled="busy || locked" /><p class="spec">{{ digits(form.caption.length) }} / ۲۲۰۰ · کپشن را با متن تصویر هماهنگ کنید.</p>
                  </div>
                  <div v-show="editorTab === 'publish'" class="publication">
                    <template v-if="selected.status === 'draft'"><div class="publication-intro"><span class="empty-mark" aria-hidden="true">✓</span><h3>آمادهٔ بازبینی</h3><p>متن، تصویر و کپشن را بررسی کنید؛ سپس پست را به صف آمادهٔ انتشار اضافه کنید.</p></div><button class="primary" :disabled="busy || !validPost || previewing || !previewUrl" @click="save('queued')">تأیید و افزودن به صف</button></template>
                    <template v-if="selected.status === 'queued'">
                      <div class="method-choice" aria-label="روش انتشار"><button :aria-pressed="publishMethod === 'manual'" @click="publishMethod = 'manual'">انتشار دستی</button><button :aria-pressed="publishMethod === 'schedule'" @click="publishMethod = 'schedule'">زمان‌بندی</button></div>
                      <form v-if="publishMethod === 'manual'" class="publish" @submit.prevent="save('published')"><h3>انتشار در اینستاگرام</h3><p class="muted">تصویر را دریافت و همراه کپشن منتشر کنید؛ سپس لینک پست را اینجا ثبت کنید.</p><button type="button" :disabled="busy" @click="copyCaption">کپی کپشن</button><label>لینک پست منتشرشده<input v-model.trim="form.instagram_url" type="url" placeholder="https://www.instagram.com/p/…/" required :disabled="busy" /></label><button class="primary" :disabled="busy || !form.instagram_url || !validPost">ثبت انتشار دستی</button></form>
                      <form v-else class="publish" @submit.prevent="schedule"><h3>ارسال زمان‌بندی‌شده</h3><div v-if="!publishing.configured" class="connection-note"><strong>اتصال انتشار هنوز آماده نیست</strong><p>برای فعال شدن ارسال به @nouradhkar، دسترسی انتشار حساب را از طریق Meta تنظیم کنید.</p></div><label>زمان ارسال · به وقت تهران<input v-model="form.scheduled_local" type="datetime-local" required :disabled="busy || !publishing.configured" /></label><p class="spec">ارسال در اجرای بعدی سرویس پس از زمان انتخابی انجام می‌شود و ممکن است تأخیر داشته باشد.</p><button class="primary" :disabled="busy || !publishing.configured || !form.scheduled_local || !previewUrl || previewing || !validPost">زمان‌بندی ارسال به اینستاگرام</button></form>
                    </template>
                    <div v-if="locked" class="publication-intro"><span :class="['status-badge', selected.status]">{{ labels[selected.status] }}</span><p v-if="selected.scheduled_at">{{ scheduleTime(selected.scheduled_at) }} · تهران</p><p v-if="selected.publish_error" class="notice error">{{ selected.publish_error }}</p><a v-if="selected.status === 'published'" :href="selected.instagram_url" target="_blank" rel="noopener noreferrer">مشاهده پست در اینستاگرام ↗</a><button v-if="['scheduled', 'failed'].includes(selected.status)" :disabled="busy" @click="cancelSchedule">لغو زمان‌بندی و بازگشت به صف</button><p v-if="selected.status === 'publishing'" class="muted">این پست برای جلوگیری از ارسال تکراری قفل است. در صورت خطا، نتیجه را در اینستاگرام بررسی کنید.</p></div>
                    <form v-if="selected.status === 'publishing' && selected.publish_started_at && selected.publish_error" class="publish" @submit.prevent="reconcile"><label>لینک پست پس از بررسی انتشار<input v-model.trim="form.instagram_url" type="url" required /></label><button class="primary" :disabled="busy || !form.instagram_url">تأیید انتشار و ثبت لینک</button></form>
                  </div>
                </div>
              </div>
              <footer class="editor-footer"><span class="save-state">{{ locked ? 'این پست فقط قابل مشاهده است' : dirty ? 'تغییرات ذخیره‌نشده' : 'همهٔ تغییرات ذخیره شده' }}</span><div class="actions" v-if="!locked"><button class="primary" :disabled="busy || !dirty || !validPost" @click="save()">{{ busy ? 'در حال ذخیره…' : 'ذخیره تغییرات' }}</button><button v-if="selected.status === 'draft' && editorTab === 'content'" :disabled="busy || !validPost || !previewUrl || previewing" @click="save('queued')">افزودن به صف</button><details class="post-options"><summary aria-label="گزینه‌های بیشتر پست">•••</summary><div><button v-if="selected.status === 'queued'" :disabled="busy" @click="save('draft')">بازگشت به پیش‌نویس</button><button class="danger" :disabled="busy" @click="remove">حذف پست</button></div></details></div></footer>
            </template>
            <div v-else class="empty editor-empty"><div class="empty-canvas" aria-hidden="true"><span>نور</span></div><h2>جای یادآوری بعدی شما</h2><p>یک پست را از فهرست انتخاب کنید یا با یک آیهٔ تازه شروع کنید.</p><button class="primary" @click="openPage('library')">انتخاب آیهٔ تازه</button></div>
          </section>
        </div>
      </template>
      <section v-if="pageMode === 'library'" class="library"><header class="section-heading"><div><h2>یک یادآوری تازه انتخاب کنید</h2><p>آیه‌هایی دربارهٔ امید، صبر و یاد الله، با ترجمهٔ تفسیر نور.</p></div><span class="muted">{{ digits(catalog.length) }} آیه</span></header><div class="library-grid"><article v-for="verse in catalog" :key="verse.key" class="panel library-card"><div class="row-meta"><span class="tag">{{ verse.topic }}</span><small>{{ persianText(verse.reference) }}</small></div><p>{{ verse.translation }}</p><details class="library-source"><summary>مشاهدهٔ متن کامل</summary><p>{{ verse.translation }}</p><a :href="verse.source_url" target="_blank" rel="noopener noreferrer">بررسی منبع ↗</a></details><button :class="{ primary: !posts.some(p => p.verse_key === verse.key) }" :disabled="busy" @click="openVerse(verse)">{{ posts.some(p => p.verse_key === verse.key) ? 'مشاهدهٔ پست موجود' : 'ساخت پیش‌نویس' }}</button></article></div><p class="spec">انتخاب تحریریه · بدون رتبه‌بندی آماری اشتراک‌گذاری</p></section>
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
const pageMode = ref('queue'), editorTab = ref('content'), publishMethod = ref('manual');
const settingsOpen = computed(() => pageMode.value === 'templates');
const templates = ref([]), editingTemplateId = ref(null);
const templateForm = ref({ name: 'قالب جدید', is_default: false, design: { ...defaultDesign } });
const templatePreviewUrl = ref(''), templatePreviewError = ref(false);
const selected = computed(() => posts.value.find(p => p.id === selectedId.value));
const visible = computed(() => posts.value.filter(p => p.status === tab.value));
const validPost = computed(() => !!form.value.image_text?.trim() && !!form.value.caption?.trim());
const dirty = computed(() => selected.value && !locked.value && (form.value.image_text !== selected.value.image_text || form.value.caption !== selected.value.caption || form.value.translator_key !== selected.value.translator_key || !!form.value.template_id));
function canLeavePost() { return !dirty.value || window.confirm('تغییرات ذخیره نشده‌اند. بدون ذخیره ادامه می‌دهید؟'); }
function choosePost(post) { if (post.id !== selectedId.value && canLeavePost()) select(post); }
function switchTab(key) { if (key !== tab.value && canLeavePost()) tab.value = key; }
function openPage(mode) { pageMode.value = mode; message.value = ''; }
async function openVerse(verse) {
  if (!canLeavePost()) return;
  const existing = posts.value.find(p => p.verse_key === verse.key);
  if (existing) { tab.value = existing.status; select(existing); }
  else await add(verse);
  pageMode.value = 'queue';
}

const digits = value => new Intl.NumberFormat('fa-IR').format(value);
const persianText = value => value.replace(/\d/g, digit => '۰۱۲۳۴۵۶۷۸۹'[digit]);
const sourceVerse = post => catalog.value.find(v => v.key === post.verse_key);
const verseFor = post => {
  const verse = sourceVerse(post);
  const source = verse.translations[post.translator_key || 'rowwad'];
  return { ...verse, ...source, translation: post.image_text ?? source.translation,
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
  editorTab.value = ['draft', 'queued'].includes(post.status) ? 'content' : 'publish';
  form.value = { template_id: '', caption: post.caption, instagram_url: post.instagram_url || '', image_text: post.image_text, translator_key: post.translator_key, scheduled_local: '' };
}
async function preview() {
  const generation = ++previewGeneration;
  clearPreview();
  previewError.value = false;
  if (!selected.value) return;
  previewing.value = true;
  try {
    if (!form.value.image_text?.trim()) throw new Error('Empty image text');
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
  link.download = `nour-adhkar-${selected.value.verse_key.replace(':', '-')}
.jpg`;
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
watch([settingsOpen, () => templateForm.value.design, imageVerse], previewTemplate, { deep: true });
onMounted(load);
onBeforeUnmount(() => { ++previewGeneration; ++templateGeneration; clearPreview(); if (templatePreviewUrl.value) URL.revokeObjectURL(templatePreviewUrl.value); });
</script>

<style scoped>
.social{max-width:1440px;margin:auto;padding:28px;color:var(--admin-text);font-size:14px;line-height:1.8}
.social *{box-sizing:border-box}
.social h1,.social h2,.social h3,.social p{margin:0}
.social h1{font-size:28px;letter-spacing:-.7px;line-height:1.7}
.social h2{font-size:18px}
.social h3{font-size:15px}
.social p{line-height:1.9}
.social button,.social input,.social select,.social textarea{font:inherit}
.social button{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:44px;padding:9px 16px;border:1px solid var(--admin-border);border-radius:10px;background:var(--admin-surface);color:var(--admin-text);cursor:pointer;line-height:1.6}
.social button:hover:not(:disabled){border-color:var(--admin-accent)}
.social button:disabled{opacity:.45;cursor:default}
.social .primary{background:var(--admin-accent);border-color:var(--admin-accent);color:var(--admin-bg);font-weight:600}
.social .quiet,.social .text-button{background:transparent;border-color:transparent}
.social .text-button{color:var(--admin-accent);padding:3px 0;min-height:36px;font-size:12px}
.social a{color:var(--admin-accent);line-height:1.8}
.social :is(button,a,input,textarea,select,summary):focus-visible{outline:2px solid var(--admin-accent);outline-offset:3px}
.social .muted,.social .spec,.social small{color:var(--admin-muted);font-size:12px}
.social .spec{margin-top:8px}
.eyebrow{display:block;color:var(--admin-muted);font-size:11px;letter-spacing:.2px}
.page-heading{display:flex;align-items:center;justify-content:space-between;gap:20px;margin-bottom:26px}
.page-heading p{color:var(--admin-muted);font-size:13px}
.heading-actions,.actions{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.page-nav{display:flex;gap:24px;border-bottom:1px solid var(--admin-border);margin-bottom:24px}
.social .page-nav button{border:0;border-radius:0;background:transparent;padding:8px 0 15px;position:relative;color:var(--admin-muted)}
.social .page-nav button[aria-pressed=true]{color:var(--admin-text);font-weight:700}
.page-nav button[aria-pressed=true]:after{content:'';position:absolute;inset-inline:0;bottom:-1px;height:3px;background:var(--admin-accent);border-radius:3px}
.count{display:inline-flex;justify-content:center;align-items:center;background:var(--admin-bg);border:1px solid var(--admin-border);border-radius:7px;min-width:24px;height:23px;padding:0 5px;font-size:11px;color:var(--admin-muted)}
.status-nav{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:18px}
.social .status-nav button{font-size:12px;border-color:transparent;background:transparent;padding:7px 10px}
.social .status-nav button[aria-pressed=true]{background:var(--admin-surface);border-color:var(--admin-border);font-weight:600}
.workspace{display:grid;grid-template-columns:240px minmax(0,1fr);gap:20px;align-items:start}
.panel{border:1px solid var(--admin-border);background:var(--admin-surface);border-radius:16px;min-width:0}
.list-heading{padding:16px 18px;border-bottom:1px solid var(--admin-border);display:flex;align-items:center;justify-content:space-between}
.social .list-heading h2{font-size:13px}
.queue-list{padding:8px;max-height:680px;overflow:auto}
.queue-row{display:flex;align-items:center;border:1px solid transparent;border-radius:11px;margin-bottom:6px;position:relative;overflow:hidden}
.queue-row.selected{background:var(--admin-bg);border-color:var(--admin-border)}
.queue-row.selected:before{content:'';position:absolute;inset-inline-start:0;top:16px;bottom:16px;width:3px;border-radius:3px;background:var(--admin-accent)}
.social .row-content{display:block;min-width:0;flex:1;text-align:right;padding:14px 16px;background:transparent;border:0;border-radius:0}
.row-meta{display:flex;align-items:center;justify-content:space-between;gap:12px;font-size:11px;color:var(--admin-muted)}
.row-number{font-variant-numeric:tabular-nums}
.social .row-content h3{font-size:13px;margin:5px 0;font-weight:600}
.social .row-content p{font-size:12px;color:var(--admin-muted);display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;overflow-wrap:anywhere}
.order{display:flex;flex-direction:column;padding-inline-end:6px}
.social .order button{min-width:32px;min-height:40px;padding:0;border:0;background:transparent}
.editor-heading{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:20px 24px;border-bottom:1px solid var(--admin-border)}
.status-badge{display:inline-flex;align-items:center;gap:7px;font-size:11px;padding:4px 10px;border:1px solid var(--admin-border);border-radius:30px;color:var(--admin-muted);white-space:nowrap}
.status-badge:before{content:'';width:6px;height:6px;border-radius:50%;background:currentColor}
.status-badge.queued,.status-badge.scheduled,.status-badge.published{color:var(--admin-accent)}
.status-badge.failed{color:#c75b50}
.editor-grid{display:grid;grid-template-columns:minmax(180px,.85fr) minmax(250px,1.15fr);gap:24px;padding:24px}
.preview-column{align-self:start;position:sticky;top:24px;min-width:0}
.preview{aspect-ratio:4/5;background:#f6f3ec;border-radius:10px;overflow:hidden;border:1px solid var(--admin-border)}
.preview img{display:block;width:100%;height:100%;object-fit:contain}
.preview-loading{height:100%;padding:20px;text-align:center;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:14px;color:#243d34;font-size:12px}
.social .preview-spec{text-align:center;margin:10px 0 16px;font-size:11px}
.social .download{width:100%;background:transparent}
.editor-body{min-width:0}
.editor-tabs{display:flex;gap:20px;border-bottom:1px solid var(--admin-border);margin-bottom:22px}
.social .editor-tabs button{border:0;border-radius:0;background:transparent;color:var(--admin-muted);padding:0 0 12px;font-size:13px;position:relative}
.social .editor-tabs button[aria-pressed=true]{color:var(--admin-text);font-weight:700}
.editor-tabs button[aria-pressed=true]:after{content:'';position:absolute;inset-inline:0;bottom:-1px;height:2px;background:var(--admin-accent)}
.social label{display:block;font-weight:500;font-size:12px;margin:16px 0 8px}
.field-header{display:flex;align-items:center;justify-content:space-between;gap:10px}
.social .field-header label{margin:0}
.social input,.social textarea,.social select{width:100%;display:block;border:1px solid var(--admin-border);border-radius:9px;background:var(--admin-bg);color:var(--admin-text);padding:10px 12px;font-weight:400;min-width:0}
.social textarea{min-height:0;resize:vertical;line-height:1.9}
.social #image-text{margin-top:9px}
.social #post-caption{margin-top:7px}
.social input[type=url],.social input[type=datetime-local]{direction:ltr;text-align:left}
.social select,.social input{min-height:44px}
.field-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.social select{margin-top:8px}
.caption-heading{margin-top:24px}
.source-details{margin-top:12px}
.social summary{cursor:pointer;color:var(--admin-muted);font-size:12px;min-height:36px;line-height:2.5}
.source-details[open]{padding-bottom:12px;border-bottom:1px solid var(--admin-border)}
.social .source-details p{font-size:12px;color:var(--admin-muted);margin:8px 0}
.source-details .actions{gap:16px;font-size:12px}
.editor-footer{border-top:1px solid var(--admin-border);padding:16px 24px;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}
.save-state{font-size:11px;color:var(--admin-muted)}
.editor-footer .actions{gap:8px}
.editor-footer button{font-size:12px}
.post-options{position:relative}
.social .post-options summary{display:flex;align-items:center;justify-content:center;width:44px;height:44px;border:1px solid var(--admin-border);border-radius:9px;list-style:none;font-size:17px;letter-spacing:2px}
.post-options summary::-webkit-details-marker{display:none}
.post-options>div{position:absolute;bottom:calc(100% + 8px);inset-inline-end:0;width:190px;z-index:3;border:1px solid var(--admin-border);background:var(--admin-surface);box-shadow:0 8px 24px #00000010;border-radius:10px;padding:6px}
.social .post-options button{border:0;background:transparent;display:flex;justify-content:start;width:100%}
.social .danger{color:#c75b50}
.method-choice{display:grid;grid-template-columns:1fr 1fr;padding:4px;background:var(--admin-bg);border-radius:10px;gap:4px;margin-bottom:24px}
.social .method-choice button{font-size:12px;background:transparent;border:1px solid transparent;min-height:40px;padding:5px}
.social .method-choice button[aria-pressed=true]{background:var(--admin-surface);border-color:var(--admin-border)}
.social .publish h3{margin-bottom:10px}
.social .publish>.primary{width:100%;margin-top:14px}
.connection-note{border:1px solid var(--admin-border);border-radius:10px;padding:14px;background:var(--admin-bg);margin:16px 0}
.connection-note strong{font-size:12px}
.social .connection-note p{font-size:12px;color:var(--admin-muted);margin-top:5px}
.publication-intro{padding:16px 0}
.social .publication-intro p{color:var(--admin-muted);margin:14px 0;font-size:13px}
.publication-intro>button{margin-top:12px}
.publication>.primary{width:100%}
.empty{padding:36px 20px;text-align:center;color:var(--admin-muted)}
.social .empty h2,.social .empty h3{color:var(--admin-text);margin:12px 0 8px}
.social .empty p{font-size:12px;margin-bottom:20px}
.empty-mark{display:block;font-size:32px;color:var(--admin-muted)}
.editor-empty{padding:70px 30px}
.empty-canvas{display:flex;align-items:end;justify-content:end;margin:0 auto 22px;width:124px;height:155px;padding:16px;border:1px solid var(--admin-border);border-radius:10px;background:var(--admin-bg);transform:rotate(-6deg)}
.empty-canvas span{font-size:18px;padding:0 5px;color:var(--admin-muted);border:1px solid var(--admin-border);border-radius:6px}
.notice{border:1px solid var(--admin-border);border-inline-start:3px solid var(--admin-accent);border-radius:10px;background:var(--admin-surface);padding:12px 16px;margin-bottom:18px!important;font-size:13px}
.social .error{color:#c75b50;border-inline-start-color:#c75b50}
.loading-shell{display:flex;align-items:center;justify-content:center;gap:12px;min-height:320px;color:var(--admin-muted)}
.loading-dot{width:10px;height:10px;background:var(--admin-accent);border-radius:50%}
.section-heading,.settings-heading{display:flex;align-items:center;justify-content:space-between;gap:20px;margin-bottom:24px}
.section-heading p,.settings-heading p{font-size:13px;color:var(--admin-muted);margin-top:4px}
.library-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:18px}
.library-card{padding:22px;display:flex;flex-direction:column;align-items:stretch;gap:16px}
.tag{font-size:11px;padding:3px 9px;border:1px solid var(--admin-border);border-radius:30px}
.social .library-card>p{font-size:15px;min-height:114px;display:-webkit-box;-webkit-line-clamp:4;-webkit-box-orient:vertical;overflow:hidden}
.library-source{margin-top:auto}
.library-source p{font-size:13px;color:var(--admin-muted);padding:10px 0}
.library-source a{font-size:12px}
.social .library-card>button{width:100%;margin-top:0}
.template-settings{padding:26px}
.template-list{display:flex;flex-wrap:wrap;gap:10px;margin-bottom:26px}
.template-list button[aria-pressed=true]{border-color:var(--admin-accent);background:var(--admin-bg)}
.swatch{display:inline-flex;align-items:center;justify-content:center;width:28px;height:32px;border:1px solid var(--admin-border);border-radius:5px}
.settings-grid{display:grid;grid-template-columns:minmax(0,1fr) 280px;gap:36px}
.template-form>label:first-child{margin-top:0}
.color-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px}
.social input[type=color]{padding:4px;height:42px;min-height:42px;margin-top:8px}
.color-value{display:block;color:var(--admin-muted);font-size:11px;direction:ltr;margin-top:4px}
.advanced{margin-top:16px;padding:10px 16px;border:1px solid var(--admin-border);border-radius:10px}
.default-check{display:flex!important;align-items:center;gap:10px}
.social .default-check input{width:18px;height:18px;min-height:0;margin:0}
.template-form .actions{margin-top:24px}
.contrast-warning{margin-top:12px!important;font-size:12px;color:var(--admin-muted)}
.template-proof{position:sticky;top:24px;align-self:start}
@media(min-width:1500px){.workspace{grid-template-columns:280px minmax(0,1fr)}
.editor-grid{grid-template-columns:300px minmax(0,1fr)}}
@media(max-width:1250px){.workspace{grid-template-columns:210px minmax(0,1fr);gap:16px}
.editor-grid{grid-template-columns:1fr;gap:20px;padding:20px}
.preview-column{position:static;max-width:240px;width:100%;justify-self:center}
.editor-heading,.editor-footer{padding:18px 20px}
.library-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:900px){.workspace{grid-template-columns:1fr}
.queue-list{display:flex;overflow-x:auto;max-height:none;gap:8px;padding:10px}
.queue-row{flex:0 0 220px;margin:0}
.list-heading{padding:12px 16px}
.editor-grid{grid-template-columns:minmax(180px,.8fr) minmax(230px,1.2fr)}
.settings-grid{grid-template-columns:1fr 220px;gap:24px}
.color-grid{gap:8px}}
@media(max-width:650px){.social{padding:16px}
.page-heading{gap:12px;align-items:start;margin-bottom:20px}
.social h1{font-size:23px}
.heading-actions{gap:4px}
.social .heading-actions .quiet{font-size:0;padding:9px;width:44px}
.heading-actions .quiet:before{content:'↻';font-size:22px}
.heading-actions .primary{font-size:12px;padding:10px}
.page-nav{gap:20px;margin-bottom:18px}
.status-nav{gap:3px}
.social .status-nav button{padding:6px 8px}
.editor-grid{grid-template-columns:1fr;padding:18px}
.preview-column{max-width:230px}
.field-grid{gap:12px}
.editor-heading{padding:16px}
.editor-footer{padding:14px 16px}
.editor-footer .actions{width:100%}
.editor-footer .actions>.primary{flex:1}
.settings-grid{grid-template-columns:1fr}
.template-proof{position:static;max-width:230px;width:100%;justify-self:center;grid-row:1}
.settings-heading{align-items:start}
.template-settings{padding:18px}
.color-grid{grid-template-columns:1fr}
.color-grid label{display:grid;grid-template-columns:1fr 64px;align-items:center;gap:8px;margin:8px 0}
.color-value{grid-column:1/-1}
.library-grid{grid-template-columns:1fr}
.section-heading{align-items:start}
.social .library-card>p{min-height:0}
.library-card{padding:20px}
.editor-empty{padding:45px 24px}}
@media(prefers-reduced-motion:reduce){*{scroll-behavior:auto}}
@media(max-width:650px){.status-nav{flex-wrap:nowrap;overflow-x:auto;padding-bottom:5px;scrollbar-width:thin}
.social .status-nav button{flex-shrink:0}
.queue-row{flex-basis:calc(100% - 24px)}
.queue-row:only-child{flex-basis:100%}}
@media(max-width:380px){.page-heading{flex-wrap:wrap}
.page-heading>div:first-child{flex:1 1 100%}
.heading-actions{width:100%;justify-content:flex-end}}
</style>
