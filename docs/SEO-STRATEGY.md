# adhkar.ir SEO strategy (Oct 2026)

Goal: own the Persian "adhkar" search space for the Ahl al-Sunnah audience and turn it into app installs (Cafe Bazaar / Myket).
Targets below are goals, not guarantees. Google decides rankings.

## 1. Baseline (Search Console, 19 Jul - 18 Oct 2026)

| Metric | Value |
|---|---|
| Clicks / impressions | 72 / 821 |
| Avg CTR / avg position | 8.8% / 5.9 |
| Indexed pages | 2 (home + /privacy) |
| Content pages on site | 0 (home is a ~100-word landing page) |

Queries that already show impressions:

| Query | Impr. | Clicks | Pos. | Read |
|---|---|---|---|---|
| اذکار نور (brand) | 22 | 12 | 1.8 | brand, fine |
| اذکار شامگاهی | 175 | 1 | 7.8 | page 1, near-zero CTR: title/snippet mismatch |
| اذکار شامگاه | 63 | 0 | 7.6 | same |
| اذکار صبح و شام شبکه نور | 102 | 6 | 5.7 | TV-channel intent, ignore |
| اذکار شامگاهی/صبح و شام اهل سنت صوتی | 10 | 0 | 9-10 | audio + Ahl al-Sunnah is a real intent |
| برنامه اذکار صبح و شام اهل سنت | 2 | 0 | 5.5 | app intent exists, tiny |
| اذکار صبحگاه | none | - | - | zero visibility |

Insight: Google already trusts the domain for the evening queries. The gap is dedicated, text-rich pages that match the phrasing searchers use (صبحگاهی / شامگاهی / صبح و شام), plus a snippet that says so.

## 2. Competitors (to verify)

I could not pull Iranian SERPs (search tool is US-only, no DataForSEO). Before Phase 2, check these by hand in Google.ir results for the target queries and record the top 10:
- Persian Islamic text sites that publish full adhkar pages (the main ranking competitors for صبحگاهی/شامگاهی).
- Cafe Bazaar / Myket app-listing pages (often rank for "برنامه ..." queries).
- Sunni scholar and seminary sites, and Telegram/Instagram channels (referrals, not SERP rivals).
Record per competitor: page type, word count, whether Arabic + Persian text + audio + sources are present, schema, title pattern. Our edge to build: clean RTL pages, audio, offline app, 3 languages, no ads.

## 3. Keyword map (one primary intent per URL)

| URL (proposed) | Primary keyword | Secondary |
|---|---|---|
| `/` | اذکار نور، اپلیکیشن اذکار | اپلیکیشن اهل سنت، اذکار اندروید |
| `/adhkar` (hub) | اذکار و ادعیه | مجموعه اذکار روزانه |
| `/adhkar/sobhgahi` | اذکار صبحگاهی | اذکار صبحگاه، اذکار صبح، متن اذکار صبح با ترجمه |
| `/adhkar/shamgahi` | اذکار شامگاهی | اذکار شامگاه، اذکار شب، متن اذکار شام |
| `/adhkar/sobh-o-sham-ahl-sunnat` | اذکار صبح و شام اهل سنت | صوتی، با صدا، pdf |
| `/adhkar/ba-d-az-namaz` | اذکار بعد از نماز | تسبیحات فاطمه |
| `/adhkar/qabl-az-khab` | اذکار قبل از خواب | دعای خواب |
| `/adhkar/istikhara` | دعای استخاره | متن و ترجمه |
| `/app` | اپلیکیشن اهل سنت | دانلود اذکار نور، کافه بازار |
| `/blog/...` | long-tail (section 5) | |

Do not let two pages target the same phrase. Spelling variants (صبحگاه/صبحگاهی) go on the same page, in the H1/H2 and body.

Later (Phase 3): Arabic (أذكار الصباح والمساء) and Sorani (ئەزکاری بەیانی و ئێوارە) pages on their own URLs with hreflang. The current JS language switcher is invisible to Google because all languages share one URL.

## 4. Technical plan

1. **Make content crawlable without JS.** Today the SPA ships an empty shell (plus the static fallback I added to `index.html`). Move to prerendering/SSG for the public routes (vite-ssg or vite-plugin-prerender) so each URL has its own HTML, title, description, canonical and JSON-LD. Admin routes stay client-only and noindex.
2. **Per-page head**: unique title (<=60 chars), description (120-155), canonical, OG/Twitter, `lang="fa"`.
3. **Titles/descriptions now (quick win):**
   - Home title: `اذکار نور | اذکار صبحگاهی و شامگاهی اهل سنت، صوتی و آفلاین`
   - Home description should name صبحگاهی، شامگاهی، اهل سنت، صوتی, free, offline.
