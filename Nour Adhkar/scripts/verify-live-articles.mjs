import assert from 'node:assert/strict'
import fs from 'node:fs'
import { fetchPosts } from './refresh-articles.mjs'

const site = process.env.SITE_URL || 'https://adhkar.ir'
const posts = await fetchPosts(process.env.CONTENT_API_URL || 'https://api.adhkar.ir/api')
async function page(url, status = 200) {
  const response = await fetch(site + url, { signal: AbortSignal.timeout(20000), headers: { 'Cache-Control': 'no-cache' } })
  assert.equal(response.status, status, url)
  return response.text()
}
const locales = [
  { code: 'fa', prefix: '', label: 'Persian' },
  { code: 'ar', prefix: '/ar', label: 'Arabic' },
  { code: 'ckb', prefix: '/ckb', label: 'Sorani Kurdish' },
]
const localized = posts.map(post => ({ ...post, locale: post.locale || 'fa' }))
const arabicSnapshot = JSON.parse(fs.readFileSync(new URL('../content-snapshot/posts-ar.json', import.meta.url), 'utf8'))
for (const post of [...localized].filter(item => item.locale === 'fa')) {
  const hasArabicVariant = localized.some(item => item.locale === 'ar' && item.translation_group && item.translation_group === post.translation_group)
  if (arabicSnapshot[post.slug] && !hasArabicVariant) localized.push({ ...post, locale: 'ar' })
}
for (const { code, prefix, label } of locales) {
  const index = await page(`${prefix}/blog`)
  const expectedSlugs = new Set(localized.filter(post => post.locale === code).map(post => post.slug))
  for (const post of localized) {
    const expected = expectedSlugs.has(post.slug)
    assert.equal(index.includes(`href="${prefix}/blog/${post.slug}"`), expected, `${prefix}/blog ${expected ? 'missing' : 'contains foreign'} ${label} article ${post.slug}`)
  }
}
const sitemap = await page('/sitemap.xml')
for (const post of localized) {
  const prefix = post.locale === 'fa' ? '' : `/${post.locale}`
  const url = `${prefix}/blog/${post.slug}`
  const html = await page(url)
  assert.ok(html.includes(`rel="canonical" href="https://adhkar.ir${url}"`), `${url} canonical missing`)
  assert.ok(html.includes('<article>'), `${url} body must be server-rendered HTML`)
  assert.ok(sitemap.includes(`https://adhkar.ir${url}</loc>`), `${url} missing from sitemap`)
}
await page('/blog/nonexistent-article-verification-4ec061', 404)
console.log(`Live website verified: ${posts.length} articles, all indexes, detail pages, sitemap and real 404.`)
