#!/usr/bin/env node
// Generates the crawlable static pages (adhkar, app, about, blog) into dist/ after `vite build`,
// plus the full sitemap. Content comes from the live API; if it is unreachable the committed
// snapshot in content-snapshot/ is used, so a network problem can never break a deploy.
//
//   node scripts/generate-content-pages.mjs            build pages into dist/
//   node scripts/generate-content-pages.mjs --refresh  also rewrite content-snapshot/ from the live API
//   node scripts/generate-content-pages.mjs --dist out use another output directory

import fs from 'fs'
import path from 'path'
import { fileURLToPath } from 'url'

const __dirname = path.dirname(fileURLToPath(import.meta.url))
const ROOT = path.resolve(__dirname, '..')
const args = process.argv.slice(2)
const REFRESH = args.includes('--refresh')
const distArg = args.indexOf('--dist')
const DIST = path.resolve(ROOT, distArg >= 0 ? args[distArg + 1] : 'dist')
const SNAP = path.join(ROOT, 'content-snapshot')

const SITE = 'https://adhkar.ir'
const API = process.env.CONTENT_API_URL || 'https://api.adhkar.ir/api'
const BAZAAR = 'https://cafebazaar.ir/app/ir.adhkar.app'
const MYKET = 'https://myket.ir/app/ir.adhkar.app'
const OG_IMAGE = `${SITE}/images/og-cover.jpg`
const GITHUB = 'https://github.com/edrisranjbar/Nour-Adhkar-App'

// ---------- data ----------

async function fetchJson(url, tries = 2) {
  for (let i = 1; i <= tries; i++) {
    try {
      const res = await fetch(url, { signal: AbortSignal.timeout(20000), headers: { Accept: 'application/json' } })
      if (!res.ok) throw new Error(`HTTP ${res.status}`)
      return await res.json()
    } catch (e) {
      console.warn(`  fetch failed (${i}/${tries}) ${url}: ${e.message}`)
    }
  }
  return null
}

async function fetchAllPosts() {
  const all = []
  for (let page = 1; page <= 20; page++) {
    const json = await fetchJson(`${API}/posts?per_page=100&page=${page}`)
    if (!json || !Array.isArray(json.data)) return null
    all.push(...json.data)
    const last = json.meta && json.meta.last_page ? json.meta.last_page : 1
    if (page >= last) break
  }
  return all
}

function readSnapshot(name) {
  const file = path.join(SNAP, name)
  return fs.existsSync(file) ? JSON.parse(fs.readFileSync(file, 'utf8')) : null
}

async function loadContent() {
  const live = { collections: null, posts: null }
  const col = await fetchJson(`${API}/collections`)
  if (col && Array.isArray(col.collections) && col.collections.length) live.collections = col.collections
  live.posts = await fetchAllPosts()

  const collections = live.collections || readSnapshot('collections.json')
  const posts = live.posts || readSnapshot('posts.json')
  if (!live.collections) console.warn('! Using committed snapshot for collections')
  if (!live.posts) console.warn('! Using committed snapshot for posts')
  if (!collections || !collections.length) throw new Error('No collections available (API down and no snapshot)')
  if (!posts) throw new Error('No posts available (API down and no snapshot)')

  if (REFRESH) {
    fs.mkdirSync(SNAP, { recursive: true })
    if (live.collections) fs.writeFileSync(path.join(SNAP, 'collections.json'), JSON.stringify(live.collections, null, 1))
    if (live.posts) fs.writeFileSync(path.join(SNAP, 'posts.json'), JSON.stringify(live.posts, null, 1))
    console.log('Snapshot refreshed')
  }
  return { collections, posts: posts.filter(p => p.status === 'published' && p.slug && p.title) }
}

// ---------- helpers ----------

const esc = s => String(s ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;')
const toFa = n => String(n).replace(/\d/g, d => '۰۱۲۳۴۵۶۷۸۹'[d])
const faDate = iso => iso ? new Date(iso).toLocaleDateString('fa-IR-u-ca-persian', { year: 'numeric', month: 'long', day: 'numeric' }) : ''
const isoDay = iso => (iso ? new Date(iso) : new Date()).toISOString().slice(0, 10)
const maxDate = list => list.map(x => x && x.updated_at).filter(Boolean).sort().pop()
const clip = (s, n) => (s.length <= n ? s : s.slice(0, n - 1).replace(/\s+\S*$/, '') + '…')
const hasHarakat = s => (s.match(/[ً-ْ]/g) || []).length >= 3

const lines = (v) => v.split(/\n{2,}/)

function formatArticle(content) {
  const out = []
  const clean = r => r.replace(/\p{Extended_Pictographic}️?/gu, '').trim()
  const isHeading = r => r.length <= 60 && !/[.!؛:،«»”)]$/.test(r) && !r.startsWith('•')
  for (const block of lines(String(content || '').trim())) {
    const rows = block.split('\n').map(r => r.trim()).filter(Boolean)
    let i = 0
    if (rows.length && isHeading(rows[0]) && (rows.length > 1 || rows[0].length < 40)) {
      const h = clean(rows[0])
      if (h) out.push(`<h2>${esc(h)}</h2>`)
      i = 1
    }
    let bullets = []
    const flush = () => { if (bullets.length) { out.push('<ul>' + bullets.join('') + '</ul>'); bullets = [] } }
    for (const r of rows.slice(i)) {
      if (r.startsWith('•')) { bullets.push(`<li>${esc(r.replace(/^•\s*/, ''))}</li>`); continue }
      flush()
      if (r.startsWith('«') && hasHarakat(r)) out.push(`<blockquote class="ar" lang="ar" dir="rtl">${esc(r)}</blockquote>`)
      else out.push(`<p>${esc(r)}</p>`)
    }
    flush()
  }
  return out.join('\n')
}

