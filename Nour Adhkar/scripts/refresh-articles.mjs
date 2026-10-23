// Incremental server-side regeneration: no build, npm packages or browser JS required.
import fs from 'node:fs/promises'
import path from 'node:path'
import { createHash, randomUUID } from 'node:crypto'
import { fileURLToPath, pathToFileURL } from 'node:url'
import { renderBlogPages } from './generate-content-pages.mjs'

const HERE = path.dirname(fileURLToPath(import.meta.url))
const SITE = 'https://adhkar.ir'
const BLOG_PATH = /^\/(?:ar\/|ckb\/)?blog(?:\/[\p{L}\p{N}_-]+)?$/u
const SLUG = /^[\p{L}\p{N}_-]+$/u
const xml = s => String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;')

export async function fetchPosts(api, fetcher = fetch) {
  const all = []
  for (let page = 1; page <= 100; page++) {
    const response = await fetcher(`${api}/posts?page=${page}`, {
      signal: AbortSignal.timeout(15000), headers: { Accept: 'application/json', 'Cache-Control': 'no-cache' }
    })
    if (!response.ok) throw new Error(`Articles API returned HTTP ${response.status}`)
    const json = await response.json()
    const last = json.meta?.last_page
    if (!Array.isArray(json.data) || !Number.isInteger(last) || last < page || last > 100) {
      throw new Error('Invalid articles API response; keeping the current website')
    }
    all.push(...json.data)
    if (page === last) return all
  }
  throw new Error('Articles API pagination did not finish')
}

function publishedPosts(posts, now) {
  const seen = new Set()
  if (!Array.isArray(posts) || posts.some(p => !p || !['draft', 'published'].includes(p.status)
    || (p.status === 'published' && !Number.isFinite(Date.parse(p.published_at))))) {
    throw new Error('Invalid article publication data')
  }
  return posts.filter(p => p.status === 'published' && Date.parse(p.published_at) <= now).map(p => {
    if (!SLUG.test(p.slug || '') || !p.title || typeof p.content !== 'string' || seen.has(p.slug)) {
      throw new Error('Invalid or duplicate article; keeping the current website')
    }
    seen.add(p.slug)
    if (!Number.isFinite(Date.parse(p.updated_at || p.published_at))) throw new Error('Invalid article date')
    // Only fields that affect the page: view counters must not trigger regeneration.
    return Object.fromEntries(['id', 'slug', 'title', 'content', 'excerpt', 'meta_title', 'meta_description',
      'published_at', 'updated_at'].map(k => [k, p[k] ?? null]).concat([['user', p.user ? { name: p.user.name } : null]]))
  }).sort((a, b) => a.slug.localeCompare(b.slug))
}

export function mergeSitemap(current, pages) {
  if (!/<urlset\b[^>]*>[\s\S]*<\/urlset>/.test(current)) throw new Error('Website sitemap is missing or invalid')
  const base = current.replace(/\s*<url>\s*[\s\S]*?<\/url>/g, block => {
    const loc = block.match(/<loc>([^<]+)<\/loc>/)?.[1]
    return loc && loc.startsWith(SITE) && BLOG_PATH.test(loc.slice(SITE.length)) ? '' : block
  })
  const entries = pages.map(p => `  <url><loc>${SITE}${xml(p.path)}</loc><lastmod>${p.lastmod}</lastmod><changefreq>${p.changefreq || 'monthly'}</changefreq><priority>${p.priority}</priority></url>`).join('\n')
  return base.replace('</urlset>', `${entries}\n</urlset>`)
}

async function readJson(file) {
  try { return JSON.parse(await fs.readFile(file, 'utf8')) } catch (e) {
    if (e.code === 'ENOENT') return null
    throw e
  }
}

async function atomicWrite(file, content) {
  await fs.mkdir(path.dirname(file), { recursive: true })
  const temporary = `${file}.${randomUUID()}.tmp`
  try {
    await fs.writeFile(temporary, content, { mode: 0o644 })
    await fs.rename(temporary, file)
  } finally { await fs.rm(temporary, { force: true }) }
}

function pageFile(root, urlPath) {
  if (!BLOG_PATH.test(urlPath)) throw new Error('Unsafe article path')
  return path.join(root, ...urlPath.slice(1).split('/'), 'index.html')
}

export async function refreshArticles({ root, stateFile, posts, now = Date.now(), force = false }) {
  root = path.resolve(root)
  // Never create a new web root by mistake or publish over an incomplete deployment.
  await fs.access(path.join(root, 'index.html'))
  const sitemapFile = path.join(root, 'sitemap.xml')
  const sitemap = await fs.readFile(sitemapFile, 'utf8')
  const visible = publishedPosts(posts, now)
  const renderer = await Promise.all(['generate-content-pages.mjs', 'content-i18n.mjs', '../content-snapshot/posts-ar.json']
    .map(file => fs.readFile(path.resolve(HERE, file))))
  const revision = createHash('sha256').update(JSON.stringify(visible)).update(Buffer.concat(renderer)).digest('hex')
  const previous = await readJson(stateFile)
  if (!force && previous?.revision === revision && previous?.root === root) {
    // Private heartbeat proves the timer is running; public HTML retains Last-Modified/ETag.
    await atomicWrite(`${stateFile}.status`, JSON.stringify({ checkedAt: new Date(now).toISOString(), revision, articles: visible.length }))
    return { changed: false, articles: visible.length, revision }
  }
  const pages = renderBlogPages(visible)
  const nextSitemap = mergeSitemap(sitemap, pages)
  const paths = new Set(pages.map(p => p.path))
  // All rendering and validation happens before the first public write. Write details before indexes.
  for (const page of [...pages].sort((a, b) => b.path.split('/').length - a.path.split('/').length)) {
    await atomicWrite(pageFile(root, page.path), page.html)
  }
  const oldPaths = previous?.root === root ? previous.paths : [...sitemap.matchAll(/<loc>https:\/\/adhkar\.ir([^<]+)<\/loc>/g)].map(m => m[1]).filter(p => BLOG_PATH.test(p))
  for (const stale of oldPaths || []) {
    if (!paths.has(stale)) await fs.rm(pageFile(root, stale), { force: true })
  }
  await atomicWrite(sitemapFile, nextSitemap)
  await atomicWrite(stateFile, JSON.stringify({ root, revision, paths: [...paths] }))
  await atomicWrite(`${stateFile}.status`, JSON.stringify({ checkedAt: new Date(now).toISOString(), revision, articles: visible.length }))
  return { changed: true, articles: visible.length, revision }
}

async function main() {
  const args = process.argv.slice(2)
  const arg = key => args[args.indexOf(key) + 1]
  if (!args.includes('--root') || !args.includes('--state')) throw new Error('Required: --root <existing web root> --state <private manifest path>')
  const posts = await fetchPosts(process.env.CONTENT_API_URL || 'https://api.adhkar.ir/api')
  const result = await refreshArticles({ root: arg('--root'), stateFile: arg('--state'), posts, force: args.includes('--force') })
  if (result.changed) console.log(`Articles refreshed: ${result.articles} published; revision ${result.revision.slice(0, 12)}`)
}

if (process.argv[1] && import.meta.url === pathToFileURL(path.resolve(process.argv[1])).href) {
  try { await main() } catch (error) { console.error(`Article refresh failed: ${error.message}`); process.exitCode = 1 }
}
