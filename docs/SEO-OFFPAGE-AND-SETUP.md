# SEO: off-page work and account setup (things code cannot do)

## 1. Cafe Bazaar and Myket listings (highest impact)
Store pages often rank in Google for "برنامه ..." queries. Use the same wording as the website.

- **Title:** اذکار نور؛ اذکار صبحگاهی و شامگاهی اهل سنت
- **Short description:** اذکار صبح و شام اهل سنت با متن عربی، ترجمهٔ فارسی و پخش صوتی؛ آفلاین و بدون تبلیغ.
- **Long description:** start with the sentence above, then list: اذکار صبحگاهی و شامگاهی، پخش صوتی با صدای مشاری راشد العفاسی، اوقات شرعی، قبله‌نما، ذکرشمار، چک‌لیست روزانه، قرآن کریم، فارسی/عربی/کوردی.
- Add the website link `https://adhkar.ir` as the developer site; ask satisfied users for ratings (real ratings only).

## 2. Backlinks (ongoing)
Targets: Sunni community and mosque sites, seminary and educational blogs, Telegram/Instagram channels you already run, app review blogs.
Offer: a shareable adhkar image or PDF, a link to `/adhkar/sobhgahi` or `/adhkar/shamgahi`, and the app page `/app`.
Message template: «سلام؛ اذکار نور یک اپلیکیشن رایگان و بدون تبلیغ برای اذکار صبح و شام اهل سنت است. اگر مایل باشید، صفحهٔ اذکار صبحگاهی ما با متن و ترجمه برای مخاطبان شما مفید است: https://adhkar.ir/adhkar/sobhgahi»
Avoid paid or automated link schemes.

## 3. Bing Webmaster Tools and IndexNow
1. Sign in at bing.com/webmasters, add `https://adhkar.ir`, import from Google Search Console (fastest).
2. The IndexNow key file is `public/007823b9be0f59c0fa2a8d7f5082b3b0.txt`. After each deploy run `npm run indexnow` in `Nour Adhkar/` to notify participating engines of every sitemap URL.

## 4. Google API access (PageSpeed, CrUX, Search Console API)
1. In Google Cloud console create a project, enable "PageSpeed Insights API", "Chrome UX Report API" and "Google Search Console API".
2. Create an API key; put it in `~/.config/claude-seo/google-api.json` as `{"api_key": "..."}` (never commit it).
3. For Search Console API: create an OAuth client (Desktop), then run the `/seo google` auth step, or add a service account and grant it access in Search Console settings.

## 5. After each deploy
- Check `https://adhkar.ir/sitemap.xml` lists the new URLs.
- In Search Console: URL Inspection > Request indexing for new pages.
- Adhkar content comes from `https://api.adhkar.ir/api` at build time, with the committed snapshot as a fallback. Articles are refreshed automatically on the server every minute; no commit or deployment is needed after changing them in the panel. See [dynamic article publishing](DYNAMIC-ARTICLES.md) for freshness, outage behavior and diagnostics.

## 6. Content still needed from you
- A named reviewer for the adhkar text and hadith references per dhikr (the data has no `source` field yet; the pages do not invent sources).
- More articles in the admin panel (published articles appear automatically under `/blog`, normally within one minute).
