# Instagram verse queue

Admin route: `/admin/instagram-queue`. Requires the existing admin login. Android source, version, and release artifacts are unchanged.

## Workflow

1. Deploy the backend and frontend changes, then run `php artisan migrate --force` from `backend` using the normal deployment procedure.
2. Choose a suggested verse to create a draft. Repeated clicks return the same draft. No posts are created by migration.
3. Review the full Persian translation, linked source, caption, and image. Choose a saved template, save the post's design, then approve to add to the ready queue; use arrows to order it.
4. Download the 1080 × 1350 JPEG and copy the caption. Publish manually to Instagram.
5. Paste the post's Instagram permalink and record publication. This records the admin's confirmation; it does not verify the post with Meta. Published entries are read-only archive records. Downloads never mark a post published.

This initial scope is an editorial queue, not a background scheduler or automatic publisher. Creating an Instagram account requires the account owner's sign-up/access. Automatic publishing would require a supported professional account and a separately configured Meta integration. No Instagram credentials or tokens are stored here.

## Content and design

- Eight editorial candidates: 94:5, 2:152, 13:28, 2:153, 93:3, 93:5, 20:46, 3:139. Themes: hope, remembrance, calm, patience, trust, and perseverance. No measured popularity or share ranking is claimed.
- Full, unchanged Persian verses from the Android app's `translation-fa-islamhouse.txt` (QuranEnc / Rowwad Translation Center, IslamHouse Persian team, v1.1.0). No generated or paraphrased verse text. The library is immutable from the UI; caption and image design are editable.
- 93:3 and 93:5 address the Prophet; 20:46 addresses Moses and Aaron. Retain the translator's bracketed clarifications and consult the linked surrounding verses before writing commentary. Do not turn these into unqualified personal guarantees.
- Arabic text is preserved as source metadata from Tanzil Uthmani 1.1. Attribution and original copyright notice: `backend/resources/data/TANZIL-NOTICE.txt`; see https://tanzil.net/docs/text_license. Keep text intact and retain attribution in derivative distributions.
- Image: centered Persian text, generous empty space, verse reference and translation credit, existing Nour logo at bottom right. Bundled Vazirmatn (regular/bold) and Noto Naskh Arabic; no network requests to font services. Preview and export share one canvas renderer. Noto Naskh is distributed with its SIL Open Font License in `Nour Adhkar/src/assets/fonts/noto-naskh/OFL.txt`; source: https://github.com/google/fonts/tree/main/ofl/notonaskharabic.
- Suggested account identity (handle availability unverified): `nour.adhkar`; display name `اذکار نور | یادآوری‌های قرآنی`; bio `یادآوری‌های کوتاه از قرآن، برای دل و روزهای ما\nترجمه فارسی با ذکر منبع\nدریافت برنامه: adhkar.ir`. Check availability during sign-up.

## API and verification

All endpoints are within existing `auth:api` + `AdminMiddleware` protection:

- `GET /api/admin/instagram-queue`: source catalog, ordered entries, and saved templates.
- `POST /api/admin/instagram-queue`: `verse_key` from catalog, idempotent.
- `PUT /api/admin/instagram-queue/{id}`: caption (max 2200), `template_id`, status; recording publication requires current queued status and an Instagram post/reel permalink. Selected template design is copied into the post. Caption/status-only updates preserve that snapshot. Legacy `theme` input remains accepted.
- `PUT /api/admin/instagram-queue/order`: complete, distinct list of currently queued IDs; stale membership returns 409.
- `DELETE /api/admin/instagram-queue/{id}`: draft/queued only.
- `POST /api/admin/instagram-templates`, `PUT /api/admin/instagram-templates/{id}`, `DELETE /api/admin/instagram-templates/{id}`: manage named designs. All are protected by the same admin middleware.

Migrations add the queue and templates tables plus a saved design and template reference on each post; they do not change Android app data or reminders. Run focused `php artisan test --filter=InstagramQueue` against the test database. Source syntax checks do not establish deployment or browser/runtime success. Fonts, clipboard, downloads, and preview need browser verification on the deployed admin.

## Page settings and templates

Open `تنظیمات قالب‌ها` on the queue page. Select a template to edit or create/duplicate one. Name, background color, main text color, secondary text color, font, font size (36–84 px), line spacing (1.4–2.2), side margin (80–180 px), and logo size (48–120 px) are configurable. Layouts: centered, upper half (still horizontally centered), and centered with a thin frame. Live preview uses the selected verse or first library verse. Low text contrast displays a warning; it does not prevent saving.

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
