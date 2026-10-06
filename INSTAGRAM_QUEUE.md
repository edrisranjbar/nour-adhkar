# Instagram verse queue

Primary admin route: `https://api.adhkar.ir/admin/instagram-queue`, accessible from `صف اینستاگرام` in the Laravel panel's sidebar. Uses the existing panel session; no second login or JWT is needed. The website's Vue admin retains its `/admin/instagram-queue` route. Both clients share the same source catalog, queue, templates, and controllers. Android source, version, and release artifacts are unchanged.

## Workflow

1. Deploy the backend and frontend changes, then run `php artisan migrate --force` from `backend` using the normal deployment procedure.
2. Choose a suggested verse to create a draft. Repeated clicks return the same draft. No posts are created by migration.
3. Choose Khorramdel (default) or Rowwad, review the linked source, and edit the image text and caption. Choose a saved template, save the post's design, then approve to add to the ready queue; use arrows to order it.
4. Download the 1080 × 1350 JPEG and copy the caption. Publish manually to Instagram.
5. Paste the post's Instagram permalink and record publication. This records the admin's confirmation; it does not verify the post with Meta. Published entries are read-only archive records. Downloads never mark a post published.

The page supports manual publication and scheduled publication through a separately configured GitHub Actions worker. The owner reports `@nouradhkar` is a Creator account. Account creation alone does not configure API publishing. Scheduling stays disabled until backend publishing settings are enabled; Meta credentials must be supplied separately as repository secrets. No live Instagram publication has been verified.

## Content and design

- Eight editorial candidates: 94:5, 2:152, 13:28, 2:153, 93:3, 93:5, 20:46, 3:139. Themes: hope, remembrance, calm, patience, trust, and perseverance. No measured popularity or share ranking is claimed.
- Rowwad source verses from the Android app's `translation-fa-islamhouse.txt` (QuranEnc / Rowwad Translation Center, IslamHouse Persian team, v1.1.0). No generated or paraphrased verse text. The source catalog is immutable; image text, caption and design are editable. Khorramdel translations come verbatim from the existing Android `translation-fa-khorramdel.txt` asset (Tanzil fa.khorramdel). New drafts default to Khorramdel. The content migration preserves existing Rowwad text and attribution, including the published archive. An edited excerpt is credited as «برگرفته از» instead of a full translation. Review context and update the caption independently. Tanzil translation distribution is for non-commercial use; commercial use requires translator/publisher permission: https://tanzil.net/trans/.
- 93:3 and 93:5 address the Prophet; 20:46 addresses Moses and Aaron. Retain the translator's bracketed clarifications and consult the linked surrounding verses before writing commentary. Do not turn these into unqualified personal guarantees.
- Arabic text is preserved as source metadata from Tanzil Uthmani 1.1. Attribution and original copyright notice: `backend/resources/data/TANZIL-NOTICE.txt`; see https://tanzil.net/docs/text_license. Keep text intact and retain attribution in derivative distributions.
- Image: centered Persian text, generous empty space, verse reference and translation credit, existing Nour logo at bottom right. Bundled Vazirmatn (regular/bold) and Noto Naskh Arabic; no network requests to font services. Preview and export share one canvas renderer. Noto Naskh is distributed with its SIL Open Font License in `Nour Adhkar/src/assets/fonts/noto-naskh/OFL.txt`; source: https://github.com/google/fonts/tree/main/ofl/notonaskharabic.
- Account: `@nouradhkar` (Creator, owner confirmed); display name `اذکار نور | یادآوری‌های قرآنی`; bio `یادآوری‌های کوتاه از قرآن، برای دل و روزهای ما\nترجمه فارسی با ذکر منبع\nدریافت برنامه: adhkar.ir`. Check availability during sign-up.

## API and verification

All endpoints are within existing `auth:api` + `AdminMiddleware` protection:

- `GET /api/admin/instagram-queue`: source catalog, ordered entries, and saved templates.
- `POST /api/admin/instagram-queue`: `verse_key` from catalog, idempotent.
- `PUT /api/admin/instagram-queue/{id}`: caption (max 2200), `image_text` (max 1200), `translator_key` (khorramdel/rowwad), `template_id`, status; recording publication requires current queued status and an Instagram post/reel permalink. Selected template design is copied into the post. Caption/status-only updates preserve that snapshot. Legacy `theme` input remains accepted.
- `PUT /api/admin/instagram-queue/order`: complete, distinct list of currently queued IDs; stale membership returns 409.
- `DELETE /api/admin/instagram-queue/{id}`: draft/queued only.
- `POST /api/admin/instagram-templates`, `PUT /api/admin/instagram-templates/{id}`, `DELETE /api/admin/instagram-templates/{id}`: manage named designs. All are protected by the same admin middleware.

