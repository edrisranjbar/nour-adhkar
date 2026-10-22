// Prerenders the landing page into dist/index.html after `vite build`.
//
// Lighthouse measured most of the landing page's Largest Contentful Paint as render delay: the
// page was empty until the JavaScript bundle had been downloaded, parsed and run. With the HTML
// in place the hero paints immediately and main.js hydrates it (createSSRApp) on "/".
// Other routes share index.html (SPA fallback), so an inline script clears the prerendered page
// before it is painted anywhere but "/".
import { build } from 'vite'
import vue from '@vitejs/plugin-vue'
import { fileURLToPath, pathToFileURL } from 'node:url'
import { readFileSync, readdirSync, writeFileSync, rmSync } from 'node:fs'
import path from 'node:path'

const root = fileURLToPath(new URL('..', import.meta.url))
const outDir = path.join(root, '.landing-ssr')

await build({
  root,
  configFile: false,
  logLevel: 'warn',
  mode: 'production',
  // Production templates drop comments; keeping them here would shift every node during hydration.
  plugins: [vue({ template: { compilerOptions: { comments: false } } })],
  resolve: { alias: { '@': path.join(root, 'src') } },
  // Same asset naming as vite.config.js, so the prerendered URLs match the client build's files.
  build: {
    ssr: 'src/entry-landing-ssr.js', outDir, emptyOutDir: true, assetsDir: 'assets',
    rollupOptions: { output: { assetFileNames: 'assets/[name].[hash][extname]' } }
  }
})

const { render } = await import(pathToFileURL(path.join(outDir, 'entry-landing-ssr.js')).href)
const html = await render()

const indexPath = path.join(root, 'dist', 'index.html')
const index = readFileSync(indexPath, 'utf8')
const start = index.indexOf('<div id="app">')
const end = index.indexOf('</body>', start)
const closing = index.lastIndexOf('</div>', end)
if (start < 0 || end < 0 || closing < start) throw new Error('prerender: #app not found in dist/index.html')

const clearOtherRoutes =
  '<script>if(location.pathname!=="/"){var a=document.getElementById("app");a.innerHTML="";a.removeAttribute("data-ssr")}</script>'
const next =
  index.slice(0, start) +
  `<div id="app" data-ssr="landing">${html}</div>\n    ${clearOtherRoutes}\n    ` +
  index.slice(closing + '</div>'.length).replace(/^\s*/, '')

// Preload the hero's three weights (body 400, buttons 700, title 900) so they arrive with the
// page instead of after the CSS. They use font-display: optional (fonts.css), so a late font never
// re-flows the hero (a layout shift). The video poster is not preloaded: measured, it made the
// fonts miss the first paint and cost more than it saved.
const assets = readdirSync(path.join(root, 'dist', 'assets'))
const preloads = ['Regular', 'Bold', 'Black']
  .map((w) => assets.find((f) => f.startsWith(`Vazirmatn-FD-${w}.`) && f.endsWith('.woff2')))
  .filter(Boolean)
  .map((f) => `<link rel="preload" href="/assets/${f}" as="font" type="font/woff2" crossorigin>`)
  .join('\n    ')

// On "/" the page is already drawn by the HTML, so the app bundle only adds interactivity (language,
// theme, video): load it after the page has loaded, so it does not compete with the hero for
// bandwidth. Every other route needs the app to render and loads it at once.
const entry = /<script type="module" crossorigin src="([^"]+)"><\/script>/
const src = next.match(entry)?.[1]
if (!src) throw new Error('prerender: app entry script not found in dist/index.html')
const loader =
  `<script>(function(){function go(){var s=document.createElement("script");s.type="module";s.crossOrigin="";s.src=${JSON.stringify(src)};document.head.appendChild(s)}` +
  `if(location.pathname!=="/")return go();if(document.readyState==="complete")return go();` +
  `addEventListener("load",function(){setTimeout(go,0)},{once:true})})()</script>`

writeFileSync(indexPath, next.replace(entry, loader).replace('</head>', `    ${preloads}\n</head>`))
rmSync(outDir, { recursive: true, force: true })
console.log(`Landing page prerendered into dist/index.html (${Math.round(html.length / 1024)} KB of HTML)`)
