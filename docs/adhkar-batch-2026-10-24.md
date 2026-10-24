# Reviewed app adhkar batch — 2026-10-24

Scope: 3 new collections, 8 situation-specific texts. Prepared for the existing admin `app_dhikr_collections` / `app_dhikrs` tables, not the website's legacy collections. No APK change or rebuild is needed. No content is published merely by deploying this file.

## Evidence

Hisn al-Muslim was checked as a reference compilation, not treated as an independent authenticity grade. Each selected text is supported by Sahih al-Bukhari or Sahih Muslim:

| Collection / situation | Primary reference | Compilation cross-check |
| --- | --- | --- |
| Rain begins | [Bukhari 1032](https://sunnah.com/bukhari:1032) | [Hisn, rain](https://hisnmuslim.com/i/ar/64); the compilation supplies the familiar wording beginning اللهم |
| After rain | [Bukhari 846](https://sunnah.com/bukhari:846) | [Hisn, after rain](https://hisnmuslim.com/i/ar/65) |
| Strong wind | [Muslim 899b](https://sunnah.com/muslim:899b) | [Hisn, wind](https://hisnmuslim.com/i/ar/61) |
| Sneezing: speaker, listener, reply | [Bukhari 6224](https://sunnah.com/bukhari:6224) | [Hisn, sneezing](https://hisnmuslim.com/i/ar/77) |
| Visiting a sick person | [Bukhari 3616](https://sunnah.com/bukhari:3616) | [Hisn, visiting the sick](https://hisnmuslim.com/i/ar/49) |
| Prayer for healing | [Bukhari 5743](https://sunnah.com/bukhari:5743) | Direct primary text; no claim that this exact wording appears in Hisn's visiting-the-sick chapter |

Arabic is the cited transmitted text with ordinary punctuation/diacritics. Persian meanings are newly prepared explanatory translations, not attributed to a named translator. Context instructions are in `source` and the collection description, separate from the Arabic. All counters are 1 as a UI default for one occasion, not an assertion of a prescribed daily count. The three sneezing phrases belong to different speakers and are not a three-part daily recitation. No promises of guaranteed medical recovery are added; the description explicitly does not replace treatment.

The bundled Android catalog was checked for the distinctive texts and missing collection types. The short praise الحمد لله already exists as a standalone bundled entry; it is deliberately reused here to explain the complete, situation-specific sneezing exchange. Therefore this batch has eight contextual entries, not eight previously unseen formulas. Live admin content must be rechecked before import. The importer rejects normalized duplicates within the admin catalog and conflicting existing records rather than overwriting or republishing them. Reruns preserve IDs. All additions run in one transaction.

## Import

`NOUR_BACKEND_ROOT=/path/to/backend php deploy/import-app-adhkar.php deploy/content/app-adhkar-2026-10-24.json`

Run only with explicit publication authorization. This operation publishes the batch. Use the existing admin panel afterwards for editing or hiding entries. Verify the public `/api/app-adhkar` response against the batch after importing; app delivery still follows its startup/6-hour cache policy. Device verification is separate and has not been performed.

The manually dispatched **Publish reviewed app adhkar** workflow requires `publish=true`, tests the importer in an isolated database, uploads only this script/batch into private server storage, and verifies all eight public API records. It does not reset server code, run migrations, rebuild an APK, or deploy the website. Publication was explicitly authorized in chat on 2026-10-24.

## Publication evidence

Published through the existing Deploy dispatcher with `publish_app_adhkar_only=true`; [run 40126973962](https://github.com/edrisranjbar/nour-adhkar/actions/runs/40126973962) succeeded. All application/website deployment jobs were skipped. Seven focused tests passed in CI. The public API was independently re-read after the workflow: collection IDs 1 (باد و باران), 2 (عطسه), and 3 (عیادت بیمار) contain 3, 3, and 2 entries respectively. All eight Arabic, Persian translation, reference and count fields match the reviewed JSON exactly. No phone installation or visual verification was performed.