// ---------- layout ----------

const CSS = `
@font-face{font-family:"Vazirmatn FD";src:url(/fonts/vazir/Vazirmatn-FD-Regular.woff2) format("woff2");font-weight:400;font-style:normal;font-display:swap}
@font-face{font-family:"Vazirmatn FD";src:url(/fonts/vazir/Vazirmatn-FD-Medium.woff2) format("woff2");font-weight:500;font-style:normal;font-display:swap}
@font-face{font-family:"Vazirmatn FD";src:url(/fonts/vazir/Vazirmatn-FD-Bold.woff2) format("woff2");font-weight:700;font-style:normal;font-display:swap}
@font-face{font-family:"Othman Taha";src:url(/fonts/othman-taha.ttf) format("truetype");font-weight:400 700;font-style:normal;font-display:swap}
:root{--bg:#f7faf3;--fg:#1d2a1f;--mut:#58665a;--pri:#2f6b3a;--card:#fff;--line:#dfe8da}
@media (prefers-color-scheme:dark){:root{--bg:#141c16;--fg:#e8efe6;--mut:#9fb0a2;--pri:#7cc58a;--card:#1c261e;--line:#2b392e}}
*{box-sizing:border-box}body{margin:0;background:var(--bg);color:var(--fg);font:17px/1.9 "Vazirmatn FD",Vazirmatn,Tahoma,system-ui,sans-serif}
a{color:var(--pri);text-decoration:none}a:hover{opacity:.8}a:focus-visible{outline:2px solid var(--pri);outline-offset:2px;border-radius:4px}.wrap{max-width:46rem;margin:0 auto;padding:0 1rem}
header.top{border-bottom:1px solid var(--line);background:var(--card)}
header.top .wrap{display:flex;align-items:center;gap:1rem;flex-wrap:wrap;padding:.6rem 1rem}
.brand{display:flex;align-items:center;gap:.5rem;font-weight:700;text-decoration:none;color:var(--fg)}
.brand img{border-radius:8px}nav.main{display:flex;gap:1rem;flex-wrap:wrap;margin-inline-start:auto}
nav.main a{text-decoration:none;color:var(--mut)}nav.main a:hover{color:var(--pri)}
main{padding:1.5rem 0 3rem}h1{font-size:1.7rem;line-height:1.5;margin:.3rem 0 .6rem}h2{font-size:1.25rem;margin:1.8rem 0 .5rem}
.crumbs{font-size:.85rem;color:var(--mut)}.crumbs a{color:var(--mut)}.lead{color:var(--mut)}
.item{background:var(--card);border:1px solid var(--line);border-radius:14px;padding:1rem 1.1rem;margin:.9rem 0}
.item h3{margin:0 0 .3rem;font-size:1.05rem}.ar{font:1.55rem/2.3 "Othman Taha","Amiri","Scheherazade New","Traditional Arabic",serif;margin:.4rem 0}
.item p{margin:.3rem 0}.badge{display:inline-block;background:var(--bg);border:1px solid var(--line);border-radius:999px;padding:0 .7rem;font-size:.85rem;color:var(--mut)}
blockquote.ar{border-inline-start:3px solid var(--pri);padding:.2rem 1rem;margin:1rem 0;background:var(--card);border-radius:8px}
.cta{background:var(--pri);color:#fff;border-radius:16px;padding:1.2rem;margin:2rem 0;text-align:center}
.cta a.btn{display:inline-block;background:#fff;color:#1d4a27;border-radius:999px;padding:.35rem 1.1rem;margin:.3rem;text-decoration:none;font-weight:700}
.cards{display:grid;gap:.8rem;grid-template-columns:repeat(auto-fit,minmax(15rem,1fr))}
.cards a{display:block;background:var(--card);border:1px solid var(--line);border-radius:14px;padding:1rem;text-decoration:none;color:var(--fg)}
.cards a strong{display:block;color:var(--pri)}.cards span{color:var(--mut);font-size:.9rem}
footer.foot{border-top:1px solid var(--line);padding:1.2rem 0;color:var(--mut);font-size:.9rem}
footer.foot .wrap{display:flex;gap:1rem;flex-wrap:wrap}footer.foot a{color:var(--mut)}
.meta{color:var(--mut);font-size:.88rem}ul{padding-inline-start:1.3rem}
`

