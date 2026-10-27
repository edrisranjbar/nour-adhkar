import assert from 'node:assert/strict'
import fs from 'node:fs/promises'
import path from 'node:path'
import { fileURLToPath } from 'node:url'
import { JSDOM } from 'jsdom'

const dist = fileURLToPath(new URL('../dist/', import.meta.url))
const sitemap = new JSDOM(await fs.readFile(path.join(dist,'sitemap.xml'),'utf8'), {contentType:'application/xml'}).window.document
const locations = [...sitemap.querySelectorAll('url > loc')].map(n=>n.textContent)
assert.equal(new Set(locations).size, locations.length, 'Sitemap URLs must not repeat')
for (const url of locations) {
  const route = new URL(url).pathname
  const html = await fs.readFile(path.join(dist,route,'index.html'),'utf8')
  const doc = new JSDOM(html).window.document
  assert.equal(doc.querySelectorAll('link[rel="canonical"]').length, 1, `${route}: one canonical`)
  assert.equal(doc.querySelector('link[rel="canonical"]').href,url,`${route}: canonical must match the sitemap URL`)
  assert.equal(doc.querySelectorAll('h1').length,1,`${route}: one visible heading`)
  assert.ok(doc.title.trim(),`${route}: title`)
  assert.ok(doc.querySelector('meta[name="description"]')?.content.trim(),`${route}: description`)
  assert.doesNotMatch(doc.querySelector('meta[name="robots"]')?.content || '',/noindex/i,`${route}: indexable`)
  for (const schema of doc.querySelectorAll('script[type="application/ld+json"]')) JSON.parse(schema.textContent)
}
const updates = new JSDOM(await fs.readFile(path.join(dist,'updates/index.html'),'utf8')).window.document
assert.equal(updates.querySelector('h1').textContent,'تازه‌ها و به‌روزرسانی‌ها')
assert.equal(updates.querySelector('meta[property="twitter:url"]').content,'https://adhkar.ir/updates')
assert.ok(updates.querySelector('article time[datetime]'), 'Release history is present in the initial response')
assert.ok(updates.querySelector('#app').textContent.includes('نسخهٔ'), 'Release notes remain visible before JS')
for (const route of ['app','adhkar/sobhgahi','adhkar/shamgahi']) {
  const html=await fs.readFile(path.join(dist,route,'index.html'),'utf8')
  const doc=new JSDOM(html).window.document
  assert.match(doc.title,/اهل سنت/)
  assert.ok(doc.querySelector('a[href="https://cafebazaar.ir/app/ir.adhkar.app"]'))
  assert.ok(doc.querySelector('a[href="https://myket.ir/app/ir.adhkar.app"]'))
  if (route.startsWith('adhkar/')) assert.ok(html.indexOf('href="/app"',html.indexOf('class="lead"'))<html.indexOf('<article'),'App link comes before the long collection')
}
for (const route of ['/','/updates','/app']) {
  const entry=[...sitemap.querySelectorAll('url')].find(n=>n.querySelector('loc').textContent==='https://adhkar.ir'+route)
  assert.equal(entry.querySelector('lastmod'),null,`${route}: do not invent a build-time content modification date`)
}
console.log(`Verified ${locations.length} generated sitemap URLs, metadata, JSON-LD, release history, download links and truthful lastmod handling.`)