4. **Server** (done Oct 12): real 404/410, www to apex, HTTPS. Keep `robots.txt` + sitemap in sync after each new page (generator script already reads a route list; make it read the router).
5. **Sitemap** `lastmod` should be each page's real change date, not the build date.
6. **Schema** (JSON-LD, in prerendered HTML):
   - Home/App page: `SoftwareApplication` (done), add `aggregateRating` only when real store ratings exist.
   - Content pages: `BreadcrumbList`, `Article` (author, dateModified), reference list.
   - Site: `Organization` + `WebSite`.
   - Do not add `FAQPage` for SERP benefit (retired) and never `HowTo`.
7. **Performance**: serve images as WebP/AVIF with width/height, preload the Persian font (font-display swap), keep CLS 0, INP under 200 ms, LCP under 2.5 s. Measure with PageSpeed/CrUX once API access is set up (needs a Google API key).
8. **Internal linking**: hub to every page, each page links to siblings (صبحگاهی <-> شامگاهی) and to the app page; breadcrumbs; footer links.
9. **Bing Webmaster Tools + IndexNow** as a second channel.

## 5. Content plan

Content pages need the actual text, not summaries. Target 600-1,200 words plus the adhkar list.

Each adhkar page should contain:
- Intro: when to read (after Fajr until sunrise / after Asr until Maghrib), brief virtue with a cited hadith.
- Every dhikr: Arabic text, Persian translation, repetition count, source (e.g. Hisn al-Muslim / Sahih collection reference).
- Audio player (existing recordings, if rights allow) and a "listen in the app" CTA.
- Printable/shareable image or PDF (earns links and shares).
- Related pages + app download button (Bazaar, Myket).

E-E-A-T for religious content (important): name who compiled and who reviewed the text, cite primary sources for every hadith, show "last reviewed" date, link an About page, and keep the Ahl al-Sunnah positioning explicit and consistent.

Publishing order (refine, don't expand: finish the highest-value pages first):
1. Weeks 1-2: صبحگاهی + شامگاهی pages, home title/description rewrite.
2. Weeks 3-4: صبح و شام اهل سنت (صوتی) + `/app` page + hub.
3. Weeks 5-8: بعد از نماز، قبل از خواب، استخاره.
4. Weeks 9-24: 2 blog posts/month on long-tail questions (virtue and timing of adhkar, the best times, children and adhkar, Ramadan routines, Quran du'as) each linking to a hub page.

## 6. Off-page and app-store layer

- **Cafe Bazaar and Myket listings**: put the same keywords in the app name/subtitle/description (اذکار صبحگاهی و شامگاهی اهل سنت). These pages often rank in Google for "برنامه ..." queries. Link them to adhkar.ir and ask for reviews: real ratings are the fastest trust signal.
- **Backlinks**: Sunni community sites, mosque and seminary sites, Telegram/Instagram channels you already run, and educational blogs. Offer the shareable adhkar images/PDFs. Avoid paid or spammy links.
- **Brand**: keep "اذکار نور" spelling consistent everywhere (site, stores, social).

## 7. Measurement

Weekly in Search Console (Performance > Queries/Pages): impressions, avg position and CTR for the keyword map above. Monthly: indexed pages, Core Web Vitals, referring domains.

| Metric | Now | 3 months | 6 months | 12 months |
|---|---|---|---|---|
| Indexed pages | 2 | 10 | 20 | 40 |
| Clicks / 90 days | 72 | 300 | 900 | 3,000 |
| اذکار شامگاهی position | 7.8 | top 5 | top 3 | top 3 |
| اذکار صبحگاهی/صبح و شام اهل سنت | none | top 20 | top 10 | top 3-5 |
| Home CTR (non-brand) | ~0% | 3% | 4% | 5% |

How we would know it failed: after 8 weeks the new pages are not indexed (check URL Inspection), or impressions rise but CTR stays under 1% (titles/snippets wrong), or positions drop for the evening queries (new pages cannibalizing the home page).

## 8. Roadmap

- **Phase 0 (this week):** rewrite home title/description; request reindexing; verify Search Console validation results.
- **Phase 1 (weeks 1-4):** prerendering, صبحگاهی/شامگاهی/اهل سنت pages, hub, `/app`, per-page schema, store listing keyword update.
- **Phase 2 (weeks 5-12):** remaining adhkar pages, breadcrumbs, Bing/IndexNow, first outreach and shareable assets.
- **Phase 3 (weeks 13-24):** blog cadence, Arabic and Sorani pages with hreflang, review/ratings drive, link building.
- **Phase 4 (months 7-12):** refresh content by Search Console data, expand to winning long-tail queries, seasonal content (Ramadan).

## 9. Dependencies and risks

- Needs your decision that the site may publish the adhkar text and audio from the app (rights and accuracy review).
- Prerendering touches the build; test before each deploy (the earlier deploy failure was a build error).
- Small search volume: expect single-digit-thousands of impressions, not millions. Rankings take weeks to months.
- Iran-specific network filtering can affect crawl and user behavior; track from Search Console only, not third-party rank tools.
- Religious accuracy: every text and source needs review before publishing.
