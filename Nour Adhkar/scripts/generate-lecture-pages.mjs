#!/usr/bin/env node
// Generate crawlable Persian pages for Sheikh Ziaei's published lecture records.
// The audio files remain on api.adhkar.ir and are not copied into the website bundle.

import fs from 'node:fs'
import path from 'node:path'
import { fileURLToPath } from 'node:url'

const ROOT = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..')
const DIST = path.join(ROOT, 'dist')
const SNAPSHOT = path.join(ROOT, 'content-snapshot', 'lectures-ziaei.json')
const SITE = 'https://adhkar.ir'
const API = process.env.CONTENT_API_URL || 'https://api.adhkar.ir/api'
const SCHOLAR_ID = 'ziaei'
const AUDIO_ORIGIN = 'https://api.adhkar.ir'

const esc = value => String(value ?? '').replace(/[&<>"']/g, ch => ({
  '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
}[ch]))
const safeJson = value => JSON.stringify(value).replace(/</g, '\\u003c')

async function loadScholar() {
  try {
    const response = await fetch(API + '/scholars', {
      signal: AbortSignal.timeout(20000),
      headers: { Accept: 'application/json' }
    })
    if (!response.ok) throw new Error('API returned HTTP ' + response.status)
    const payload = await response.json()
    const scholars = Array.isArray(payload?.data) ? payload.data : []
    const scholar = scholars.find(item => item.id === SCHOLAR_ID)
    if (!scholar) throw new Error('Scholar ' + SCHOLAR_ID + ' is absent from the public API')
    return scholar
  } catch (error) {
    if (!fs.existsSync(SNAPSHOT)) throw new Error('Unable to load lecture data (' + error.message + ') and no snapshot exists')
    console.warn('! Using committed Sheikh Ziaei lecture snapshot (' + error.message + ')')
    const payload = JSON.parse(fs.readFileSync(SNAPSHOT, 'utf8'))
    const scholar = payload?.data?.find(item => item.id === SCHOLAR_ID)
    if (!scholar) throw new Error('Snapshot does not contain scholar ' + SCHOLAR_ID)
    return scholar
  }
}

function validateLectures(scholar) {
  if (!Array.isArray(scholar.lectures) || scholar.lectures.length === 0) throw new Error('No published lectures are available for Sheikh Ziaei')
  const ids = new Set()
  for (const lecture of scholar.lectures) {
    const id = String(lecture.id ?? '')
    if (!/^[A-Za-z0-9_-]+$/.test(id) || ids.has(id)) throw new Error('Invalid or duplicate lecture id: ' + id)
    ids.add(id)
    if (!lecture.title?.trim() || !lecture.description?.trim()) throw new Error('Lecture ' + id + ' needs a title and description')
    let audio
    try { audio = new URL(lecture.audioUrl) } catch { throw new Error('Lecture ' + id + ' has an invalid audio URL') }
    if (audio.protocol !== 'https:' || audio.origin !== AUDIO_ORIGIN || !audio.pathname.startsWith('/storage/lectures/')) {
      throw new Error('Lecture ' + id + ' audio must remain on ' + AUDIO_ORIGIN)
    }
    if (!audio.pathname.toLowerCase().endsWith('.mp3')) throw new Error('Lecture ' + id + ' is not an MP3 file')
    if (lecture.durationSec != null && (!Number.isFinite(Number(lecture.durationSec)) || Number(lecture.durationSec) <= 0)) {
      throw new Error('Lecture ' + id + ' has an invalid duration')
    }
  }
  return scholar.lectures
}

