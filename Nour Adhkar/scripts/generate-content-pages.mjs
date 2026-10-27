#!/usr/bin/env node
// Generates the crawlable static pages (adhkar, app, about, blog) into dist/ after `vite build`,
// in Persian (root), Arabic (/ar) and Kurdish (/ckb); page text is in content-i18n.mjs,
// plus the full sitemap. Content comes from the live API; if it is unreachable the committed
// snapshot in content-snapshot/ is used, so a network problem can never break a deploy.
//
//   node scripts/generate-content-pages.mjs            build pages into dist/
//   node scripts/generate-content-pages.mjs --refresh  also rewrite content-snapshot/ from the live API
//   node scripts/generate-content-pages.mjs --dist out use another output directory

import fs from 'fs'
import path from 'path'
import { fileURLToPath, pathToFileURL } from 'url'
import { LANGS, LOCALE, T, COLLECTION_TEXT, num, fmtDate, isPersianSpelling } from './content-i18n.mjs'

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
.langs{display:flex;gap:2px;border:1px solid var(--line);border-radius:999px;padding:2px}.langs a{color:var(--mut);padding:0 .6rem;border-radius:999px;font-size:.9rem;line-height:1.9}.langs a[aria-current]{background:var(--pri);color:var(--card)}
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

// Path of a page in a language: Persian at the root, Arabic under /ar, Kurdish under /ckb.
// The home page is the landing page for every language (it switches language itself).
const lp = (lang, p) => (lang === 'fa' || p === '/' ? p : `/${lang}${p}`)
const allLangs = p => Object.fromEntries(LANGS.map(l => [l, lp(l, p)]))

function layout({ lang = 'fa', title, description, path: urlPath, alts = { fa: urlPath }, missing = '/adhkar', body, schema = [], ogType = 'website', noindex = false }) {
  const t = T[lang]
  const url = SITE + urlPath
  const ld = schema.map(s => `<script type="application/ld+json">${JSON.stringify(s).replace(/</g, '\\u003c')}</script>`).join('\n')
  const altLinks = Object.keys(alts).length > 1
    ? Object.entries(alts).map(([l, p]) => `<link rel="alternate" hreflang="${l}" href="${SITE + p}">`).join('\n') +
      (alts.fa ? `\n<link rel="alternate" hreflang="x-default" href="${SITE + alts.fa}">` : '')
    : ''
  const names = { fa: 'فا', ar: 'ع', ckb: 'کو' }
  const titles = { fa: 'فارسی', ar: 'العربية', ckb: 'کوردی' }
  // Languages without this page link to the nearest page they do have (`missing`).
  const fallback = { fa: '/', ar: lp('ar', missing), ckb: lp('ckb', missing) }
  const switcher = LANGS.map(l => {
    const href = alts[l] || fallback[l]
    return l === lang
      ? `<a href="${href}" lang="${l}" title="${titles[l]}" aria-current="true" data-lang="${l}">${names[l]}</a>`
      : `<a href="${href}" lang="${l}" hreflang="${l}" title="${titles[l]}" data-lang="${l}">${names[l]}</a>`
  }).join('')
  return `<!DOCTYPE html>
<html lang="${lang}" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>${esc(title)}</title>
<meta name="description" content="${esc(description)}">
<meta name="robots" content="${noindex ? 'noindex, nofollow' : 'index, follow, max-image-preview:large'}">
<link rel="canonical" href="${url}">
${altLinks}
<link rel="icon" type="image/png" sizes="32x32" href="/icons/favicon-32x32.png">
<link rel="apple-touch-icon" sizes="180x180" href="/icons/apple-touch-icon.png">
<meta name="theme-color" content="#2f6b3a">
<link rel="preload" href="/fonts/vazir/Vazirmatn-FD-Regular.woff2" as="font" type="font/woff2" crossorigin>
<meta property="og:type" content="${ogType}">
<meta property="og:site_name" content="${esc(t.siteName)}">
<meta property="og:locale" content="${LOCALE[lang]}">
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
<a class="brand" href="/" data-lang="${lang}"><img src="/icons/logo.png" alt="" width="32" height="32"><span>${esc(t.siteName)}</span></a>
<nav class="main" aria-label="${esc(t.mainNav)}"><a href="${lp(lang, '/adhkar')}">${esc(t.adhkar)}</a><a href="${lp(lang, '/blog')}">${esc(t.blog)}</a><a href="${lp(lang, '/app')}">${esc(t.app)}</a></nav>
<nav class="langs" aria-label="${esc(t.langLabel)}">${switcher}</nav>
</div></header>
<main><div class="wrap">
${body}
</div></main>
<footer class="foot"><div class="wrap">
<span>© ${esc(t.siteName)}</span><a href="${lp(lang, '/about')}">${esc(t.about)}</a><a href="/privacy">${esc(t.privacy)}</a><a href="${GITHUB}" rel="noopener">GitHub</a>
</div></footer>
<script>document.addEventListener("click",function(e){var a=e.target.closest&&e.target.closest("a[data-lang]");if(a)try{localStorage.setItem("nour-lang",a.getAttribute("data-lang"))}catch(_){}})</script>
</body>
</html>
`
}

