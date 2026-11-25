#!/usr/bin/env node
// Generate crawlable Persian pages for every lecture and scholar in the public catalog.
// Audio remains served from the first-party adhkar.ir API origin.

import fs from 'node:fs'
import path from 'node:path'
import { fileURLToPath } from 'node:url'

const ROOT = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..')
const DIST = path.join(ROOT, 'dist')
const SNAPSHOT = path.join(ROOT, 'content-snapshot', 'lectures-public.json')
const SITE = 'https://adhkar.ir'
const API = process.env.CONTENT_API_URL || 'https://api.adhkar.ir/api'
const AUDIO_ORIGIN = 'https://api.adhkar.ir'

const esc = value => String(value ?? '').replace(/[&<>"']/g, ch => ({
  '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
}[ch]))
const safeJson = value => JSON.stringify(value).replace(/</g, '\\u003c')
const routeForScholar = scholar => '/lectures/' + scholar.id
const routeForLecture = (scholar, lecture) => routeForScholar(scholar) + '/' + lecture.id
const photoForScholar = scholar => {
  try {
    const photo = new URL(scholar.photoUrl)
    return photo.origin === 'https://api.adhkar.ir' && photo.pathname.startsWith('/storage/scholars/')
      ? photo.href
      : null
  } catch {
    return null
  }
}

async function loadCatalog() {
  try {
    const response = await fetch(API + '/scholars', {
      signal: AbortSignal.timeout(20000),
      headers: { Accept: 'application/json' }
    })
    if (!response.ok) throw new Error('API returned HTTP ' + response.status)
    const payload = await response.json()
    if (!Array.isArray(payload?.data)) throw new Error('Public API returned an invalid catalog')
    return payload.data
  } catch (error) {
    if (!fs.existsSync(SNAPSHOT)) throw new Error('Unable to load lecture catalog (' + error.message + ') and no snapshot exists')
    console.warn('! Using committed public lecture catalog snapshot (' + error.message + ')')
    const payload = JSON.parse(fs.readFileSync(SNAPSHOT, 'utf8').replace(/^\uFEFF/, ''))
    if (!Array.isArray(payload?.data)) throw new Error('Public lecture snapshot is invalid')
    return payload.data
  }
}

function validateCatalog(scholars) {
  const scholarIds = new Set()
  let total = 0
  for (const scholar of scholars) {
    if (!/^[A-Za-z0-9_-]+$/.test(String(scholar.id ?? '')) || scholarIds.has(scholar.id)) {
      throw new Error('Invalid or duplicate scholar id: ' + scholar.id)
    }
    scholarIds.add(scholar.id)
    if (!scholar.name?.trim()) throw new Error('Scholar ' + scholar.id + ' needs a name')
    if (!Array.isArray(scholar.lectures)) throw new Error('Scholar ' + scholar.id + ' has no lecture list')
    const lectureIds = new Set()
    for (const lecture of scholar.lectures) {
      const id = String(lecture.id ?? '')
      if (!/^[A-Za-z0-9_-]+$/.test(id) || lectureIds.has(id)) throw new Error('Invalid or duplicate lecture id for ' + scholar.id + ': ' + id)
      lectureIds.add(id)
      if (!lecture.title?.trim() || !lecture.description?.trim()) throw new Error('Lecture ' + scholar.id + '/' + id + ' needs a title and description')
      let audio
      try { audio = new URL(lecture.audioUrl) } catch { throw new Error('Lecture ' + scholar.id + '/' + id + ' has an invalid audio URL') }
      if (audio.protocol !== 'https:' || audio.origin !== AUDIO_ORIGIN || !audio.pathname.startsWith('/storage/lectures/')) {
        throw new Error('Lecture ' + scholar.id + '/' + id + ' audio must remain on ' + AUDIO_ORIGIN)
      }
      if (!audio.pathname.toLowerCase().endsWith('.mp3')) throw new Error('Lecture ' + scholar.id + '/' + id + ' is not an MP3 file')
      total++
    }
  }
  if (!scholars.length || !total) throw new Error('Public lecture catalog is empty')
  return total
}

