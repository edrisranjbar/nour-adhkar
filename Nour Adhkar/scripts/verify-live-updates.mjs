import assert from 'node:assert/strict'

async function read(url) {
  const response = await fetch(url, { signal: AbortSignal.timeout(30000) })
  assert.equal(response.status, 200, `${url}: HTTP ${response.status}`)
  return response
}

const history = await (await read('https://api.adhkar.ir/api/app-versions')).json()
assert.ok(Array.isArray(history.data), 'Release history must contain a data array')
assert.ok(history.data.length <= 20, 'Release history must be paginated')
for (const [index, release] of history.data.entries()) {
  assert.equal(typeof release.changelog, 'string')
  assert.equal(typeof release.versionName, 'string')
  assert.match(release.releaseDate, /^\d{4}-\d{2}-\d{2}$/)
  assert.ok(!('published' in release), 'Internal publication fields must not be exposed')
  if (index) assert.ok(history.data[index - 1].versionCode > release.versionCode)
}
if (history.data.length) {
  const latest = await (await read('https://api.adhkar.ir/api/app-version')).json()
  assert.equal(history.data[0].versionCode, latest.versionCode)
  assert.equal(history.data[0].changelog, latest.changelog)
}

const html = await (await read('https://adhkar.ir/updates')).text()
assert.match(html, /id="app"/)
assert.match(html, /\/assets\//)
const home = await (await read('https://adhkar.ir/')).text()
assert.match(home, /href="\/updates"/)
const sitemap = await (await read('https://adhkar.ir/sitemap.xml')).text()
assert.ok(sitemap.includes('https://adhkar.ir/updates</loc>'))
console.log(`Live updates page, homepage link, sitemap and release API verified (${history.data.length} releases on first page).`)