const crumbs = items => ({
  '@context': 'https://schema.org',
  '@type': 'BreadcrumbList',
  itemListElement: items.map((it, i) => ({ '@type': 'ListItem', position: i + 1, name: it[0], item: SITE + it[1] }))
})
const crumbsHtml = (lang, items) =>
  `<nav class="crumbs" aria-label="${esc(T[lang].crumbsLabel)}">${items.map((it, i) => (i < items.length - 1 ? `<a href="${it[1]}">${esc(it[0])}</a>` : esc(it[0]))).join(' › ')}</nav>`

const ctaBlock = (lang, text = T[lang].cta) =>
  `<div class="cta"><p>${esc(text)}</p><a class="btn" href="${BAZAAR}" rel="noopener">${esc(T[lang].bazaar)}</a><a class="btn" href="${MYKET}" rel="noopener">${esc(T[lang].myket)}</a></div>`

const publisher = lang => ({ '@type': 'Organization', name: T[lang].siteName, url: SITE + '/', logo: { '@type': 'ImageObject', url: SITE + '/icons/android-chrome-192x192.png' } })
const website = lang => ({ '@type': 'WebSite', name: T[lang].siteName, url: SITE + '/' })

// ---------- page definitions ----------

const COLLECTION_PAGES = [
  {
    slug: 'morning', path: '/adhkar/sobhgahi', name: 'اذکار صبحگاهی',
    title: 'اذکار صبحگاهی اهل سنت با متن و ترجمه فارسی | اذکار نور',
    h1: 'اذکار صبحگاهی اهل سنت با متن عربی و ترجمه فارسی',
    intro: 'مجموعهٔ اذکار صبحگاهی اهل سنت با متن عربی، ترجمهٔ فارسی و تعداد تکرار هر ذکر. همین اذکار در اپلیکیشن اذکار نور هم با پخش صوتی، شمارنده و یادآور در دسترس است.'
  },
  {
    slug: 'night', path: '/adhkar/shamgahi', name: 'اذکار شامگاهی',
    title: 'اذکار شامگاهی اهل سنت با متن و ترجمه فارسی | اذکار نور',
    h1: 'اذکار شامگاهی اهل سنت با متن عربی و ترجمه فارسی',
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

// A collection's text in a language: the Persian page copy above, or COLLECTION_TEXT.
function collectionText(def, lang) {
  if (lang === 'fa') return def
  const x = COLLECTION_TEXT[def.slug][lang]
  return { ...def, name: x.name, h1: x.h1, intro: x.intro, title: `${x.name} | ${T[lang].siteName}` }
}

const written = [] // { path, lastmod, priority, changefreq }

function writePage(urlPath, html, meta = {}) {
  const dir = path.join(DIST, urlPath)
  fs.mkdirSync(dir, { recursive: true })
  fs.writeFileSync(path.join(dir, 'index.html'), html)
  written.push({ path: urlPath, lastmod: meta.lastmod, priority: meta.priority || '0.6', changefreq: meta.changefreq || 'monthly' })
}

// Persian pages show each dhikr's title and Persian meaning. Arabic and Kurdish pages show the
// Arabic text only (as the app does): there are no sourced meanings in those languages, and a
// title in Persian spelling is left out.
function adhkarItems(list, lang = 'fa') {
  const t = T[lang]
  return list.map((a, i) => {
    const count = Number(a.count) > 1 ? `<span class="badge">${esc(t.repeat(a.count))}</span>` : ''
    const title = a.title && (lang === 'fa' || !isPersianSpelling(a.title)) ? `<h3>${esc(a.title)}</h3>` : ''
    return `<article class="item" id="dhikr-${i + 1}">
${title}
${a.prefix ? `<p class="meta" lang="ar">${esc(a.prefix)}</p>` : ''}
<p class="ar" lang="ar" dir="rtl">${esc(a.arabic_text)}</p>
${lang === 'fa' && a.translation ? `<p>${esc(a.translation)}</p>` : ''}
${count}
</article>`
  }).join('\n')
}

function buildCollectionPage(base, col, siblings, lang) {
  const t = T[lang]
  const def = collectionText(base, lang)
  const p = lp(lang, def.path)
  const list = col.adhkar || []
  const updated = maxDate(list) || col.updated_at
  const desc = clip(lang === 'fa' ? t.collectionDesc(def.name, toFa(list.length)) : t.collectionDesc(def.name, num(lang, list.length)), 155)
  const trail = [[t.home, '/'], [t.adhkar, lp(lang, '/adhkar')], [def.name, p]]
  const others = siblings.filter(s => s.path !== base.path).map(s => collectionText(s, lang))
  const body = `${crumbsHtml(lang, trail)}
<h1>${esc(def.h1)}</h1>
<p class="lead">${esc(def.intro)}</p>
${(base.slug === 'morning' || base.slug === 'night') ? `<p><a href="${lp(lang, '/app')}">${esc(t.appCta)}</a></p>` : ''}
<p class="meta">${esc(t.dhikrCount(list.length))} · ${esc(t.updated(fmtDate(lang, updated)))}</p>
${adhkarItems(list, lang)}
${ctaBlock(lang)}
<h2>${esc(t.otherCollections)}</h2>
<div class="cards">${others.map(o => `<a href="${lp(lang, o.path)}"><strong>${esc(o.name)}</strong><span>${esc(t.viewText)}</span></a>`).join('')}<a href="${lp(lang, '/blog')}"><strong>${esc(t.blog)}</strong><span>${esc(t.blogCardSub)}</span></a></div>`
  const schema = [
    crumbs(trail),
    { '@context': 'https://schema.org', '@type': 'WebPage', name: def.title.replace(/ \| [^|]+$/, ''), url: SITE + p, inLanguage: lang, description: desc, dateModified: updated, isPartOf: website(lang), publisher: publisher(lang) }
  ]
  writePage(p, layout({ lang, title: def.title, description: desc, path: p, alts: allLangs(base.path), body, schema }), { lastmod: isoDay(updated), priority: base.slug === 'morning' || base.slug === 'night' ? (lang === 'fa' ? '0.9' : '0.7') : (lang === 'fa' ? '0.7' : '0.5') })
}

// Persian-only SEO page (Persian meanings are its point).
function buildOverviewPage(morning, night) {
  const lang = 'fa'
  const p = '/adhkar/sobh-o-sham-ahl-sunnat'
  const title = 'اذکار صبح و شام اهل سنت (صوتی) | اذکار نور'
  const desc = 'اذکار صبح و شام اهل سنت با متن عربی و ترجمهٔ فارسی؛ پخش صوتی با صدای مشاری راشد العفاسی، شمارندهٔ تکرار و یادآور در اپلیکیشن اذکار نور.'
  const trail = [['خانه', '/'], ['اذکار و ادعیه', '/adhkar'], ['اذکار صبح و شام اهل سنت', p]]
  const preview = (list, n) => adhkarItems(list.filter(a => a.arabic_text).slice(0, n))
  const body = `${crumbsHtml(lang, trail)}
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
${ctaBlock(lang, 'اذکار نور رایگان است و برای اندروید عرضه می‌شود.')}`
  const schema = [crumbs(trail), { '@context': 'https://schema.org', '@type': 'WebPage', name: 'اذکار صبح و شام اهل سنت', url: SITE + p, inLanguage: 'fa', description: desc, isPartOf: website(lang), publisher: publisher(lang) }]
  writePage(p, layout({ lang, title, description: desc, path: p, body, schema }), { lastmod: isoDay(maxDate([morning, night])), priority: '0.9' })
}

function buildHub(defs, collections, lang) {
  const t = T[lang]
  const p = lp(lang, '/adhkar')
  const trail = [[t.home, '/'], [t.adhkar, p]]
  const count = d => { const c = collections.find(x => x.slug === d.slug); return c ? (c.adhkar || []).length : 0 }
  const overview = t.overviewCard ? `\n<a href="/adhkar/sobh-o-sham-ahl-sunnat"><strong>${esc(t.overviewCard[0])}</strong><span>${esc(t.overviewCard[1])}</span></a>` : ''
  const body = `${crumbsHtml(lang, trail)}
<h1>${esc(t.hubH1)}</h1>
<p class="lead">${esc(t.hubLead)}</p>
<div class="cards">
${defs.map(d => collectionText(d, lang)).map(d => `<a href="${lp(lang, d.path)}"><strong>${esc(d.name)}</strong><span>${esc(t.dhikrCount(count(d)))}</span></a>`).join('\n')}${overview}
</div>
<h2>${esc(t.blog)}</h2>
<p>${t.hubBlog.replace('{blog}', lp(lang, '/blog'))}</p>
${ctaBlock(lang)}`
  const schema = [crumbs(trail), { '@context': 'https://schema.org', '@type': 'CollectionPage', name: t.adhkar, url: SITE + p, inLanguage: lang, description: t.hubDesc, isPartOf: website(lang) }]
  writePage(p, layout({ lang, title: t.hubTitle, description: t.hubDesc, path: p, alts: allLangs('/adhkar'), body, schema }), { lastmod: isoDay(maxDate(collections)), priority: lang === 'fa' ? '0.8' : '0.6' })
}

function buildAppPage(lang) {
  const t = T[lang]
  const p = lp(lang, '/app')
  const trail = [[t.home, '/'], [t.app, p]]
  const morning = collectionText(COLLECTION_PAGES[0], lang)
  const night = collectionText(COLLECTION_PAGES[1], lang)
  const body = `${crumbsHtml(lang, trail)}
<h1>${esc(t.appH1)}</h1>
<p class="lead">${esc(t.appLead)}</p>
${ctaBlock(lang, t.appCta)}
<h2>${esc(t.features)}</h2>
<ul>
${t.appFeatures.map(([k, v]) => `<li><strong>${esc(k)}</strong> ${esc(v)}</li>`).join('\n')}
</ul>
<h2>${esc(t.privacy)}</h2>
<p>${t.appPrivacy}</p>
<h2>${esc(t.siteSections)}</h2>
<div class="cards"><a href="${lp(lang, morning.path)}"><strong>${esc(morning.name)}</strong><span>${esc(t.textAndMeaning)}</span></a><a href="${lp(lang, night.path)}"><strong>${esc(night.name)}</strong><span>${esc(t.textAndMeaning)}</span></a><a href="${lp(lang, '/blog')}"><strong>${esc(t.blog)}</strong><span>${esc(t.readingVirtue)}</span></a></div>`
  const schema = [
    crumbs(trail),
    {
      '@context': 'https://schema.org', '@type': 'SoftwareApplication', name: t.siteName, alternateName: 'Nour Adhkar', url: SITE + p, image: OG_IMAGE,
      description: t.appDesc, applicationCategory: 'LifestyleApplication', operatingSystem: 'Android', inLanguage: ['fa', 'ar', 'ckb'], downloadUrl: BAZAAR,
      offers: { '@type': 'Offer', price: '0', priceCurrency: 'IRR' }, author: { '@type': 'Person', name: 'ادریس رنجبر' }
    }
  ]
  writePage(p, layout({ lang, title: t.appTitle, description: t.appDesc, path: p, alts: allLangs('/app'), body, schema }), { priority: lang === 'fa' ? '0.9' : '0.7' })
}

function buildAboutPage(lang) {
  const t = T[lang]
  const p = lp(lang, '/about')
  const trail = [[t.home, '/'], [t.about, p]]
  const body = `${crumbsHtml(lang, trail)}
<h1>${esc(t.aboutH1)}</h1>
<p class="lead">${esc(t.aboutLead)}</p>
${t.aboutSections.map(([h, html]) => `<h2>${esc(h)}</h2>\n<p>${html.replace(/\{github\}/g, GITHUB)}</p>`).join('\n')}
${ctaBlock(lang)}`
  const schema = [crumbs(trail), { '@context': 'https://schema.org', '@type': 'AboutPage', name: t.aboutH1, url: SITE + p, inLanguage: lang, description: t.aboutDesc, isPartOf: website(lang), publisher: publisher(lang) }]
  writePage(p, layout({ lang, title: t.aboutTitle, description: t.aboutDesc, path: p, alts: allLangs('/about'), body, schema }), { priority: '0.4', changefreq: 'yearly' })
}

// The privacy policy text lives in the Vue view; read it here so /privacy has real static HTML
// (own title, canonical and content) without keeping a second copy of the policy. Persian only.
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
  const body = `${crumbsHtml('fa', trail)}
<h1>سیاست حریم خصوصی</h1>
<p class="meta">${updated}</p>
${content}`
  const schema = [crumbs(trail), { '@context': 'https://schema.org', '@type': 'WebPage', name: 'سیاست حریم خصوصی اذکار نور', url: SITE + p, inLanguage: 'fa', description: desc, isPartOf: website('fa') }]
  writePage(p, layout({ title, description: desc, path: p, body, schema }), { lastmod: '2026-09-28', priority: '0.3', changefreq: 'yearly' })
}

// Articles exist in Persian (API) and, for some, in Arabic (content-snapshot/posts-ar.json, the
// same translations the app shows). A language gets an article page only if it has the text;
// the blog index lists the rest as Persian articles.
function postIn(post, lang) {
  if (lang === 'fa') return post
  const x = lang === 'ar' && POSTS_AR[post.slug]
  return x ? { ...post, title: x.title, excerpt: x.excerpt, meta_title: null, meta_description: x.excerpt, content: x.content, user: null } : null
}

function buildBlog(posts, lang, emit = writePage) {
  const t = T[lang]
  const sorted = [...posts].sort((a, b) => String(b.published_at).localeCompare(String(a.published_at)))
  const own = sorted.map(p => postIn(p, lang)).filter(Boolean)
  const persianOnly = lang === 'fa' ? [] : sorted.filter(p => !postIn(p, lang))
  const idx = lp(lang, '/blog')
  const trailIdx = [[t.home, '/'], [t.blog, idx]]
  const card = (p, l) => `<article class="item"${l === 'fa' && lang !== 'fa' ? ' lang="fa"' : ''}><h3><a href="${lp(l, '/blog/' + p.slug)}">${esc(p.title)}</a></h3><p>${esc(clip(p.excerpt || p.meta_description || '', 200))}</p><p class="meta">${esc(fmtDate(l, p.published_at))}</p></article>`
  const idxBody = `${crumbsHtml(lang, trailIdx)}
<h1>${esc(t.blog)}</h1>
<p class="lead">${esc(t.blogLead)}</p>
${own.length ? own.map(p => card(p, lang)).join('\n') : persianOnly.length ? '' : `<p>${esc(t.blogEmpty)}</p>`}
${persianOnly.length ? `<h2>${esc(t.blogOnlyFa)}</h2>\n${persianOnly.map(p => card(p, 'fa')).join('\n')}` : ''}
${ctaBlock(lang)}`
  emit(idx, layout({
    lang, title: t.blogTitle, description: t.blogDesc, path: idx, alts: allLangs('/blog'), body: idxBody,
    schema: [crumbs(trailIdx), { '@context': 'https://schema.org', '@type': 'Blog', name: t.blogTitle.replace(/ \| .*$/, ''), url: SITE + idx, inLanguage: lang, publisher: publisher(lang) }]
  }), { lastmod: isoDay(maxDate(posts)), priority: lang === 'fa' ? '0.7' : '0.5', changefreq: 'weekly' })

  for (const post of own) {
    const p = lp(lang, `/blog/${post.slug}`)
    const baseTitle = (post.meta_title && post.meta_title !== 'اذکار نور' ? post.meta_title : post.title).replace(/\s*\|\s*اذکار نور\s*$/, '')
    const title = clip(baseTitle, 58) + ` | ${t.siteName}`
    const desc = clip(post.meta_description || post.excerpt || post.title, 155)
    const trail = [[t.home, '/'], [t.blog, idx], [post.title, p]]
    const more = own.filter(x => x.slug !== post.slug).slice(0, 3)
    const alts = Object.fromEntries(LANGS.filter(l => postIn(post, l)).map(l => [l, lp(l, `/blog/${post.slug}`)]))
    const morning = collectionText(COLLECTION_PAGES[0], lang)
    const night = collectionText(COLLECTION_PAGES[1], lang)
    const meta = [t.published(fmtDate(lang, post.published_at))]
    if (post.updated_at && fmtDate(lang, post.updated_at) !== fmtDate(lang, post.published_at)) meta.push(t.updatedOn(fmtDate(lang, post.updated_at)))
    if (post.user && post.user.name) meta.push(t.author(post.user.name))
    const body = `${crumbsHtml(lang, trail)}
<article>
<h1>${esc(post.title)}</h1>
<p class="meta">${esc(meta.join(' · '))}</p>
${formatArticle(post.content)}
</article>
${ctaBlock(lang)}
${more.length ? `<h2>${esc(t.moreArticles)}</h2><div class="cards">${more.map(m => `<a href="${lp(lang, '/blog/' + m.slug)}"><strong>${esc(m.title)}</strong><span>${esc(fmtDate(lang, m.published_at))}</span></a>`).join('')}</div>` : ''}
<h2>${esc(t.adhkarLinksTitle)}</h2>
<p><a href="${lp(lang, morning.path)}">${esc(morning.name)}</a> · <a href="${lp(lang, night.path)}">${esc(night.name)}</a> · <a href="${lp(lang, '/adhkar')}">${esc(t.allAdhkar)}</a></p>`
    const schema = [crumbs(trail), {
      '@context': 'https://schema.org', '@type': 'Article', headline: clip(post.title, 110), description: desc, inLanguage: lang,
      datePublished: post.published_at, dateModified: post.updated_at || post.published_at,
      author: { '@type': 'Person', name: (post.user && post.user.name) || 'ادریس رنجبر' }, publisher: publisher(lang),
      mainEntityOfPage: { '@type': 'WebPage', '@id': SITE + p }, image: [OG_IMAGE]
    }]
    emit(p, layout({ lang, title, description: desc, path: p, alts, missing: '/blog', body, schema, ogType: 'article' }), { lastmod: isoDay(post.updated_at || post.published_at), priority: lang === 'fa' ? '0.6' : '0.5' })
  }
}

function writeSitemap() {
  const fixed = [
    { path: '/', priority: '1.0', changefreq: 'monthly' },
    { path: '/updates', priority: '0.6', changefreq: 'weekly' }
  ]
  const all = [...fixed, ...written].sort((a, b) => parseFloat(b.priority) - parseFloat(a.priority))
  const xml = `<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
${all.map(u => `  <url>
    <loc>${SITE}${u.path === '/' ? '/' : u.path}</loc>
    ${u.lastmod ? `<lastmod>${u.lastmod}</lastmod>` : ''}
    <changefreq>${u.changefreq}</changefreq>
    <priority>${u.priority}</priority>
  </url>`).join('\n')}
</urlset>
`
  fs.writeFileSync(path.join(DIST, 'sitemap.xml'), xml)
  return all.length
}

// The background publisher uses the same renderer as the initial build, including translations.
const POSTS_AR = readSnapshot('posts-ar.json') || {}

export function renderBlogPages(posts) {
  const pages = []
  for (const lang of LANGS) buildBlog(posts, lang, (urlPath, html, meta) => pages.push({ path: urlPath, html, ...meta }))
  return pages
}

// ---------- build entry point (importing the renderer never writes files or calls the API) ----------

async function main() {

if (!fs.existsSync(DIST)) throw new Error(`dist directory not found: ${DIST} (run vite build first)`)
const { collections, posts } = await loadContent()
const bySlug = Object.fromEntries(collections.map(c => [c.slug, c]))
const defs = COLLECTION_PAGES.filter(d => bySlug[d.slug] && (bySlug[d.slug].adhkar || []).length)
if (!bySlug.morning || !bySlug.night) throw new Error('morning/night collections missing')

for (const lang of LANGS) {
  for (const d of defs) buildCollectionPage(d, bySlug[d.slug], defs, lang)
  buildHub(defs, collections, lang)
  buildAppPage(lang)
  buildAboutPage(lang)
  buildBlog(posts, lang)
}
buildOverviewPage(bySlug.morning, bySlug.night)
buildPrivacyPage()
fs.mkdirSync(path.join(DIST, 'updates'), { recursive: true })
// Build-only DOM dependency: the server's article renderer imports this module without npm.
const { updatesDocument } = await import('./updates-document.mjs')
const versions = await fetchJson(`${API}/app-versions`)
fs.writeFileSync(path.join(DIST, 'updates', 'index.html'), updatesDocument(fs.readFileSync(path.join(DIST, 'index.html'), 'utf8'), Array.isArray(versions?.data) ? versions.data : []))
const total = writeSitemap()
console.log(`Content pages generated: ${written.length} pages in ${LANGS.length} languages (${posts.length} articles); sitemap has ${total} URLs`)
}

if (process.argv[1] && import.meta.url === pathToFileURL(path.resolve(process.argv[1])).href) await main()