const CSS = '@font-face{font-family:Vazirmatn;src:url(/fonts/vazir/Vazirmatn-FD-Regular.woff2) format("woff2");font-weight:400;font-display:swap}' +
  '@font-face{font-family:Vazirmatn;src:url(/fonts/vazir/Vazirmatn-FD-Bold.woff2) format("woff2");font-weight:700;font-display:swap}' +
  ':root{color-scheme:light dark;--bg:#f7faf3;--fg:#1d2a1f;--mut:#58665a;--pri:#2f6b3a;--card:#fff;--line:#dfe8da}' +
  '@media(prefers-color-scheme:dark){:root{--bg:#141c16;--fg:#e8efe6;--mut:#9fb0a2;--pri:#7cc58a;--card:#1c261e;--line:#2b392e}}' +
  '*{box-sizing:border-box}body{margin:0;background:var(--bg);color:var(--fg);font:17px/1.9 Vazirmatn,Tahoma,system-ui,sans-serif}' +
  'a{color:var(--pri);text-decoration:none!important;text-decoration-line:none!important}a:hover{text-decoration:none!important}a:focus-visible{outline:2px solid var(--pri);outline-offset:3px}' +
  '.wrap{max-width:56rem;margin:0 auto;padding:0 1rem}header{border-bottom:1px solid var(--line);background:var(--card)}' +
  'header .wrap{display:flex;align-items:center;gap:1rem;flex-wrap:wrap;padding:.65rem 1rem}nav{display:flex;gap:1rem;flex-wrap:wrap;margin-inline-start:auto}' +
  'nav a{color:var(--mut)}.brand{display:flex;align-items:center;gap:.5rem;color:var(--fg);font-weight:700}.brand img{border-radius:8px}' +
  'main{padding:1.5rem 0 3rem}h1{font-size:1.75rem;line-height:1.5;margin:.3rem 0 .6rem}h2{font-size:1.25rem;line-height:1.6;margin:1.5rem 0 .5rem}' +
  '.crumbs{font-size:.9rem;color:var(--mut)}.crumbs a{color:var(--mut)}.lead{color:var(--mut)}' +
  '.item{background:var(--card);border:1px solid var(--line);border-radius:14px;padding:1rem 1.1rem;margin:.9rem 0;scroll-margin-top:1rem}' +
  '.scholar-card{display:flex;align-items:center;gap:1rem;color:#fff;padding:1.1rem;background:linear-gradient(145deg,hsl(var(--hue) 55% 42%),hsl(var(--hue) 60% 25%));box-shadow:0 8px 22px hsl(var(--hue) 60% 20% / .18)}' +
  '.scholar-cover{position:relative;display:grid;place-items:center;flex:0 0 5.5rem;aspect-ratio:1;border-radius:1.5rem;overflow:hidden;background:linear-gradient(145deg,hsl(var(--hue) 55% 55%),hsl(var(--hue) 60% 30%));color:#fff;font-size:2rem;font-weight:700}' +
  '.scholar-cover img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}.scholar-info{min-width:0}.scholar-info h2{margin:0 0 .25rem;font-size:1.15rem}.scholar-info h2 a{color:#fff}.scholar-info p{margin:.2rem 0;color:rgb(255 255 255 / .82)}.scholar-info .meta{color:rgb(255 255 255 / .9)}' +
  '.item h2{margin:0 0 .3rem;font-size:1.1rem}.item p{margin:.35rem 0}.meta{color:var(--mut);font-size:.9rem}' +
  'audio{display:block;width:100%;margin:.75rem 0;min-height:44px}footer{border-top:1px solid var(--line);padding:1.2rem 0;color:var(--mut);font-size:.9rem}' +
  'footer .wrap{display:flex;gap:1rem;flex-wrap:wrap}@media(prefers-reduced-motion:reduce){*{scroll-behavior:auto!important}}'

