# Dynamic articles with static delivery

Articles use incremental server-side regeneration. The server checks the published posts API once per minute and renders complete HTML only when article content changes. Visitors receive ordinary nginx files: no browser API request, loading spinner, Node server, or PHP rendering on the request path. New publication, edits, unpublication, deletion and scheduled publication normally reach the website within one minute plus the API/render time. This is bounded-freshness publishing, not per-request SSR.

The shared renderer in `Nour Adhkar/scripts/generate-content-pages.mjs` serves both the initial build and `scripts/refresh-articles.mjs`. Persian, Arabic and Kurdish blog indexes, existing Arabic article translations, canonical URLs, structured data, related links and the sitemap are updated together. Untranslated articles remain Persian; translations are never invented. Other website pages remain build-time generated.

## Runtime and failure behavior

- `deploy/refresh-articles.sh` runs under the deploy user's crontab every minute. It requires Node 18+ (20 recommended), `flock` and outbound access to the articles API, with no npm dependencies.
- `CONTENT_API_URL` defaults to `https://api.adhkar.ir/api`. The updater fetches every page and rejects failed/malformed responses instead of substituting an old snapshot or empty list.
- The revision hashes render-relevant content and renderer/translation sources, so same-second edits work. View counters do not trigger public writes. Unchanged pages keep their file timestamps and HTTP validators.
- Files are rendered/validated before publication, then each is replaced using an atomic same-directory rename. Detail pages precede indexes. Stale managed article HTML is removed, while unrelated website files and sitemap entries are retained. This is atomic per file, not a transaction across all languages/files.
- API failure leaves the last successful pages in place and writes an error to `backend/storage/logs/website-articles.log`. During an outage, freshness can exceed one minute; already cached content remains readable.
- State and the successful-check heartbeat are private: `backend/storage/app/website/articles.json` and `articles.json.status`. They are not served from the public root.
- Deployment and refresh share `publish.lock`, preventing the timer from writing into a website directory being replaced. Deployment forces a live refresh and restores the previous website if that refresh fails.

## Deployment and verification

`DEPLOY_WEB_ROOT` must match the active nginx root, not merely a local build directory. On this host it is `/home/edris/nour-adhkar/NourAdhkar`. `deploy/install-article-timer.sh` preserves other cron entries and replaces only the entry ending in `# nour-adhkar-articles`.

The deploy workflow tests regeneration, builds the website, publishes it, checks all current article links/detail HTML/canonicals/sitemap and a genuine 404, then waits for a successful timer heartbeat independent of deployment. CI and local checks:

```sh
cd 'Nour Adhkar'
node --test scripts/__tests__/refresh-articles.test.mjs
npm run build
node scripts/verify-live-articles.mjs
```

For a manual server refresh (uses the same lock):

```sh
APP_DIR=/home/edris/nour-adhkar WEB_ROOT=/home/edris/nour-adhkar/NourAdhkar bash deploy/refresh-articles.sh --force
```

To disable automatic publication, remove only the marked cron entry. Existing HTML remains available. Check the private status timestamp and refresh log when articles lag behind the API; a deployment is no longer required to publish content.
