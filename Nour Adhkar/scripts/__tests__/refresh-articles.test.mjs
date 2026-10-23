import { test } from 'node:test'
import assert from 'node:assert/strict'
import fs from 'node:fs/promises'
import os from 'node:os'
import path from 'node:path'
import { fetchPosts, refreshArticles } from '../refresh-articles.mjs'

const NOW = Date.parse('2026-10-23T12:00:00Z')
const post = (slug, extra = {}) => ({ id: slug, slug, title: `عنوان ${slug}`, content: 'متن مقاله\n\nذکر و دعا',
  status: 'published', published_at: '2026-10-20T12:00:00Z', updated_at: '2026-10-20T12:00:00Z', ...extra })

async function fixture(t) {
  const dir = await fs.mkdtemp(path.join(os.tmpdir(), 'nour-articles-'))
  t.after(() => fs.rm(dir, { recursive: true, force: true }))
  const root = path.join(dir, 'public')
  await fs.mkdir(root)
  await fs.writeFile(path.join(root, 'index.html'), 'landing')
  await fs.writeFile(path.join(root, 'sitemap.xml'), '<?xml version="1.0"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"><url><loc>https://adhkar.ir/adhkar</loc></url></urlset>')
  return { root, stateFile: path.join(dir, 'private', 'articles.json'), now: NOW }
}
const read = (f, url = 'blog/index.html') => fs.readFile(path.join(f.root, url), 'utf8')

test('newly published articles appear as complete HTML, all language indexes and sitemap update', async t => {
  const f = await fixture(t)
  await refreshArticles({ ...f, posts: [post('first')] })
  await refreshArticles({ ...f, posts: [post('first'), post('second')] })
  assert.match(await read(f), /\/blog\/second/)
  assert.match(await read(f, 'blog/second/index.html'), /متن مقاله/)
  assert.match(await read(f, 'ar/blog/index.html'), /\/blog\/second/)
  assert.match(await read(f, 'ckb/blog/index.html'), /\/blog\/second/)
  const sitemap = await read(f, 'sitemap.xml')
  assert.match(sitemap, /https:\/\/adhkar.ir\/adhkar</)
  assert.match(sitemap, /https:\/\/adhkar.ir\/blog\/second</)
  assert.equal((sitemap.match(/<loc>https:\/\/adhkar.ir\/blog<\/loc>/g) || []).length, 1)
  assert.equal(await read(f, 'index.html'), 'landing')
})

test('editing invalidates HTML even within the same second; view counters do not rewrite HTML', async t => {
  const f = await fixture(t)
  await refreshArticles({ ...f, posts: [post('first')] })
  const edit = post('first', { title: 'عنوان اصلاح‌شده' })
  assert.equal((await refreshArticles({ ...f, posts: [edit] })).changed, true)
  assert.match(await read(f, 'blog/first/index.html'), /عنوان اصلاح‌شده/)
  const html = await fs.stat(path.join(f.root, 'blog/index.html'))
  assert.equal((await refreshArticles({ ...f, posts: [{ ...edit, views: 100 }] })).changed, false)
  assert.equal((await fs.stat(path.join(f.root, 'blog/index.html'))).mtimeMs, html.mtimeMs)
  // A fresh deployment may replace HTML with a fallback snapshot: --force must repair it.
  await fs.writeFile(path.join(f.root, 'blog/index.html'), 'old build')
  await refreshArticles({ ...f, posts: [edit], force: true })
  assert.match(await read(f), /عنوان اصلاح‌شده/)
})

test('unpublished/deleted pages disappear, future publication goes live on the next refresh', async t => {
  const f = await fixture(t)
  const scheduled = post('scheduled', { published_at: new Date(NOW + 60000).toISOString() })
  await refreshArticles({ ...f, posts: [post('first'), post('deleted'), scheduled] })
  assert.doesNotMatch(await read(f), /\/blog\/scheduled/)
  await refreshArticles({ ...f, posts: [post('first', { status: 'draft' }), scheduled], now: NOW + 61000 })
  assert.match(await read(f), /\/blog\/scheduled/)
  for (const slug of ['first', 'deleted']) {
    await assert.rejects(read(f, `blog/${slug}/index.html`), { code: 'ENOENT' })
    assert.doesNotMatch(await read(f, 'sitemap.xml'), new RegExp(`/blog/${slug}<`))
  }
  await refreshArticles({ ...f, posts: [] })
  assert.doesNotMatch(await read(f), /\/blog\/scheduled/)
  assert.match(await read(f), /به‌زودی/)
})

test('known Arabic translations remain native; untranslated articles do not invent translations', async t => {
  const f = await fixture(t)
  await refreshArticles({ ...f, posts: [post('virtue-of-remembering-allah'), post('new-persian')] })
  assert.match(await read(f, 'ar/blog/virtue-of-remembering-allah/index.html'), /فضل ذكر الله/)
  await assert.rejects(read(f, 'ar/blog/new-persian/index.html'), { code: 'ENOENT' })
})

test('unsafe source, invalid dates or invalid sitemap preserve the last good public output', async t => {
  const f = await fixture(t)
  await refreshArticles({ ...f, posts: [post('first')] })
  const before = await read(f)
  for (const posts of [[post('../escape')], [post('first'), post('first')], [post('bad', { published_at: 'invalid' })]]) {
    await assert.rejects(refreshArticles({ ...f, posts }))
    assert.equal(await read(f), before)
  }
  await fs.writeFile(path.join(f.root, 'sitemap.xml'), 'invalid')
  await assert.rejects(refreshArticles({ ...f, posts: [post('second')] }))
  assert.equal(await read(f), before)
})

test('HTML and JSON-LD escape article data, preventing script breakout', async t => {
  const f = await fixture(t)
  await refreshArticles({ ...f, posts: [post('safe', { title: '</script><script>alert(1)</script>', content: '<img src=x onerror=alert(1)>' })] })
  const html = await read(f, 'blog/safe/index.html')
  assert.doesNotMatch(html, /<script>alert|<img src=x/)
  for (const match of html.matchAll(/<script type="application\/ld\+json">([\s\S]*?)<\/script>/g)) JSON.parse(match[1])
  assert.match(html, /rel="canonical" href="https:\/\/adhkar.ir\/blog\/safe"/)
})

test('API pagination is complete; failure or malformed data never becomes an empty list', async () => {
  const seen = []
  const fetcher = async url => {
    seen.push(url)
    return { ok: true, json: async () => ({ data: [post(`page-${seen.length}`)], meta: { last_page: 2 } }) }
  }
  assert.equal((await fetchPosts('http://fixture/api', fetcher)).length, 2)
  assert.equal(seen.length, 2)
  await assert.rejects(fetchPosts('http://fixture/api', async () => ({ ok: false, status: 503 })), /503/)
  await assert.rejects(fetchPosts('http://fixture/api', async () => ({ ok: true, json: async () => ({ data: [] }) })), /Invalid/)
  await assert.rejects(fetchPosts('http://fixture/api', async () => { throw new Error('offline') }), /offline/)
})