const CSS = '@font-face{font-family:Vazirmatn;src:url(/fonts/vazir/Vazirmatn-FD-Regular.woff2) format("woff2");font-weight:400;font-display:swap}' +
  '@font-face{font-family:Vazirmatn;src:url(/fonts/vazir/Vazirmatn-FD-Bold.woff2) format("woff2");font-weight:700;font-display:swap}' +
  ':root{color-scheme:light dark;--bg:#f7faf3;--fg:#1d2a1f;--mut:#58665a;--pri:#2f6b3a;--card:#fff;--line:#dfe8da}' +
  '@media(prefers-color-scheme:dark){:root{--bg:#141c16;--fg:#e8efe6;--mut:#9fb0a2;--pri:#7cc58a;--card:#1c261e;--line:#2b392e}}' +
  '*{box-sizing:border-box}body{margin:0;background:var(--bg);color:var(--fg);font:17px/1.9 Vazirmatn,Tahoma,system-ui,sans-serif}' +
  'a{color:var(--pri);text-decoration:none}a:hover{text-decoration:underline}a:focus-visible{outline:2px solid var(--pri);outline-offset:3px}' +
  '.wrap{max-width:52rem;margin:0 auto;padding:0 1rem}header{border-bottom:1px solid var(--line);background:var(--card)}' +
  'header .wrap{display:flex;align-items:center;gap:1rem;flex-wrap:wrap;padding:.65rem 1rem}nav{display:flex;gap:1rem;flex-wrap:wrap;margin-inline-start:auto}' +
  'nav a{color:var(--mut)}.brand{display:flex;align-items:center;gap:.5rem;color:var(--fg);font-weight:700}.brand img{border-radius:8px}' +
  'main{padding:1.5rem 0 3rem}h1{font-size:1.75rem;line-height:1.5;margin:.3rem 0 .6rem}h2{font-size:1.25rem;line-height:1.6;margin:1.8rem 0 .5rem}' +
  '.crumbs{font-size:.9rem;color:var(--mut)}.crumbs a{color:var(--mut)}.lead{color:var(--mut)}' +
  '.item{background:var(--card);border:1px solid var(--line);border-radius:14px;padding:1rem 1.1rem;margin:.9rem 0;scroll-margin-top:1rem}' +
  '.item h2{margin:0 0 .3rem;font-size:1.1rem}.item p{margin:.35rem 0}.meta{color:var(--mut);font-size:.9rem}' +
  'audio{display:block;width:100%;margin:.75rem 0;min-height:44px}footer{border-top:1px solid var(--line);padding:1.2rem 0;color:var(--mut);font-size:.9rem}' +
  'footer .wrap{display:flex;gap:1rem;flex-wrap:wrap}@media(prefers-reduced-motion:reduce){*{scroll-behavior:auto!important}}'

function breadcrumb(items) {
  return {
    '@context': 'https://schema.org',
    '@type': 'BreadcrumbList',
    itemListElement: items.map((pair, index) => ({ '@type': 'ListItem', position: index + 1, name: pair[0], item: pair[1] }))
  }
}

