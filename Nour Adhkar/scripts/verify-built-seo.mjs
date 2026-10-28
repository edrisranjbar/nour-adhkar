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
  const ids = [...doc.querySelectorAll('[id]')].map(n => n.id)
  assert.equal(new Set(ids).size, ids.length, `${route}: fragment IDs must be unique`)
  for (const anchor of doc.querySelectorAll('a[href^="#"]')) {
    const id = anchor.getAttribute('href').slice(1)
    if (id) assert.ok(doc.getElementById(id), `${route}: fragment target ${id} exists`)
  }
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
  if (route.startsWith('adhkar/')) {
    assert.ok(html.indexOf('href="https://cafebazaar.ir/app/ir.adhkar.app"',html.indexOf('class="lead"'))<html.indexOf('<article'),'Store CTA comes before the long collection')
    assert.ok(doc.querySelector('.toc a[href="#dhikr-1"]'), 'Collection has usable contents navigation')
    assert.match(doc.querySelector('main').textContent,/پخش صوتی اذکار در برنامهٔ اندروید/)
    assert.ok(doc.querySelector('.source a[href="https://quran.com/113"]'), 'Falaq source is visible')
    assert.match(doc.querySelector('main').textContent,/دمندگان در گره‌ها/)
    assert.match(doc.querySelector('main').textContent,/از دانش او جز آنچه بخواهد/)
  }
}
for (const language of ['ar','ckb']) {
  const doc = new JSDOM(await fs.readFile(path.join(dist,language,'adhkar/sobhgahi/index.html'),'utf8')).window.document
  assert.ok(doc.querySelector('.source a[href="https://quran.com/113"]'), `${language}: native source references`)
  assert.ok(doc.querySelector('.toc a[href="#dhikr-1"]'), `${language}: contents navigation`)
}
const collections = JSON.parse(await fs.readFile(new URL('../content-snapshot/collections.json', import.meta.url),'utf8'))
for (const [slug, route] of [['morning','sobhgahi'], ['night','shamgahi']]) {
  const collection = collections.find(c => c.slug === slug)
  for (const language of ['fa','ar','ckb']) {
    const prefix = language === 'fa' ? '' : `${language}/`
    const doc = new JSDOM(await fs.readFile(path.join(dist,prefix,`adhkar/${route}/index.html`),'utf8')).window.document
    collection.adhkar.forEach((item,index) => {
      const card = doc.getElementById(`dhikr-${index+1}`)
      assert.ok(card.querySelector('.source a'), `${language} ${slug} ${item.id}: source visible`)
      assert.ok(card.querySelector('.editorial-note'), `${language} ${slug} ${item.id}: qualification visible`)
      if ([14,15,24,29,31,33,42,43].includes(item.id)) {
        assert.match(card.querySelector('.source').textContent, language === 'fa' ? /ضعیف/ : /ضعيف/)
      }
      if ([5,13,14,16,23,27,28,29,30,31,42,47].includes(item.id) || (slug === 'morning' && item.id === 26)) {
        assert.match(card.querySelector('.badge').textContent, language === 'fa' ? /شمارندهٔ ثبت‌شده/ : language === 'ar' ? /العدّاد المسجّل/ : /ژمێرەری تۆمارکراو/)
      }
    })
  }
}
const landing = new JSDOM(await fs.readFile(path.join(dist,'index.html'),'utf8')).window.document
assert.ok(landing.querySelector('.reading-nav a[href="/adhkar/sobhgahi"]'), 'Homepage links directly to morning collection')
assert.ok(landing.querySelector('.reading-nav a[href="/adhkar/shamgahi"]'), 'Homepage links directly to evening collection')
for (const route of ['/','/updates','/app']) {
  const entry=[...sitemap.querySelectorAll('url')].find(n=>n.querySelector('loc').textContent==='https://adhkar.ir'+route)
  assert.equal(entry.querySelector('lastmod'),null,`${route}: do not invent a build-time content modification date`)
}
console.log(`Verified ${locations.length} generated sitemap URLs, metadata, JSON-LD, release history, download links and truthful lastmod handling.`)
