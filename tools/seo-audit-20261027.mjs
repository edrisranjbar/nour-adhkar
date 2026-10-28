import fs from 'node:fs/promises'
import path from 'node:path'
import { createRequire } from 'node:module'
const require = createRequire(new URL('../Nour Adhkar/package.json', import.meta.url))
const { JSDOM } = require('jsdom')
const origin = process.env.SEO_AUDIT_ORIGIN || 'https://adhkar.ir'
const output = path.resolve(process.env.SEO_AUDIT_OUTPUT || 'output/seo-audit-20261027')
await fs.mkdir(output, {recursive: true})
async function read(route) {
  const response = await fetch(new URL(route, origin), {signal: AbortSignal.timeout(25000)})
  const html = await response.text()
  const document = new JSDOM(html).window.document
  return {path: route,status: response.status,url: response.url,type: response.headers.get('content-type'),robotsHeader: response.headers.get('x-robots-tag'),title: document.title,description:document.querySelector('meta[name="description"]')?.content,canonical:[...document.querySelectorAll('link[rel="canonical"]')].map(n=>n.href),robots:document.querySelector('meta[name="robots"]')?.content,h1:[...document.querySelectorAll('h1')].map(n=>n.textContent.trim()),bodyLength: document.body?.textContent.trim().length,links:[...document.querySelectorAll('a[href]')].map(a=>a.getAttribute('href')),jsonLd:[...document.querySelectorAll('script[type="application/ld+json"]')].map(n=>{try{return JSON.parse(n.textContent)}catch{return 'INVALID_JSON'}}),html}
}
const sitemap = await read('/sitemap.xml')
const robots = await read('/robots.txt')
await fs.writeFile(path.join(output,'sitemap-before.xml'),sitemap.html)
await fs.writeFile(path.join(output,'robots-before.txt'),robots.html)
const urls=[...sitemap.html.matchAll(/<loc>(.*?)<\/loc>/g)].map(m=>new URL(m[1]).pathname)
const routes=[...new Set(['/', '/app', '/adhkar/sobhgahi','/adhkar/shamgahi','/adhkar/sobh-o-sham-ahl-sunnat','/seo-audit-missing-20261027',...urls])].slice(0,110)
const pages=[]
for (let i=0;i<routes.length;i+=5) {
  await Promise.all(routes.slice(i,i+5).map(async route=>{
    try {
      const page=await read(route)
      await fs.writeFile(path.join(output, route==='/'?'home.html':encodeURIComponent(route)+'.html'),page.html)
      delete page.html
      pages.push(page)
    }catch(error){pages.push({path:route,error:error.message})}
  }))
}
pages.sort((a,b)=>a.path.localeCompare(b.path))
const report={checkedAt:new Date().toISOString(),origin,sitemapUrls:urls.length,robots:robots.html,pages}
await fs.writeFile(path.join(output,'audit-before.json'),JSON.stringify(report,null,2))
console.log(JSON.stringify({checkedAt:report.checkedAt,sitemapUrls:urls.length,robots:robots.html,pages:pages.map(({path,status,error,title,canonical,robots,h1,bodyLength})=>({path,status,error,title,canonical,robots,h1,bodyLength}))},null,2))