function breadcrumb(items) {
  return { '@context': 'https://schema.org', '@type': 'BreadcrumbList',
    itemListElement: items.map((pair, index) => ({ '@type': 'ListItem', position: index + 1, name: pair[0], item: pair[1] })) }
}

function documentPage(data) {
  const schema = data.schema.map(item => '<script type="application/ld+json">' + safeJson(item) + '</script>').join('\n')
  return '<!doctype html>\n<html lang="fa" dir="rtl">\n<head>\n' +
    '<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">\n' +
    '<title>' + esc(data.title) + '</title><meta name="description" content="' + esc(data.description) + '">\n' +
    '<meta name="robots" content="index,follow,max-image-preview:large"><link rel="canonical" href="' + esc(data.url) + '">\n' +
    '<link rel="icon" href="/icons/favicon-32x32.png"><meta name="theme-color" content="#2f6b3a">\n' +
    '<meta property="og:type" content="website"><meta property="og:site_name" content="اذکار نور"><meta property="og:locale" content="fa_IR">\n' +
    '<meta property="og:title" content="' + esc(data.title) + '"><meta property="og:description" content="' + esc(data.description) + '">' +
    '<meta property="og:url" content="' + esc(data.url) + '"><meta property="og:image" content="' + SITE + '/images/og-cover.jpg">\n' +
    '<meta name="twitter:card" content="summary_large_image"><meta name="twitter:title" content="' + esc(data.title) + '">' +
    '<meta name="twitter:description" content="' + esc(data.description) + '"><meta name="twitter:image" content="' + SITE + '/images/og-cover.jpg">\n' +
    '<style>' + CSS + '</style>\n' + schema + '\n</head>\n<body>' +
    '<header><div class="wrap"><a class="brand" href="/"><img src="/icons/logo.png" alt="" width="32" height="32"><span>اذکار نور</span></a>' +
    '<nav aria-label="منوی اصلی"><a href="/adhkar">اذکار و ادعیه</a><a href="/blog">مقالات</a><a href="/lectures" aria-current="page">سخنرانی‌ها</a><a href="/app">اپلیکیشن</a></nav></div></header>' +
    '<main><div class="wrap">' + data.body + '</div></main><footer><div class="wrap"><span>© اذکار نور</span>' +
    '<a href="/about">درباره ما</a><a href="/privacy">حریم خصوصی</a></div></footer></body></html>'
}

function writePage(route, html) {
  const folder = path.join(DIST, route.replace(/^\/+/, ''))
  fs.mkdirSync(folder, { recursive: true })
  fs.writeFileSync(path.join(folder, 'index.html'), html, 'utf8')
}

function mergeSitemap(routes) {
  const file = path.join(DIST, 'sitemap.xml')
  let xml = fs.readFileSync(file, 'utf8')
  xml = xml.replace(/\s*<url>\s*<loc>https:\/\/adhkar\.ir\/lectures(?:\/[^<]*)?<\/loc>[\s\S]*?<\/url>/g, '')
  const entries = routes.map(item => '  <url>\n    <loc>' + SITE + item.route + '</loc>\n    <changefreq>monthly</changefreq>\n    <priority>' + item.priority + '</priority>\n  </url>').join('\n')
  if (!xml.includes('</urlset>')) throw new Error('Generated sitemap is invalid')
  fs.writeFileSync(file, xml.replace('</urlset>', entries + '\n</urlset>'), 'utf8')
}

