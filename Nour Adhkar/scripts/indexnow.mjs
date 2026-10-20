#!/usr/bin/env node
// Tells Bing/other IndexNow engines about every URL in the live sitemap. Run after a deploy:
//   npm run indexnow
const KEY = '007823b9be0f59c0fa2a8d7f5082b3b0'
const HOST = 'adhkar.ir'
const sitemap = await (await fetch('https://' + HOST + '/sitemap.xml')).text()
const urlList = [...sitemap.matchAll(/<loc>([^<]+)<\/loc>/g)].map(m => m[1])
const res = await fetch('https://api.indexnow.org/indexnow', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json; charset=utf-8' },
  body: JSON.stringify({ host: HOST, key: KEY, keyLocation: 'https://' + HOST + '/' + KEY + '.txt', urlList })
})
console.log('IndexNow', res.status, urlList.length + ' URLs')