function layout({ title, description, path: urlPath, body, schema = [], ogType = 'website', noindex = false }) {
  const url = SITE + urlPath
  const ld = schema.map(s => `<script type="application/ld+json">${JSON.stringify(s)}</script>`).join('\n')
  return `<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>${esc(title)}</title>
<meta name="description" content="${esc(description)}">
<meta name="robots" content="${noindex ? 'noindex, nofollow' : 'index, follow, max-image-preview:large'}">
<link rel="canonical" href="${url}">
<link rel="icon" type="image/png" sizes="32x32" href="/icons/favicon-32x32.png">
<link rel="apple-touch-icon" sizes="180x180" href="/icons/apple-touch-icon.png">
<meta name="theme-color" content="#2f6b3a">
<link rel="preload" href="/fonts/vazir/Vazirmatn-FD-Regular.woff2" as="font" type="font/woff2" crossorigin>
<meta property="og:type" content="${ogType}">
<meta property="og:site_name" content="اذکار نور">
<meta property="og:locale" content="fa_IR">
<meta property="og:title" content="${esc(title)}">
<meta property="og:description" content="${esc(description)}">
<meta property="og:url" content="${url}">
<meta property="og:image" content="${OG_IMAGE}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="${esc(title)}">
<meta name="twitter:description" content="${esc(description)}">
<meta name="twitter:image" content="${OG_IMAGE}">
<style>${CSS}</style>
${ld}
</head>
<body>
<header class="top"><div class="wrap">
<a class="brand" href="/"><img src="/icons/logo.png" alt="" width="32" height="32"><span>اذکار نور</span></a>
<nav class="main" aria-label="منوی اصلی"><a href="/adhkar">اذکار و ادعیه</a><a href="/blog">مقالات</a><a href="/app">اپلیکیشن</a></nav>
</div></header>
<main><div class="wrap">
${body}
</div></main>
<footer class="foot"><div class="wrap">
<span>© اذکار نور</span><a href="/about">دربارهٔ ما</a><a href="/privacy">حریم خصوصی</a><a href="${GITHUB}" rel="noopener">GitHub</a>
</div></footer>
</body>
</html>
`
}

const crumbs = items => ({
  '@context': 'https://schema.org',
  '@type': 'BreadcrumbList',
  itemListElement: items.map((it, i) => ({ '@type': 'ListItem', position: i + 1, name: it[0], item: SITE + it[1] }))
})
const crumbsHtml = items =>
  `<nav class="crumbs" aria-label="مسیر">${items.map((it, i) => (i < items.length - 1 ? `<a href="${it[1]}">${esc(it[0])}</a>` : esc(it[0]))).join(' › ')}</nav>`

const ctaBlock = (text = 'همین اذکار را با پخش صوتی، شمارنده و یادآور در گوشی خود داشته باشید.') =>
  `<div class="cta"><p>${esc(text)}</p><a class="btn" href="${BAZAAR}" rel="noopener">دریافت از کافه‌بازار</a><a class="btn" href="${MYKET}" rel="noopener">دریافت از مایکت</a></div>`

const publisher = { '@type': 'Organization', name: 'اذکار نور', url: SITE + '/', logo: { '@type': 'ImageObject', url: SITE + '/icons/android-chrome-192x192.png' } }

// ---------- page definitions ----------