The Laravel panel exposes the same operations under `/admin/instagram-api/` (without the API client's extra `admin/` path segment), guarded by `PanelAdmin`. Writes use normal web CSRF protection. The shared component selects a session/CSRF Axios client only when mounted inside this panel; the Vue website continues using its existing JWT client. The Laravel route renders a Blade wrapper and reads hashed JavaScript/CSS filenames from `backend/public/instagram/.vite/manifest.json`.

Build the panel bundle from `Nour Adhkar` with `npm run build:instagram-panel`. Its output is `dist/instagram-panel`, packaged inside the existing `web-dist.tgz` after the main website build. Deployment copies it atomically to the API host's `backend/public/instagram`. Generated panel assets are ignored by Git. No external website iframe or authentication bypass is used.

Migrations add the queue and templates tables plus a saved design and template reference on each post; they do not change Android app data or reminders. Run focused `php artisan test --filter=InstagramQueue` against the test database. Source syntax checks do not establish deployment or browser/runtime success. Fonts, clipboard, downloads, and preview need browser verification on the deployed admin.

## Page settings and templates

Open the `قالب‌ها` tab on the queue page. Select a template to edit or create/duplicate one. Name, background color, main text color, secondary text color, font, font size (36–84 px), line spacing (1.4–2.2), side margin (80–180 px), and logo size (48–120 px) are configurable. Layouts: centered, upper half (still horizontally centered), and centered with a thin frame. Live preview uses the selected verse or first library verse. Low text contrast displays a warning; it does not prevent saving.

The default template applies to newly created drafts. Exactly one default is retained; choose another default before deleting the current default. The initial three presets are editable. Each post saves a design snapshot, so editing/deleting a template preserves existing post images, including the published archive. In the post editor, `طراحی ذخیره‌شده این پست` previews its snapshot. Selecting a named template previews its latest saved design; `ذخیره تغییرات` applies it to that post. Unsaved template editor changes are separate from the post's design.

The templates migration copies the original paper/white/night designs into existing posts, preserving their appearance. Long verse text is reduced automatically to fit; verse wording, reference, translation credit, and the bottom-right logo remain intact.

## Local verification (2026-10-06)

- Focused backend suite: 3 tests, 26 assertions, exit 0. Installed Laravel/Pest dependencies emit PHP 8.5 deprecations; these are not clean-warning test results. Test-only JWT configuration is isolated to the new test file.
- PHP syntax checks passed for controller, migration, and API routes. Vue script and template compiled with the installed Vue compiler. Git whitespace checks passed.
- The actual image renderer ran in headless Microsoft Edge with bundled font/logo. Eight 1080 × 1350 JPEGs and matching captions are in `marketing/instagram`; `contact-sheet.png` was visually inspected. `marketing/nour-instagram-starter.zip` packages these assets.
- No Android build/install/launch, production migration, deployment, Instagram sign-up, or Instagram publishing was performed. End-to-end admin browser interactions remain unverified.

## Local preview

The follow-up local run uses Vite at `http://localhost:5173` and the Laravel API at `http://localhost:8000`. The ignored backend `.env` points to an isolated `storage/app/instagram-local.sqlite`; migration completed against that database. A local-only admin was created for preview. Browser checks verified login, source catalog loading, draft creation, approval into the queue, image preview, and JPEG download with no JavaScript runtime errors. One sample post (94:5) is ready in the local queue. Production data and Instagram were not accessed.

Template update verification: the second migration completed against the local database; focused backend tests passed all 51 assertions (5 tests, exit 0, existing PHP 8.5 dependency deprecations). Vue script/template compilation and PHP syntax checks passed. Live browser checks verified custom colors, Noto Naskh font, framed layout, template creation and persistence, post design persistence after reload, JPEG download, switching templates, and template deletion, with no JavaScript runtime errors. The test template was deleted and the sample post restored to paper. The customized JPEG and settings screen were visually inspected. Reference digits are rendered explicitly in Persian for every font.

Laravel-panel integration verification: 7 focused tests passed all 74 assertions, including panel session authorization and the shared queue/template endpoints (existing PHP 8.5 dependency deprecations remain). The standalone production panel bundle built successfully; PHP syntax and Blade view compilation passed. The actual compiled bundle was served locally from Laravel, and browser checks verified session login, colors/font/layout changes, save/reload, template switching/deletion, and JPEG download without JavaScript runtime errors. The session-panel settings page was visually inspected.

## Scheduled publication setup

This implementation uses Instagram API **with Instagram Login**, at `graph.instagram.com`; do not substitute a Facebook Page token. Reference: https://www.postman.com/meta/instagram/documentation/6yqw8pt/instagram-api and https://developers.facebook.com/docs/instagram-platform/instagram-api-with-instagram-login/content-publishing/.

1. Configure a Meta Developer app with Instagram Login for the owner's Creator account, granting `instagram_business_basic` and `instagram_business_content_publish`. Generate the Instagram User access token and numeric Instagram user ID through Meta's supported account setup. Keep tokens out of chats and Git. A handle is not the numeric account ID. Manage token expiry/renewal through Meta; the worker does not silently refresh expired credentials.
2. Set repository Actions secrets `INSTAGRAM_ACCOUNT_ID`, `INSTAGRAM_ACCESS_TOKEN`, `INSTAGRAM_PUBLISH_TOKEN` (a separate random worker authentication secret). Set matching backend env `INSTAGRAM_ACCOUNT_ID`, `INSTAGRAM_PUBLISH_TOKEN`, `INSTAGRAM_USERNAME=nouradhkar`, and finally `INSTAGRAM_SCHEDULING_ENABLED=true`. Reload cached Laravel config. Do not enable before matching worker credentials are ready. The backend holds the worker secret, not the Meta token.
3. Actions variables: `INSTAGRAM_API_URL` defaults to `https://api.adhkar.ir/api`; `INSTAGRAM_GRAPH_VERSION` defaults to `v25.0`, configurable to the supported version used by the Meta app. The workflow on main runs approximately every five minutes or manually. GitHub schedules are best effort and can be delayed. Missing secrets skip publication. Do not promise an exact send time.
4. Approve a post into the ready queue, choose a Tehran date/time, then click «زمان‌بندی ارسال به اینستاگرام». The page saves text/design/caption, renders the saved values into a 1080×1350 JPEG, and uploads it with a content hash. Concurrent edits cause 409 rather than scheduling a stale image. Dates are converted from Asia/Tehran to UTC; the browser's timezone is ignored.
5. Ensure `/storage/instagram-posts/...jpg` is publicly reachable by Meta (normal Laravel public storage symlink, CDN access, no login). The worker claims only due posts bound to the configured account. Creates a container, waits for image processing, records publication start, then calls `media_publish` once and records the returned permalink.
6. Scheduled posts are immutable until canceled. Cancel returns the post to the ready queue and removes its uploaded JPEG. Before-publication failures may be canceled/reviewed/rescheduled. After publication starts, errors remain locked: the publish call may have succeeded despite a lost response. Never automatically retry it. Verify in Instagram and use «تأیید انتشار و ثبت لینک» to reconcile a confirmed publication. An interrupted worker with no reported error needs operator investigation; do not reset/replay its claim without checking Meta's result. Published image files remain available for the archive.

Admin scheduling endpoints: POST/DELETE `instagram-queue/{id}/schedule`, POST `instagram-queue/{id}/reconcile` under both existing admin API paths. Worker-only POST `/api/internal/instagram/claim` and `/api/internal/instagram/{id}/report` authenticate with a timing-safe bearer-secret comparison, rate-limited to 60/minute. These are not public admin write endpoints.

Verification: focused backend suite now passes 13 tests / 122 assertions (existing PHP 8.5 dependency deprecations). Tests cover editable source snapshots, disabled scheduling, stale image hashes, Tehran-to-UTC conversion, cancellation, worker authentication/account binding, single claims, publication locking and idempotent result recording. Real Meta authentication/publication remains unverified until the account connection is configured.

Local follow-up verification: both production website and standalone panel bundles build successfully. The actual Laravel panel browser verified Khorramdel selection, editable image text, save/reload, JPEG export, reset to source, and disabled scheduling without credentials; no JavaScript runtime errors. The exported edited image was visually inspected. Two Python worker tests confirm the actual publish request is never retried, while idempotent result reporting is retried. Profile image and Persian bio for @nouradhkar are in `marketing/instagram/nouradhkar-profile.png` and `nouradhkar-bio.txt`.

## Studio layout refinement (2026-10-06)

The shared Instagram page now has three separate views: Posts, Templates and Suggested verses. The post workspace uses compact status filters with counts, a scannable list, image preview beside the desktop editor, and separate Text/appearance and Publish stages. The original source is expandable; manual publication and scheduling are mutually exclusive views. Download stays by the preview, save state and actions sit in the editor footer, and delete/return-to-draft live under More options. Unsaved post edits require confirmation before choosing another post or status; empty image text cannot be saved or exported. Existing verse entries in the library open their post instead of a disabled dead end.

Colors, font and layout remain visible in the Templates view. Size/spacing controls are expandable. Mobile filters and post lists scroll inside their sections, with the preview above the editor. Theme colors come from the existing admin theme. Reference digits are Persian. No backend API, scheduling rules or persisted post design changes.

Verification: website and standalone Laravel-panel production bundles build. Actual local session panel interactions verified edited text/save/reload, dirty-state protection, source/library navigation, font controls, collapsed advanced settings, manual/scheduled method switching and disabled sending without credentials. Screenshots were inspected in desktop, narrow mobile and dark mode; 390px and 320px pages had no horizontal overflow and no browser JS errors.
