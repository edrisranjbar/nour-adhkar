# Public app update history

The website exposes `/updates`, linked from the landing-page footer. It reads `GET /api/app-versions` from the configured `VITE_API_URL`; the default production API is `https://api.adhkar.ir/api`.

The existing admin panel's **versions** section (`/admin/versions`) remains the only source of release notes. Save the version name/code, release date, and multiline changelog there, then mark the version as published. Published records appear automatically when the updates page is loaded; edited notes are returned on the next fetch. Draft/unpublished records are excluded. Publication is the existing explicit flag, not a new release-date scheduling mechanism.

The API returns a standard Laravel paginator with 20 entries per page, ordered by descending version code. Public fields are `versionName`, `versionCode`, `changelog`, and `releaseDate`. The Android latest-version endpoint (`/api/app-version`) remains unchanged. No schema change, manual duplicate changelog file, or static rebuild is needed when admin content changes.

The Persian RTL page renders each nonempty changelog line as escaped text, formats release dates in the Persian calendar, and supports loading, empty, retry, and older-version states. No changelog HTML is executed. Route metadata and sitemap generation include `/updates`.

The content generator uses `scripts/updates-document.mjs` to give `dist/updates/index.html` its own title, description, canonical, structured data and the current first page of release notes in the initial response. It removes the homepage prerender/cleanup code while preserving Vue bootstrap and pagination. Direct visits work with existing nginx static routing. Vue still fetches current admin data at runtime; initial static release notes are refreshed by a website build, not the article timer.

Validation:

- Backend: `php artisan test --filter=AppVersionsTest` from `backend/`.
- Frontend: `npx vitest run src/views/__tests__/UpdatesView.spec.js` from `Nour Adhkar/`.
- Initial HTML: `node --test scripts/__tests__/updates-document.test.mjs`; after building, `node scripts/verify-built-seo.mjs`.
- Deploy both the API route/controller and frontend bundle together. This source change alone does not deploy the public site.
