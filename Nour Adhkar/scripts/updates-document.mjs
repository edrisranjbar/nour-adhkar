import { JSDOM } from 'jsdom'

export const UPDATES_TITLE = 'تازه‌ها و تاریخچهٔ به‌روزرسانی‌ها | اذکار نور'
export const UPDATES_DESCRIPTION = 'تغییرات، بهبودها و رفع اشکال‌های نسخه‌های منتشرشدهٔ اذکار نور؛ تاریخچهٔ نسخه‌های برنامه برای اندروید.'
export const UPDATES_URL = 'https://adhkar.ir/updates'

const escape = value => String(value ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]))

// Keep the existing Vue bootstrap and pagination, but give crawlers and visitors the correct
// content and metadata before JavaScript runs. Never publish the homepage at the updates URL.
export function updatesDocument(index, releases = []) {
  const document = new JSDOM(index).window.document
  document.title = UPDATES_TITLE
  for (const [selector, value] of [
    ['meta[name="description"]', UPDATES_DESCRIPTION],
    ['meta[property="og:title"]', UPDATES_TITLE],
    ['meta[property="og:description"]', UPDATES_DESCRIPTION],
    ['meta[property="og:url"]', UPDATES_URL],
    ['meta[name="twitter:url"],meta[property="twitter:url"]', UPDATES_URL],
    ['meta[name="twitter:title"],meta[property="twitter:title"]', UPDATES_TITLE],
    ['meta[name="twitter:description"],meta[property="twitter:description"]', UPDATES_DESCRIPTION]
  ]) {
    const node = document.querySelector(selector)
    if (node) node.setAttribute('content', value)
  }
  const canonical = document.querySelector('link[rel="canonical"]')
  if (!canonical) throw new Error('Updates prerender requires a canonical link in the app shell')
  canonical.setAttribute('href', UPDATES_URL)
  // Homepage-only structured data and the landing HTML cleanup cannot carry over to /updates.
  document.querySelectorAll('script[type="application/ld+json"]').forEach(node => node.remove())
  for (const script of document.querySelectorAll('script:not([src])')) {
    if (script.textContent.includes('location.pathname!=="/"') && script.textContent.includes('a.innerHTML=""')) script.remove()
  }
  const app = document.getElementById('app')
  if (!app) throw new Error('Updates prerender requires #app')
  app.removeAttribute('data-ssr')
  const publicReleases = releases.filter(r => r && typeof r.versionName === 'string' && typeof r.changelog === 'string' && /^\d{4}-\d{2}-\d{2}$/.test(r.releaseDate))
  const cards = publicReleases.map(release => `<article><h2>نسخهٔ <bdi>${escape(release.versionName)}</bdi></h2><time datetime="${escape(release.releaseDate)}">${escape(new Intl.DateTimeFormat('fa-IR', {year:'numeric',month:'long',day:'numeric',timeZone:'UTC'}).format(new Date(release.releaseDate+'T12:00:00Z')))}</time><ul>${release.changelog.split(/\r?\n/).map(x=>x.trim()).filter(Boolean).map(line=>`<li>${escape(line)}</li>`).join('')}</ul></article>`).join('')
  app.innerHTML = `<main class="updates-prerender" lang="fa" dir="rtl"><a href="/">اذکار نور</a><h1>تازه‌ها و به‌روزرسانی‌ها</h1><p>${escape(UPDATES_DESCRIPTION)}</p>${cards}<p>برای مشاهدهٔ نسخه‌های قدیمی‌تر، از دکمهٔ «نمایش نسخه‌های قدیمی‌تر» پس از بارگذاری صفحه استفاده کنید.</p><a href="/app">دریافت برنامهٔ اذکار نور</a></main>`
  const style = document.createElement('style')
  style.textContent = '.updates-prerender{max-width:800px;margin:auto;padding:40px 20px;font-family:Tahoma,sans-serif;line-height:2;color:#282c24;background:#f7f6f1}.updates-prerender h1{font-size:clamp(1.6rem,5vw,2.4rem)}.updates-prerender article{background:#fff;border:1px solid #dee3d8;border-radius:20px;padding:24px;margin:20px 0}.updates-prerender h2{font-size:1.2rem}.updates-prerender a{color:#355f30}.updates-prerender time{color:#5c6455}'
  document.head.appendChild(style)
  const schema = document.createElement('script')
  schema.type = 'application/ld+json'
  schema.textContent = JSON.stringify({'@context':'https://schema.org','@type':'CollectionPage',name:UPDATES_TITLE,url:UPDATES_URL,inLanguage:'fa',description:UPDATES_DESCRIPTION}).replace(/</g,'\\u003c')
  document.head.appendChild(schema)
  return '<!doctype html>\n'+document.documentElement.outerHTML
}
