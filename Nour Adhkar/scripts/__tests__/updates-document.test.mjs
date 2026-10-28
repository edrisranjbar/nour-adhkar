import { test } from 'node:test'
import assert from 'node:assert/strict'
import { JSDOM } from 'jsdom'
import { updatesDocument, UPDATES_URL, UPDATES_TITLE } from '../updates-document.mjs'

const shell = `<!doctype html><html lang="fa"><head><title>Homepage</title><meta name="description" content="Home description"><link rel="canonical" href="https://adhkar.ir/"><meta property="og:title" content="Home"><meta property="og:url" content="https://adhkar.ir/"><script type="application/ld+json">{"@type":"SoftwareApplication"}</script><script type="module" src="/assets/app.js"></script></head><body><div id="app" data-ssr="landing"><h1>Homepage hero</h1></div><script>if(location.pathname!=="/"){var a=document.getElementById("app");a.innerHTML="";a.removeAttribute("data-ssr")}</script></body></html>`

test('updates response has its own crawlable release content, canonical and metadata before JS', () => {
  const result = updatesDocument(shell, [{versionName:'3.1.0',releaseDate:'2026-10-26',changelog:'بهبود یادآورها\nرفع اشکال اوقات شرعی'}])
  const doc = new JSDOM(result).window.document
  assert.equal(doc.title, UPDATES_TITLE)
  assert.deepEqual([...doc.querySelectorAll('link[rel="canonical"]')].map(n=>n.href), [UPDATES_URL])
  assert.equal(doc.querySelector('meta[property="og:url"]').content, UPDATES_URL)
  assert.equal(doc.querySelector('h1').textContent, 'تازه‌ها و به‌روزرسانی‌ها')
  assert.match(doc.querySelector('#app').textContent,/3\.1\.0/)
  assert.match(doc.querySelector('#app').textContent,/بهبود یادآورها/)
  assert.doesNotMatch(result,/Homepage hero|a\.innerHTML=""|SoftwareApplication/)
  assert.equal(doc.querySelector('script[type="module"]').getAttribute('src'),'/assets/app.js')
  assert.equal(doc.querySelector('#app').hasAttribute('data-ssr'),false)
  assert.equal(JSON.parse(doc.querySelector('script[type="application/ld+json"]').textContent).url, UPDATES_URL)
})

test('unavailable release API still produces an honest unique page; release text stays escaped', () => {
  const fallback = new JSDOM(updatesDocument(shell)).window.document
  assert.equal(fallback.querySelector('h1').textContent,'تازه‌ها و به‌روزرسانی‌ها')
  assert.equal(fallback.querySelectorAll('article').length,0)
  const doc = new JSDOM(updatesDocument(shell,[{versionName:'<script>x</script>',releaseDate:'2026-10-26',changelog:'<img src=x onerror=alert(1)>'}])).window.document
  assert.equal(doc.querySelector('#app script, #app img'),null)
  assert.match(doc.querySelector('article').textContent,/<img src=x onerror=alert\(1\)>/)
})