function isoDuration(value) {
  const total = Math.floor(Number(value))
  if (!Number.isFinite(total) || total <= 0) return null
  const hours = Math.floor(total / 3600)
  const minutes = Math.floor((total % 3600) / 60)
  const seconds = total % 60
  return 'PT' + (hours ? hours + 'H' : '') + (minutes ? minutes + 'M' : '') + (seconds ? seconds + 'S' : '')
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
    '<a href="/about">دربارهٔ ما</a><a href="/privacy">حریم خصوصی</a></div></footer></body></html>'
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
  const scholar = await loadScholar()
  const lectures = validateLectures(scholar)
  const routes = [{ route: '/lectures', priority: '0.8' }, { route: '/lectures/' + SCHOLAR_ID, priority: '0.7' }]
  const archiveUrl = SITE + '/lectures'
  const scholarRoute = '/lectures/' + SCHOLAR_ID
  const scholarUrl = SITE + scholarRoute

  const archiveCards = lectures.map(lecture => {
    const route = scholarRoute + '/' + lecture.id
    routes.push({ route: route, priority: '0.6' })
    return '<article class="item"><h2><a href="' + route + '">' + esc(lecture.title) + '</a></h2><p>' +
      esc(lecture.description) + '</p><audio controls preload="none" aria-label="پخش صوتی: ' + esc(lecture.title) +
      '"><source src="' + esc(lecture.audioUrl) + '" type="audio/mpeg">مرورگر شما از پخش صوتی پشتیبانی نمی‌کند.</audio>' +
      '<p class="meta"><a href="' + route + '">صفحهٔ سخنرانی</a> · <a href="' + esc(lecture.audioUrl) +
      '">بازکردن فایل صوتی روی سرور</a></p></article>'
  }).join('\n')
  const archiveSchema = [
    breadcrumb([['خانه', SITE + '/'], ['سخنرانی‌ها', archiveUrl]]),
    { '@context': 'https://schema.org', '@type': 'CollectionPage', name: 'آرشیو سخنرانی‌های شیخ ضیایی',
      url: archiveUrl, inLanguage: 'fa', numberOfItems: lectures.length,
      mainEntity: { '@type': 'ItemList', numberOfItems: lectures.length,
        itemListElement: lectures.map((lecture, index) => ({ '@type': 'ListItem', position: index + 1,
          name: lecture.title, url: SITE + scholarRoute + '/' + lecture.id })) } }
  ]
  const archiveDesc = 'فهرست سخنرانی‌های منتشرشدهٔ شیخ ضیایی در برنامهٔ اذکار نور؛ ' + lectures.length +
    ' فایل صوتی فارسی با توضیح و پخش مستقیم از سرور اذکار نور.'
  writePage('lectures', documentPage({
    title: 'سخنرانی‌های شیخ ضیایی؛ فهرست و پخش صوتی | اذکار نور', description: archiveDesc,
    url: archiveUrl, schema: archiveSchema,
    body: '<nav class="crumbs" aria-label="مسیر صفحه"><a href="/">خانه</a> › سخنرانی‌ها</nav>' +
      '<h1>سخنرانی‌های شیخ ضیایی</h1><p class="lead">در این صفحه فایل‌های صوتی منتشرشده در فهرست عمومی اذکار نور را می‌توانید بشنوید. در حال حاضر ' +
      lectures.length + ' سخنرانی در این فهرست موجود است.</p><section aria-label="فهرست سخنرانی‌ها">' + archiveCards + '</section>'
  }))

  const scholarCards = lectures.map(lecture => '<article class="item"><h2><a href="' + scholarRoute + '/' + lecture.id +
    '">' + esc(lecture.title) + '</a></h2><p>' + esc(lecture.description) + '</p></article>').join('\n')
  writePage('lectures/' + SCHOLAR_ID, documentPage({
    title: 'شیخ ضیایی؛ سخنرانی‌ها و درس‌های صوتی | اذکار نور',
    description: 'سخنرانی‌های صوتی شیخ ضیایی با توضیح فارسی و پخش مستقیم؛ ' + lectures.length + ' فایل در فهرست عمومی اذکار نور.',
    url: scholarUrl,
    schema: [breadcrumb([['خانه', SITE + '/'], ['سخنرانی‌ها', archiveUrl], ['شیخ ضیایی', scholarUrl]]),
      { '@context': 'https://schema.org', '@type': 'ProfilePage', name: 'شیخ ضیایی', url: scholarUrl,
        inLanguage: 'fa', mainEntity: { '@type': 'Person', name: scholar.name } }],
    body: '<nav class="crumbs" aria-label="مسیر صفحه"><a href="/">خانه</a> › <a href="/lectures">سخنرانی‌ها</a> › شیخ ضیایی</nav>' +
      '<h1>سخنرانی‌های شیخ ضیایی</h1><p class="lead">فهرست فایل‌های صوتی منتشرشده در برنامهٔ اذکار نور.</p>' +
      '<section aria-label="سخنرانی‌های شیخ ضیایی">' + scholarCards + '</section>'
  }))

  for (const lecture of lectures) {
    const route = scholarRoute + '/' + lecture.id
    const url = SITE + route
    const description = lecture.description.trim()
    const duration = isoDuration(lecture.durationSec)
    const audioSchema = { '@context': 'https://schema.org', '@type': 'AudioObject', '@id': url + '#audio',
      name: lecture.title, description: description, contentUrl: lecture.audioUrl, encodingFormat: 'audio/mpeg',
      inLanguage: 'fa', ...(duration ? { duration: duration } : {}),
      ...(scholar.name ? { creator: { '@type': 'Person', name: scholar.name } } : {}) }
    const schema = [breadcrumb([['خانه', SITE + '/'], ['سخنرانی‌ها', archiveUrl],
      ['شیخ ضیایی', scholarUrl], [lecture.title, url]]),
      { '@context': 'https://schema.org', '@type': 'WebPage', name: lecture.title, description: description,
        url: url, inLanguage: 'fa', mainEntity: { '@id': url + '#audio' } }, audioSchema]
    const body = '<nav class="crumbs" aria-label="مسیر صفحه"><a href="/">خانه</a> › <a href="/lectures">سخنرانی‌ها</a> › ' +
      '<a href="' + scholarRoute + '">شیخ ضیایی</a> › ' + esc(lecture.title) + '</nav><article><h1>' +
      esc(lecture.title) + '</h1><p class="meta">سخنران: <a href="' + scholarRoute + '">' + esc(scholar.name) +
      '</a></p><h2>دربارهٔ این سخنرانی</h2><p>' + esc(description) + '</p><h2>پخش فایل صوتی</h2>' +
      '<audio controls preload="none" aria-label="پخش صوتی: ' + esc(lecture.title) + '"><source src="' +
      esc(lecture.audioUrl) + '" type="audio/mpeg">مرورگر شما از پخش صوتی پشتیبانی نمی‌کند.</audio>' +
      '<p class="meta"><a href="' + esc(lecture.audioUrl) + '">بازکردن فایل صوتی روی سرور اذکار نور</a></p>' +
      '<p><a href="' + scholarRoute + '">بازگشت به فهرست سخنرانی‌های شیخ ضیایی</a></p></article>'
    writePage(route.slice(1), documentPage({
      title: lecture.title + '؛ سخنرانی شیخ ضیایی | اذکار نور', description: description, url: url, schema: schema, body: body
    }))
  }

  mergeSitemap(routes)
  console.log('Lecture pages generated: ' + routes.length + ' crawlable Persian pages for ' + lectures.length +
    ' published Sheikh Ziaei lectures; media remains on ' + AUDIO_ORIGIN + '.')
}

await main()
