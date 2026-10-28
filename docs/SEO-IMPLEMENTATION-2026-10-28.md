# Website SEO and reading refinements — 28 October 2026

## Implementation and remaining work

1. Collection quality: corrected incomplete Persian meanings of Ayat al-Kursi and Al-Falaq, refreshed Ikhlas/Nas meanings, split duplicated body-health/poverty translations to match each Arabic segment. Ten exact-Arabic editorial records cover 18 of the 45 morning/evening cards. No database writes or Android changes. This is **not a completed religious audit of every catalog entry**. References explain whether they support Quran text, the formula, its time or its count. Unreviewed counters are labelled stored counters rather than asserted prescribed repetitions. The original unreviewed texts remain available.
2. `/updates`: existing develop implementation serves independent initial HTML/canonical/release history; built SEO verification now runs in CI. Still absent from the live site until deployment.
3. Article formatting: fixed CRLF paragraph splitting; headings and lists render correctly; web citations are clickable; source HTML and unsafe schemes stay escaped.
4. Important unindexed pages: `/blog` and `/about` were reported crawled/not indexed in the indexing report last updated October 23. Improved links, blog starting points and editorial transparency are prepared. No indexing request, validation or indexing result is claimed.
5. Search intent: collection pages explicitly distinguish website reading from audio in the Android app and place both store links before the long catalog. No web-audio or PDF availability is invented.
6. Internal discovery: homepage links directly to morning/evening pages; collection contents link to actual cards, morning/evening cross-links and a Persian beginner article; blog index links to the principal reading paths.
7. Editorial transparency: project-author bylines and schema link to `/about#author`; about copy explains website-only corrections without claiming qualified religious review or identical app/web text.
8. Blog organization: reading/start links added without publishing duplicate articles or artificial keyword pages.
9. External links: recommendation only; no outreach or messages sent. Identify relevant publishers before any contact.
10. Performance: production build checked, no Core Web Vitals field data was available in Search Console. No Lighthouse score, ranking improvement or install uplift is claimed.

## Sources and correction boundaries

- Quran: https://quran.com/2/255 and chapters 112, 113, 114. New Persian meanings are explanatory wording prepared for the website, not attributed to an existing translator.
- https://sunnah.com/abudawud:5082 — three recitations of Ikhlas/Falaq/Nas morning/evening; explicitly labelled Hasan according to al-Albani, not Sahih.
- https://sunnah.com/abudawud:5090 — body health and refuge from disbelief/poverty/grave punishment, three morning/evening; source labels chain Hasan.
- https://sunnah.com/bukhari:6306 — Sayyid al-Istighfar.
- https://sunnah.com/bukhari:3293 — tahlil 100 **per day**, not an invented requirement for two separate sets of 100.
- https://sunnah.com/muslim:2692 — tasbih 100 morning/evening.
- https://sunnah.com/muslim:2726a — three repetitions in the morning incident; no evening-specific count inferred.

The full normalized Arabic must match before a correction/reference applies. An admin text change cannot inherit an unrelated citation through its ID or initial words. Original Arabic, stored counts and API records are not overwritten. Remaining catalog authenticity, wording and timing need a separate full editorial review; no unverified entry is marked Sahih.

## Verification and release boundary

Focused Node tests: 13 passed. Production website build passed. Generated-output checker passed all 42 sitemap URLs, canonicals, single H1s, unique fragment IDs, structured data, correct updates initial HTML, homepage collection links, sources/corrected translation clauses and contents navigation. Existing unrelated `public/sitemap.xml` bytes were restored after the build.

Prepared changes require publication through the repository's existing review/deployment workflow. PR 189 was still open/unmerged when inspected. No main merge or production deployment is authorized by an audit finding alone, and no production improvement is claimed from local verification.