async function main() {
  if (!fs.existsSync(DIST)) throw new Error('dist directory not found: ' + DIST)
  const scholars = await loadCatalog()
  const lectureCount = validateCatalog(scholars)
  const routes = [{ route: '/lectures', priority: '0.8' }]
  const archiveUrl = SITE + '/lectures'
  const scholarCards = scholars.map(scholar => {
    const lectures = scholar.lectures
    const route = routeForScholar(scholar)
    routes.push({ route, priority: '0.7' })
    const countLabel = lectures.length ? lectures.length + ' سخنرانی منتشرشده' : 'هنوز سخنرانی منتشرشده‌ای ندارد'
    const initial = esc(scholar.name.replace(/^شیخ\s+/, '').trim().slice(0, 1) || scholar.name.slice(0, 1))
    const photo = photoForScholar(scholar)
    const cover = '<div class="scholar-cover" aria-hidden="true" style="--hue:' + Number(scholar.hue) + '"><span>' + initial + '</span>' +
      (photo ? '<img src="' + esc(photo) + '" alt="' + esc(scholar.name) + '" loading="lazy" decoding="async" onerror="this.remove()">' : '') + '</div>'
    return '<article class="item scholar-card" style="--hue:' + Number(scholar.hue) + '">' + cover + '<div class="scholar-info"><h2><a href="' + route + '">' + esc(scholar.name) + '</a></h2>' +
      (scholar.tagline ? '<p>' + esc(scholar.tagline) + '</p>' : '') +
      '<p>' + esc(scholar.bio || '') + '</p><p class="meta">' + countLabel + '</p></div></article>'
  }).join('\n')
  const archiveSchema = [breadcrumb([['خانه', SITE + '/'], ['سخنرانی‌ها', archiveUrl]]),
    { '@context': 'https://schema.org', '@type': 'CollectionPage', name: 'آرشیو سخنرانی‌های استادان', url: archiveUrl,
      inLanguage: 'fa', numberOfItems: scholars.length,
      mainEntity: { '@type': 'ItemList', numberOfItems: scholars.length,
        itemListElement: scholars.map((scholar, index) => ({ '@type': 'ListItem', position: index + 1,
          name: scholar.name, url: SITE + routeForScholar(scholar) })) } }]
  writePage('lectures', documentPage({
    title: 'آرشیو سخنرانی‌های استادان؛ فهرست و پخش صوتی | اذکار نور',
    description: 'فهرست سخنرانی‌های صوتی استادان در اذکار نور؛ ' + scholars.length + ' استاد و ' + lectureCount + ' فایل صوتی منتشرشده با توضیحات فارسی و پخش مستقیم.',
    url: archiveUrl, schema: archiveSchema,
    body: '<nav class="crumbs" aria-label="مسیر صفحه"><a href="/">خانه</a> › سخنرانی‌ها</nav>' +
      '<h1>آرشیو سخنرانی‌های استادان</h1><section aria-label="استادان">' + scholarCards + '</section>'
  }))

  for (const scholar of scholars) {
    const scholarRoute = routeForScholar(scholar)
    const scholarUrl = SITE + scholarRoute
    const lectures = scholar.lectures
    const cards = lectures.map(lecture => {
      const route = routeForLecture(scholar, lecture)
      routes.push({ route, priority: '0.6' })
      return '<article class="item"><h2><a href="' + route + '">' + esc(lecture.title) + '</a></h2><p>' +
        esc(lecture.description) + '</p><audio controls preload="none" aria-label="پخش صوتی: ' + esc(lecture.title) + '">' +
        '<source src="' + esc(lecture.audioUrl) + '" type="audio/mpeg">مرورگر شما از پخش صوتی پشتیبانی نمی‌کند.</audio>' +
        '<p class="meta"><a href="' + route + '">صفحه سخنرانی</a> · <a href="' + esc(lecture.audioUrl) + '">دریافت فایل صوتی از سرور اذکار نور</a></p></article>'
    }).join('\n')
    const profile = scholar.bio ? '<p class="lead">' + esc(scholar.bio) + '</p>' : ''
    const empty = '<p class="lead">در کاتالوگ عمومی فعلی، سخنرانی منتشرشده‌ای برای این استاد ثبت نشده است.</p>'
    writePage(scholarRoute.slice(1), documentPage({
      title: scholar.name + '؛ سخنرانی‌های صوتی و درس‌ها | اذکار نور',
      description: 'سخنرانی‌های صوتی ' + scholar.name + ' در اذکار نور؛ ' + lectures.length + ' فایل منتشرشده با توضیحات فارسی و پخش مستقیم.',
      url: scholarUrl,
      schema: [breadcrumb([['خانه', SITE + '/'], ['سخنرانی‌ها', archiveUrl], [scholar.name, scholarUrl]]),
        { '@context': 'https://schema.org', '@type': 'ProfilePage', name: scholar.name, url: scholarUrl,
          inLanguage: 'fa', mainEntity: { '@type': 'Person', name: scholar.name, ...(scholar.bio ? { description: scholar.bio } : {}) } }],
      body: '<nav class="crumbs" aria-label="مسیر صفحه"><a href="/">خانه</a> › <a href="/lectures">سخنرانی‌ها</a> › ' + esc(scholar.name) + '</nav>' +
        '<h1>سخنرانی‌های ' + esc(scholar.name) + '</h1>' + profile +
        (lectures.length ? '<section aria-label="سخنرانی‌ها">' + cards + '</section>' : empty)
    }))

    for (const lecture of lectures) {
      const route = routeForLecture(scholar, lecture)
      const url = SITE + route
      const description = lecture.description.trim()
      const audioSchema = { '@context': 'https://schema.org', '@type': 'AudioObject', '@id': url + '#audio',
        name: lecture.title, description, contentUrl: lecture.audioUrl, encodingFormat: 'audio/mpeg', inLanguage: 'fa',
        creator: { '@type': 'Person', name: scholar.name } }
      const schema = [breadcrumb([['خانه', SITE + '/'], ['سخنرانی‌ها', archiveUrl], [scholar.name, scholarUrl], [lecture.title, url]]),
        { '@context': 'https://schema.org', '@type': 'WebPage', name: lecture.title, description, url, inLanguage: 'fa',
          mainEntity: { '@id': url + '#audio' } }, audioSchema]
      const body = '<nav class="crumbs" aria-label="مسیر صفحه"><a href="/">خانه</a> › <a href="/lectures">سخنرانی‌ها</a> › ' +
        '<a href="' + scholarRoute + '">' + esc(scholar.name) + '</a> › ' + esc(lecture.title) + '</nav><article><h1>' +
        esc(lecture.title) + '</h1><p class="meta">سخنران: <a href="' + scholarRoute + '">' + esc(scholar.name) +
        '</a></p><h2>درباره این سخنرانی</h2><p>' + esc(description) + '</p><h2>پخش فایل صوتی</h2>' +
        '<audio controls preload="none" aria-label="پخش صوتی: ' + esc(lecture.title) + '"><source src="' +
        esc(lecture.audioUrl) + '" type="audio/mpeg">مرورگر شما از پخش صوتی پشتیبانی نمی‌کند.</audio>' +
        '<p class="meta"><a href="' + esc(lecture.audioUrl) + '">دریافت فایل صوتی از سرور اذکار نور</a></p>' +
        '<p><a href="' + scholarRoute + '">بازگشت به سخنرانی‌های ' + esc(scholar.name) + '</a></p></article>'
      writePage(route.slice(1), documentPage({ title: lecture.title + '؛ سخنرانی ' + scholar.name + ' | اذکار نور',
        description, url, schema, body }))
    }
  }

  mergeSitemap(routes)
  console.log('Lecture pages generated: ' + routes.length + ' crawlable Persian pages for ' + lectureCount +
    ' public lectures by ' + scholars.filter(item => item.lectures.length).length + ' scholars (' + scholars.length +
    ' scholar profiles total); media remains on ' + AUDIO_ORIGIN + '.')
}

await main()
