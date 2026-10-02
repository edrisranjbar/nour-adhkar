# Store installs, votes and rating (admin dashboard)

The dashboard (`/admin`) starts with a live card showing the app's install count and a chart of the last
7 days. It refreshes every 10 minutes and, once the **🔔 صدا** button is switched on, plays a short
chime whenever a count goes up.

The feedback card shows three separate figures: in-app feedback, Bazaar's public vote count («رأی بازار»),
and Bazaar's rating out of 5 («امتیاز بازار»). Votes are not written review counts and are not added to
in-app feedback. All displayed numbers use Persian digits. These figures refresh with the install feed.
The card retains three compact rows: title, in-app/Bazaar counts side by side, and five SVG stars with
the numeric rating beside them. Fractional ratings fill the stars proportionally. Loading, unavailable,
and stale descriptions use the rating row's accessible label and tooltip instead of extra visible rows.

## Where the number comes from

The public install count on the app's Cafe Bazaar page (`https://cafebazaar.ir/app/<package>`), the same
«نصب» figure users see. It is part of the server-rendered HTML, so no API token is needed. Bazaar's
Pishkhan API only manages releases (create, upload, commit) and has no statistics, so do not put a
Pishkhan token into this project.

The same request also reads the info-table cell labelled «از ... رأی»: its label supplies the vote count
and its content supplies the rating. Missing or invalid rating data stays unavailable (—), rather than
becoming zero. Last valid votes/rating are persisted independently of installs and marked «آخرین آمار معتبر بازار»
on fetch or parse failure. A changed install count can still update when the rating cell is missing, and vice versa.

- The package id comes from `STORE_PACKAGE` (default `ir.adhkar.app`).
- It is a public, rounded-by-the-store figure, not Bazaar's private developer-panel statistics.
- If Bazaar changes its page layout the number cannot be read: the dashboard keeps the last known value
  (greyed, «آخرین مقدار معتبر») and the log gets a warning «install count was not found on the page».

## How it works

| Piece | File |
|---|---|
| Settings (package, cache seconds, heartbeat, store list) | `backend/config/stores.php` |
| One store: label + how to read its count | `backend/app/Services/StoreStats/StoreInstallProvider.php`, `BazaarInstallProvider.php` |
| Fetch with a 600 s cache, store snapshots, build the chart series | `backend/app/Services/StoreStats/StoreStats.php` |
| JSON feed (admin only) `GET /admin/installs` | `backend/app/Http/Controllers/Panel/InstallStatsController.php` |
| Card, chart (plain SVG), sound | `backend/resources/views/panel/_installs.blade.php` |
| History | table `store_install_snapshots` (store, installs, recorded_at) |
| Last valid rating/votes and observations | table `store_rating_snapshots` (store, rating, rating_count, recorded_at) |

- The dashboard polls on opening and every **600 seconds** thereafter. The cache window is also 600 seconds.
- A snapshot is stored when the number changes, and every 10 minutes otherwise when a successful fetch occurs.
  Collection runs while someone has the dashboard open; there is no background scheduled scraper.
- The chime is generated in the browser (Web Audio). Browsers only allow sound after a click: the button
  is that click, and after a page reload with sound saved as on, the first click anywhere re-enables it.
  The sound setting is stored in the browser (`localStorage`, key `installSound`).

## Written Bazaar reviews

`BazaarReviews` reads the public unauthenticated `https://api.cafebazaar.ir/rest-v1/process/ReviewRequest` feed used by the Bazaar website. It sends only package name, language and paging information, without a developer token or user credentials. Up to three pages of 24 reviews are requested per 600-second cache window, stopping on a shorter page. The public feed can place featured reviews first, so imported reviews are sorted by their supplied Solar Hijri date, newest first. This is a bounded collection of published reviews, not an assertion that all store reviews have been imported.

Written reviews are upserted into `store_reviews` using store + package + review ID. Edited text and stars update the same row; first-seen time stays unchanged. Empty rating-only entries and developer entries are excluded, and account identifiers/avatars are not stored. Missing dates/stars remain unavailable. A successful empty response does not erase earlier collected reviews; failed requests or invalid response structures preserve data and show a stale/last-received status. Cache keys and displayed records are scoped to `STORE_PACKAGE`.

Reviews refresh on the dashboard, its existing `/admin/installs` poll, and the feedback page (unless filtered to in-app feedback). There is no background schedule. Dashboard previews are capped at five; the feedback page offers source filters and independent Bazaar pagination. Text is escaped with Blade and reviews are display-only. Imported written-review counts remain distinct from public rating/vote counts and in-app feedback counts.

Deploy by running the migration creating `store_reviews`. Tests: `BazaarReviewsTest` and `StoreInstallStatsTest`.

## Adding Myket or Google Play later

Write a class that implements `StoreInstallProvider` (`key()`, `label()`, `installs()`, `metrics()`) and add it to
`providers` in `config/stores.php`. The card, chart (one line per store), polling and sound pick it up
with no other change.

`metrics()` returns `installs`, `rating`, and `rating_count`, with null for unavailable metrics.

## Deploying

Run the new migration (the deploy script already runs `php artisan migrate --force`). Nothing else needs
configuring on the server.