const COLLECTION_PAGES = [
  {
    slug: 'morning', path: '/adhkar/sobhgahi', name: 'اذکار صبحگاهی',
    title: 'اذکار صبحگاهی با متن عربی و ترجمه فارسی | اذکار نور',
    h1: 'اذکار صبحگاهی (اذکار صبح) با متن عربی و ترجمه فارسی',
    intro: 'مجموعهٔ اذکار صبحگاهی اهل سنت با متن عربی، ترجمهٔ فارسی و تعداد تکرار هر ذکر. همین اذکار در اپلیکیشن اذکار نور هم با پخش صوتی، شمارنده و یادآور در دسترس است.'
  },
  {
    slug: 'night', path: '/adhkar/shamgahi', name: 'اذکار شامگاهی',
    title: 'اذکار شامگاهی با متن عربی و ترجمه فارسی | اذکار نور',
    h1: 'اذکار شامگاهی (اذکار شب و شام) با متن عربی و ترجمه فارسی',
    intro: 'مجموعهٔ اذکار شامگاهی اهل سنت با متن عربی، ترجمهٔ فارسی و تعداد تکرار هر ذکر. همین اذکار در اپلیکیشن اذکار نور هم با پخش صوتی، شمارنده و یادآور در دسترس است.'
  },
  {
    slug: 'athkar-khoab', path: '/adhkar/qabl-az-khab', name: 'اذکار قبل از خواب',
    title: 'اذکار و دعاهای قبل از خواب با ترجمه فارسی | اذکار نور',
    h1: 'اذکار و دعاهای قبل از خواب با متن عربی و ترجمه فارسی',
    intro: 'اذکار و دعاهای پیش از خواب با متن عربی، ترجمهٔ فارسی و تعداد تکرار. این مجموعه در اپلیکیشن اذکار نور نیز موجود است.'
  },
  {
    slug: 'istikhara', path: '/adhkar/istikhara', name: 'دعای استخاره',
    title: 'دعای استخاره با متن عربی و ترجمه فارسی | اذکار نور',
    h1: 'دعای استخاره با متن عربی و ترجمه فارسی',
    intro: 'متن دعای استخاره با ترجمهٔ فارسی. این دعا در اپلیکیشن اذکار نور نیز در دسترس است.'
  },
  {
    slug: 'athkar-rmdan', path: '/adhkar/ramadan', name: 'اذکار رمضان',
    title: 'اذکار و دعاهای ماه رمضان با ترجمه فارسی | اذکار نور',
    h1: 'اذکار و دعاهای ماه رمضان با متن عربی و ترجمه فارسی',
    intro: 'اذکار و دعاهای رمضان با متن عربی و ترجمهٔ فارسی. این مجموعه در اپلیکیشن اذکار نور نیز موجود است.'
  },
  {
    slug: 'daily', path: '/adhkar/rouzaneh', name: 'اذکار روزانه',
    title: 'اذکار روزانه با متن عربی و ترجمه فارسی | اذکار نور',
    h1: 'اذکار روزانه با متن عربی و ترجمه فارسی',
    intro: 'اذکار روزانه با متن عربی، ترجمهٔ فارسی و تعداد تکرار. این مجموعه در اپلیکیشن اذکار نور نیز موجود است.'
  }
]

const written = [] // { path, lastmod, priority, changefreq }

function writePage(urlPath, html, meta = {}) {
  const dir = path.join(DIST, urlPath)
  fs.mkdirSync(dir, { recursive: true })
  fs.writeFileSync(path.join(dir, 'index.html'), html)
  written.push({ path: urlPath, lastmod: meta.lastmod || isoDay(), priority: meta.priority || '0.6', changefreq: meta.changefreq || 'monthly' })
}

function adhkarItems(list) {
  return list.map((a, i) => {
    const count = Number(a.count) > 1 ? `<span class="badge">تکرار: ${toFa(a.count)} بار</span>` : ''
    return `<article class="item" id="dhikr-${i + 1}">
${a.title ? `<h3>${esc(a.title)}</h3>` : ''}
${a.prefix ? `<p class="meta">${esc(a.prefix)}</p>` : ''}
<p class="ar" lang="ar" dir="rtl">${esc(a.arabic_text)}</p>
${a.translation ? `<p>${esc(a.translation)}</p>` : ''}
${count}
</article>`
  }).join('\n')
}

function buildCollectionPage(def, col, siblings) {
  const list = col.adhkar || []
  const updated = maxDate(list) || col.updated_at
  const desc = clip(`متن کامل ${def.name} اهل سنت به عربی با ترجمهٔ فارسی و تعداد تکرار هر ذکر (${toFa(list.length)} ذکر)؛ نسخهٔ صوتی، شمارنده و یادآور رایگان و آفلاین در اپلیکیشن اذکار نور.`, 155)
  const trail = [['خانه', '/'], ['اذکار و ادعیه', '/adhkar'], [def.name, def.path]]
  const others = siblings.filter(s => s.path !== def.path)
  const body = `${crumbsHtml(trail)}
<h1>${esc(def.h1)}</h1>
<p class="lead">${esc(def.intro)}</p>
<p class="meta">${toFa(list.length)} ذکر · آخرین به‌روزرسانی: ${esc(faDate(updated))}</p>
${adhkarItems(list)}
${ctaBlock()}
<h2>مجموعه‌های دیگر</h2>
<div class="cards">${others.map(o => `<a href="${o.path}"><strong>${esc(o.name)}</strong><span>مشاهدهٔ متن و ترجمه</span></a>`).join('')}<a href="/blog"><strong>مقالات</strong><span>فضیلت و شیوهٔ خواندن اذکار</span></a></div>`
  const schema = [
    crumbs(trail),
    { '@context': 'https://schema.org', '@type': 'WebPage', name: def.title.replace(/ \| اذکار نور$/, ''), url: SITE + def.path, inLanguage: 'fa', description: desc, dateModified: updated, isPartOf: { '@type': 'WebSite', name: 'اذکار نور', url: SITE + '/' }, publisher }
  ]
  writePage(def.path, layout({ title: def.title, description: desc, path: def.path, body, schema }), { lastmod: isoDay(updated), priority: def.slug === 'morning' || def.slug === 'night' ? '0.9' : '0.7' })
}

