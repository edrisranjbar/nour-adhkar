import assert from 'node:assert/strict'
import { fetchPosts } from './refresh-articles.mjs'

const site = process.env.SITE_URL || 'https://adhkar.ir'
const posts = await fetchPosts(process.env.CONTENT_API_URL || 'https://api.adhkar.ir/api')
async function page(url, status = 200) {
  const response = await fetch(site + url, { signal: AbortSignal.timeout(20000), headers: { 'Cache-Control': 'no-cache' } })
  assert.equal(response.status, status, url)
  return response.text()
}
for (const lang of ['', '/ar', '/ckb']) {
  const index = await page(`${lang}/blog`)
  for (const post of posts) assert.ok(index.includes(`/blog/${post.slug}`), `${lang}/blog missing ${post.slug}`)
}
const sitemap = await page('/sitemap.xml')
for (const post of posts) {
  const url = `/blog/${post.slug}`
  const html = await page(url)
  assert.ok(html.includes(`rel="canonical" href="https://adhkar.ir${url}"`), `${url} canonical missing`)
  assert.ok(html.includes('<article>'), `${url} body must be server-rendered HTML`)
  assert.ok(sitemap.includes(`https://adhkar.ir${url}</loc>`), `${url} missing from sitemap`)
}
await page('/blog/nonexistent-article-verification-4ec061', 404)
console.log(`Live website verified: ${posts.length} articles, all indexes, detail pages, sitemap and real 404.`)
