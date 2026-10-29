import { test } from 'node:test'
import assert from 'node:assert/strict'
import fs from 'node:fs'
import { formatArticle, renderBlogPages } from '../generate-content-pages.mjs'
import { reviewedDhikr, normalizeArabic } from '../collection-editorial.mjs'

test('Windows admin line endings render question headings, paragraphs, citations and lists', () => {
  const content = 'یک مقدمهٔ کامل برای مقاله.\r\n\r\nدعای سفر را چه زمانی می‌خوانیم؟\r\n\r\nاین متن توضیح مقاله است.\r\n\r\n• منبع: https://sunnah.com/muslim:1342 ؛\r\n• قرآن: https://quran.com/43/13-14';
  const html = formatArticle(content)
  assert.match(html, /<h2>دعای سفر را چه زمانی می‌خوانیم؟<\/h2>/)
  assert.match(html, /<p>این متن توضیح مقاله است\.<\/p>/)
  assert.match(html, /<ul><li>منبع: <a href="https:\/\/sunnah.com\/muslim:1342"/)
  assert.equal(html, formatArticle(content.replace(/\r\n/g, '\n')))
})

test('citation links escape content and never execute supplied HTML or unsafe URL schemes', () => {
  const html = formatArticle('<img src=x onerror=alert(1)>\n\n• https://example.com/?a=1&b=2،\n• javascript:alert(1)\n• https://user:password@example.com/\n• https://example.com/" onclick="alert(1)')
  assert.doesNotMatch(html, /<img|href="javascript:|href="https:\/\/user:password/)
  assert.match(html, /href="https:\/\/example.com\/\?a=1&amp;b=2"/)
  assert.match(html, /&lt;img/)
  assert.doesNotMatch(html, /" onclick="/)
})

test('Quran corrections include missing clauses and are guarded by the complete Arabic text', () => {
  const cols = JSON.parse(fs.readFileSync(new URL('../../content-snapshot/collections.json', import.meta.url), 'utf8'))
  const items = cols.flatMap(c => c.adhkar || [])
  const ayat = items.find(a => a.id === 7)
  const falaq = items.find(a => a.id === 9)
  assert.match(reviewedDhikr(ayat).translation, /از دانش او جز آنچه بخواهد/)
  assert.match(reviewedDhikr(falaq).translation, /دمندگان در گره‌ها/)
  assert.equal(reviewedDhikr({ ...ayat, arabic_text: ayat.arabic_text + ' عبارة مختلفة' }), null)
  assert.equal(normalizeArabic('سُبْحَانَ اللَّهِ'), normalizeArabic('سبحان الله'))
  assert.equal(reviewedDhikr(items.find(a => a.id === 38)).title, 'سورهٔ فلق')
})

test('an author is linked to the actual about page only for the known project author', () => {
  const post = { slug:'sample',title:'یک مقاله',content:'یک متن کوتاه.',published_at:'2026-10-28T10:00:00Z',updated_at:'2026-10-28T10:00:00Z',status:'published',user:{name:'ادریس'} }
  const article = renderBlogPages([post]).find(p => p.path === '/blog/sample').html
  assert.match(article, /href="\/about#author" rel="author"/)
  const other = renderBlogPages([{...post,user:{name:'نویسندهٔ مهمان'}}]).find(p => p.path === '/blog/sample').html
  assert.doesNotMatch(other, /rel="author"/)
  assert.doesNotMatch(other, /"name":"نویسندهٔ مهمان","url"/)
})

test('every morning and evening card has an exact-text review and identifiable sources', () => {
  const cols = JSON.parse(fs.readFileSync(new URL('../../content-snapshot/collections.json', import.meta.url), 'utf8'))
  const daily = cols.filter(c => ['morning', 'night'].includes(c.slug)).flatMap(c => c.adhkar)
  assert.equal(daily.length, 45)
  for (const item of daily) {
    const review = reviewedDhikr(item)
    assert.ok(review, `Missing exact-text review for card ${item.id}`)
    assert.ok(review.references.length, `Missing source for card ${item.id}`)
    assert.ok(review.note, `Missing source-scope explanation for card ${item.id}`)
    for (const source of review.references) assert.equal(new URL(source.url).protocol, 'https:')
    assert.equal(reviewedDhikr({...item, arabic_text: item.arabic_text + ' نص زائد'}), null)
  }
})

test('review distinguishes weak reports, wording variants, and evening-only repetition', () => {
  const items = JSON.parse(fs.readFileSync(new URL('../../content-snapshot/collections.json', import.meta.url), 'utf8')).flatMap(c => c.adhkar || [])
  const review = id => reviewedDhikr(items.find(a => a.id === id))
  for (const id of [14, 15, 24, 29, 31, 33, 42, 43]) {
    assert.equal(review(id).reviewStatus, 'weak-report')
    assert.equal(review(id).count, undefined, `Do not authenticate a weak report's repetition: ${id}`)
    assert.ok(review(id).noteAr)
  }
  assert.equal(review(25).reviewStatus, 'wording-variant')
  assert.equal(review(41), review(25))
  assert.deepEqual(review(26).contexts, ['night'])
  assert.equal(review(44), review(26))
  for (const id of [5, 13, 16, 23, 27, 28, 30]) assert.equal(review(id).count, undefined)
})

test('Persian meaning covers the displayed excerpt and fixes the trust pronoun', () => {
  const items = JSON.parse(fs.readFileSync(new URL('../../content-snapshot/collections.json', import.meta.url), 'utf8')).flatMap(c => c.adhkar || [])
  const morning = reviewedDhikr(items.find(a => a.id === 11))
  assert.doesNotMatch(morning.translation, /پیری|قبر|آتش|تنبلی/)
  assert.match(morning.note, /بخشی از دعا/)
  assert.match(reviewedDhikr(items.find(a => a.id === 16)).translation, /بر او توکل/)
  assert.match(reviewedDhikr(items.find(a => a.id === 41)).translation, /به تو پناه می‌برم/)
})