function buildOverviewPage(morning, night) {
  const p = '/adhkar/sobh-o-sham-ahl-sunnat'
  const title = 'اذکار صبح و شام اهل سنت (صوتی) | اذکار نور'
  const desc = 'اذکار صبح و شام اهل سنت با متن عربی و ترجمهٔ فارسی؛ پخش صوتی با صدای مشاری راشد العفاسی، شمارندهٔ تکرار و یادآور در اپلیکیشن اذکار نور.'
  const trail = [['خانه', '/'], ['اذکار و ادعیه', '/adhkar'], ['اذکار صبح و شام اهل سنت', p]]
  const preview = (list, n) => adhkarItems(list.filter(a => a.arabic_text).slice(0, n))
  const body = `${crumbsHtml(trail)}
<h1>اذکار صبح و شام اهل سنت؛ متن، ترجمه و نسخهٔ صوتی</h1>
<p class="lead">اذکار نور اپلیکیشن جامع اذکار و ادعیهٔ اهل سنت است. اذکار صبح و شام در آن با متن عربی، ترجمهٔ فارسی، شمارندهٔ تکرار، یادآور و پخش صوتی با صدای مشاری راشد العفاسی ارائه می‌شود و بدون اینترنت و بدون تبلیغ کار می‌کند.</p>
<h2>اذکار صبحگاهی</h2>
<p>${toFa((morning.adhkar || []).length)} ذکر با متن عربی و ترجمهٔ فارسی. چند نمونه:</p>
${preview(morning.adhkar || [], 2)}
<p><a href="/adhkar/sobhgahi">مشاهدهٔ همهٔ اذکار صبحگاهی ←</a></p>
<h2>اذکار شامگاهی</h2>
<p>${toFa((night.adhkar || []).length)} ذکر با متن عربی و ترجمهٔ فارسی. چند نمونه:</p>
${preview(night.adhkar || [], 2)}
<p><a href="/adhkar/shamgahi">مشاهدهٔ همهٔ اذکار شامگاهی ←</a></p>
<h2>در اپلیکیشن چه می‌بینید؟</h2>
<ul><li>متن عربی خوانا با ترجمهٔ فارسی و شمارندهٔ تکرار</li><li>پخش صوتی اذکار با صدای مشاری راشد العفاسی</li><li>یادآور صبح و شام با انتخاب روزهای هفته</li><li>اوقات شرعی، قبله‌نما و ذکرشمار</li><li>رابط راست‌به‌چپ به فارسی، عربی و کوردی</li></ul>
${ctaBlock('اذکار نور رایگان است و برای اندروید عرضه می‌شود.')}`
  const schema = [crumbs(trail), { '@context': 'https://schema.org', '@type': 'WebPage', name: 'اذکار صبح و شام اهل سنت', url: SITE + p, inLanguage: 'fa', description: desc, isPartOf: { '@type': 'WebSite', name: 'اذکار نور', url: SITE + '/' }, publisher }]
  writePage(p, layout({ title, description: desc, path: p, body, schema }), { lastmod: isoDay(maxDate([morning, night])), priority: '0.9' })
}

function buildHub(defs, collections) {
  const p = '/adhkar'
  const title = 'اذکار و ادعیه اهل سنت با متن و ترجمه فارسی | اذکار نور'
  const desc = 'مجموعهٔ اذکار صبحگاهی و شامگاهی، اذکار قبل از خواب، دعای استخاره و اذکار رمضان با متن عربی و ترجمهٔ فارسی؛ نسخهٔ صوتی در اپلیکیشن اذکار نور.'
  const trail = [['خانه', '/'], ['اذکار و ادعیه', p]]
  const count = d => { const c = collections.find(x => x.slug === d.slug); return c ? (c.adhkar || []).length : 0 }
  const body = `${crumbsHtml(trail)}
<h1>اذکار و ادعیه اهل سنت</h1>
<p class="lead">متن عربی، ترجمهٔ فارسی و تعداد تکرار اذکار و دعاهای روزانه. هر مجموعه صفحهٔ جداگانه دارد.</p>
<div class="cards">
${defs.map(d => `<a href="${d.path}"><strong>${esc(d.name)}</strong><span>${toFa(count(d))} ذکر</span></a>`).join('\n')}
<a href="/adhkar/sobh-o-sham-ahl-sunnat"><strong>اذکار صبح و شام اهل سنت</strong><span>مرور سریع و نسخهٔ صوتی</span></a>
</div>
<h2>مقالات</h2>
<p>فضیلت ذکر، شیوهٔ خواندن و توضیح دعاهای مهم در <a href="/blog">بخش مقالات</a> آمده است.</p>
${ctaBlock()}`
  const schema = [crumbs(trail), { '@context': 'https://schema.org', '@type': 'CollectionPage', name: 'اذکار و ادعیه', url: SITE + p, inLanguage: 'fa', description: desc, isPartOf: { '@type': 'WebSite', name: 'اذکار نور', url: SITE + '/' } }]
  writePage(p, layout({ title, description: desc, path: p, body, schema }), { lastmod: isoDay(maxDate(collections)), priority: '0.8' })
}

function buildAppPage() {
  const p = '/app'
  const title = 'اپلیکیشن اهل سنت؛ دانلود اذکار نور برای اندروید'
  const desc = 'اذکار نور؛ اپلیکیشن رایگان اذکار و ادعیه اهل سنت برای اندروید: اذکار صبحگاهی و شامگاهی صوتی، اوقات شرعی، قبله‌نما، ذکرشمار؛ آفلاین و بدون تبلیغ.'
  const trail = [['خانه', '/'], ['اپلیکیشن', p]]
  const body = `${crumbsHtml(trail)}
<h1>اذکار نور؛ اپلیکیشن جامع اذکار و ادعیه اهل سنت</h1>
<p class="lead">اذکار صبح و شام، ادعیهٔ قرآنی و دعاهای سنت، اوقات شرعی و ذکرشمار؛ آفلاین و بدون تبلیغ، برای اندروید.</p>
${ctaBlock('اذکار نور رایگان است. از فروشگاه دلخواه خود دریافت کنید.')}
<h2>امکانات</h2>
<ul>
<li><strong>اذکار صبح و شام:</strong> متن عربی خوانا، ترجمهٔ فارسی و شمارندهٔ تکرار؛ پخش صوتی با صدای مشاری راشد العفاسی؛ افزودن به علاقه‌مندی‌ها و اشتراک‌گذاری.</li>
<li><strong>اوقات شرعی:</strong> نماز بعدی و زمان باقی‌مانده، شش وقت روز، محاسبهٔ آفلاین بر اساس موقعیت شما و پخش اذان با انتخاب مؤذن.</li>
<li><strong>چک‌لیست روزانه:</strong> نمازهای پنج‌گانه و اعمال مستحب، نوار پیشرفت و تقویم استمرار عبادت.</li>
<li><strong>ذکرشمار:</strong> ذکرهای آماده و دلخواه، شمارش لمسی و صوتی و تاریخچه.</li>
<li><strong>قرآن کریم، قبله‌نما و یادآورها</strong> و مقالات کوتاه.</li>
<li><strong>سه زبان:</strong> رابط کامل راست‌به‌چپ به فارسی، عربی و کوردی؛ حالت روشن و تاریک.</li>
</ul>
<h2>حریم خصوصی</h2>
<p>پیشرفت و تنظیمات روی گوشی شما می‌ماند، برنامه تبلیغات و ردیاب ندارد و موقعیت فقط با اجازهٔ صریح شما استفاده می‌شود. جزئیات در <a href="/privacy">سیاست حریم خصوصی</a> آمده است.</p>
<h2>بخش‌های وب‌سایت</h2>
<div class="cards"><a href="/adhkar/sobhgahi"><strong>اذکار صبحگاهی</strong><span>متن و ترجمه</span></a><a href="/adhkar/shamgahi"><strong>اذکار شامگاهی</strong><span>متن و ترجمه</span></a><a href="/blog"><strong>مقالات</strong><span>فضیلت و شیوهٔ خواندن</span></a></div>`
  const schema = [
    crumbs(trail),
    {
      '@context': 'https://schema.org', '@type': 'SoftwareApplication', name: 'اذکار نور', alternateName: 'Nour Adhkar', url: SITE + p, image: OG_IMAGE,
      description: desc, applicationCategory: 'LifestyleApplication', operatingSystem: 'Android', inLanguage: ['fa', 'ar', 'ckb'], downloadUrl: BAZAAR,
      offers: { '@type': 'Offer', price: '0', priceCurrency: 'IRR' }, author: { '@type': 'Person', name: 'ادریس رنجبر' }
    }
  ]
  writePage(p, layout({ title, description: desc, path: p, body, schema }), { priority: '0.9' })
}

function buildAboutPage() {
  const p = '/about'
  const title = 'دربارهٔ اذکار نور | سازنده و نحوهٔ تهیهٔ محتوا'
  const desc = 'دربارهٔ اذکار نور، پروژهٔ متن‌باز و رایگان اذکار و ادعیهٔ اهل سنت: سازنده، نحوهٔ تهیه و به‌روزرسانی محتوا و راه گزارش خطا در متن و ترجمه.'
  const trail = [['خانه', '/'], ['دربارهٔ ما', p]]
  const body = `${crumbsHtml(trail)}
<h1>دربارهٔ اذکار نور</h1>
<p class="lead">اذکار نور یک پروژهٔ متن‌باز و غیرانتفاعی برای دسترسی آسان به اذکار و ادعیهٔ اهل سنت به زبان فارسی است.</p>
<h2>سازنده</h2>
<p>طراحی و توسعه: ادریس رنجبر. کد برنامه در <a href="${GITHUB}" rel="noopener">GitHub</a> در دسترس است.</p>
<h2>محتوا چگونه تهیه و به‌روز می‌شود؟</h2>
<p>متن عربی، ترجمهٔ فارسی و تعداد تکرار هر ذکر از یک پایگاه‌داده می‌آید که هم برنامهٔ اندروید و هم این وب‌سایت از آن می‌خوانند؛ بنابراین متن‌ها در هر دو یکسان است. تاریخ آخرین به‌روزرسانی در بالای هر صفحهٔ اذکار نوشته شده است.</p>
<h2>گزارش خطا و پیشنهاد</h2>
<p>اگر اشتباهی در متن، ترجمه یا تعداد تکرار دیدید، از بخش «ارسال بازخورد» در برنامه یا از طریق <a href="${GITHUB}/issues" rel="noopener">GitHub</a> گزارش دهید تا بررسی و اصلاح شود.</p>
<h2>حریم خصوصی</h2>
<p>برنامه تبلیغات و ابزار ردیابی ندارد. جزئیات در <a href="/privacy">سیاست حریم خصوصی</a>.</p>
${ctaBlock()}`
  const schema = [crumbs(trail), { '@context': 'https://schema.org', '@type': 'AboutPage', name: 'دربارهٔ اذکار نور', url: SITE + p, inLanguage: 'fa', description: desc, isPartOf: { '@type': 'WebSite', name: 'اذکار نور', url: SITE + '/' }, publisher }]
  writePage(p, layout({ title, description: desc, path: p, body, schema }), { priority: '0.4', changefreq: 'yearly' })
}

// The privacy policy text lives in the Vue view; read it here so /privacy has real static HTML
// (own title, canonical and content) without keeping a second copy of the policy.
function buildPrivacyPage() {
  const src = fs.readFileSync(path.join(ROOT, 'src/views/PrivacyView.vue'), 'utf8')
  const article = (src.match(/<article class="card">([\s\S]*?)<\/article>/) || [])[1]
  if (!article) throw new Error('PrivacyView.vue: <article class="card"> not found')
  const updated = (article.match(/<p class="updated">([^<]*)<\/p>/) || [])[1] || ''
  const content = article.replace(/<div class="head">[\s\S]*?<\/div>/, '').trim()
  const p = '/privacy'
  const title = 'سیاست حریم خصوصی | اذکار نور'
  const desc = 'سیاست حریم خصوصی اذکار نور: چه اطلاعاتی، چرا و کجا پردازش می‌شود؛ تقریباً همه‌چیز روی گوشی شما می‌ماند و برنامه تبلیغات و ردیاب ندارد.'
  const trail = [['خانه', '/'], ['حریم خصوصی', p]]
  const body = `${crumbsHtml(trail)}
<h1>سیاست حریم خصوصی</h1>
<p class="meta">${updated}</p>
${content}`
  const schema = [crumbs(trail), { '@context': 'https://schema.org', '@type': 'WebPage', name: 'سیاست حریم خصوصی اذکار نور', url: SITE + p, inLanguage: 'fa', description: desc, isPartOf: { '@type': 'WebSite', name: 'اذکار نور', url: SITE + '/' } }]
  writePage(p, layout({ title, description: desc, path: p, body, schema }), { lastmod: '2026-09-28', priority: '0.3', changefreq: 'yearly' })
}

function buildBlog(posts) {
  const sorted = [...posts].sort((a, b) => String(b.published_at).localeCompare(String(a.published_at)))
  const trailIdx = [['خانه', '/'], ['مقالات', '/blog']]
  const idxTitle = 'مقالات اذکار نور | فضیلت و شیوهٔ خواندن اذکار و دعاها'
  const idxDesc = 'مقالات کوتاه دربارهٔ فضیلت ذکر، شیوهٔ خواندن اذکار صبح و شام و توضیح دعاهای مهم اهل سنت، همراه با متن عربی، ترجمهٔ فارسی و منبع حدیث، از اذکار نور.'
  const idxBody = `${crumbsHtml(trailIdx)}
<h1>مقالات</h1>
<p class="lead">نوشته‌های کوتاه دربارهٔ ذکر، دعا و شیوهٔ خواندن آن‌ها.</p>
${sorted.length ? sorted.map(p => `<article class="item"><h3><a href="/blog/${esc(p.slug)}">${esc(p.title)}</a></h3><p>${esc(clip(p.excerpt || p.meta_description || '', 200))}</p><p class="meta">${esc(faDate(p.published_at))}</p></article>`).join('\n') : '<p>به‌زودی مقاله‌ها در این بخش منتشر می‌شود.</p>'}
${ctaBlock()}`
  writePage('/blog', layout({
    title: idxTitle, description: idxDesc, path: '/blog', body: idxBody,
    schema: [crumbs(trailIdx), { '@context': 'https://schema.org', '@type': 'Blog', name: 'مقالات اذکار نور', url: SITE + '/blog', inLanguage: 'fa', publisher }]
  }), { lastmod: isoDay(maxDate(posts)), priority: '0.7', changefreq: 'weekly' })

  for (const post of sorted) {
    const p = `/blog/${post.slug}`
    const baseTitle = (post.meta_title && post.meta_title !== 'اذکار نور' ? post.meta_title : post.title).replace(/\s*\|\s*اذکار نور\s*$/, '')
    const title = clip(baseTitle, 58) + ' | اذکار نور'
    const desc = clip(post.meta_description || post.excerpt || post.title, 155)
    const trail = [['خانه', '/'], ['مقالات', '/blog'], [post.title, p]]
    const more = sorted.filter(x => x.slug !== post.slug).slice(0, 3)
    const body = `${crumbsHtml(trail)}
<article>
<h1>${esc(post.title)}</h1>
<p class="meta">منتشر شده: ${esc(faDate(post.published_at))}${post.updated_at && post.updated_at !== post.published_at ? ` · به‌روزرسانی: ${esc(faDate(post.updated_at))}` : ''}${post.user && post.user.name ? ` · نویسنده: ${esc(post.user.name)}` : ''}</p>
${formatArticle(post.content)}
</article>
${ctaBlock()}
${more.length ? `<h2>مقالات دیگر</h2><div class="cards">${more.map(m => `<a href="/blog/${esc(m.slug)}"><strong>${esc(m.title)}</strong><span>${esc(faDate(m.published_at))}</span></a>`).join('')}</div>` : ''}
<h2>اذکار</h2>
<p><a href="/adhkar/sobhgahi">اذکار صبحگاهی</a> · <a href="/adhkar/shamgahi">اذکار شامگاهی</a> · <a href="/adhkar">همهٔ اذکار و ادعیه</a></p>`
    const schema = [crumbs(trail), {
      '@context': 'https://schema.org', '@type': 'Article', headline: clip(post.title, 110), description: desc, inLanguage: 'fa',
      datePublished: post.published_at, dateModified: post.updated_at || post.published_at,
      author: { '@type': 'Person', name: (post.user && post.user.name) || 'ادریس رنجبر' }, publisher,
      mainEntityOfPage: { '@type': 'WebPage', '@id': SITE + p }, image: [OG_IMAGE]
    }]
    writePage(p, layout({ title, description: desc, path: p, body, schema, ogType: 'article' }), { lastmod: isoDay(post.updated_at || post.published_at), priority: '0.6' })
  }
}

function writeSitemap() {
  const fixed = [{ path: '/', lastmod: isoDay(), priority: '1.0', changefreq: 'monthly' }]
  const all = [...fixed, ...written].sort((a, b) => parseFloat(b.priority) - parseFloat(a.priority))
  const xml = `<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
${all.map(u => `  <url>
    <loc>${SITE}${u.path === '/' ? '/' : u.path}</loc>
    <lastmod>${u.lastmod}</lastmod>
    <changefreq>${u.changefreq}</changefreq>
    <priority>${u.priority}</priority>
  </url>`).join('\n')}
</urlset>
`
  fs.writeFileSync(path.join(DIST, 'sitemap.xml'), xml)
  return all.length
}

// ---------- run ----------

if (!fs.existsSync(DIST)) throw new Error(`dist directory not found: ${DIST} (run vite build first)`)
const { collections, posts } = await loadContent()
const bySlug = Object.fromEntries(collections.map(c => [c.slug, c]))
const defs = COLLECTION_PAGES.filter(d => bySlug[d.slug] && (bySlug[d.slug].adhkar || []).length)
if (!bySlug.morning || !bySlug.night) throw new Error('morning/night collections missing')

for (const d of defs) buildCollectionPage(d, bySlug[d.slug], defs)
buildOverviewPage(bySlug.morning, bySlug.night)
buildHub(defs, collections)
buildAppPage()
buildAboutPage()
buildPrivacyPage()
buildBlog(posts)
const total = writeSitemap()
console.log(`Content pages generated: ${written.length} pages (${posts.length} articles); sitemap has ${total} URLs`)
