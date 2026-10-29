# Morning and evening source review — 29 October 2026

Scope: the 45 cards in the checked-in `morning` and `night` website snapshots. The editorial ledger now covers all 45 cards through 34 distinct normalized full Arabic texts. This is a source review, not a declaration that all cards are authentic hadith. No database, Android content, reminder, or saved counter was changed.

## Findings and treatment

- The morning `أصبحنا وأصبح الملك لله` card is an excerpt of Muslim 2723b. Its Persian meaning previously included the missing final supplications. The displayed meaning now covers only the displayed Arabic; the omission is explained.
- The trust pronoun in the Quran 9:129 excerpt now reads «بر او توکل کرده‌ام». This Quran reference does not establish seven morning repetitions.
- Abu Dawud 5069, 5073, 5084 and 1555 are labelled weak according to al-Albani in the linked edition. Ibn Majah 3801 is labelled weak according to the linked Darussalam grading. The Abu Darda report has explicit named weak assessments from al-Albani and Shuayb al-Arnaut. These are attributed assessments, not invented scholarly consensus.
- Differences in the gratitude, wellness, refuge-from-self/Shaytan and long istighfar formulas are explained beside the card. In particular, Tirmidhi 3392 does not contain the final `وأن أقترف…`; Adab al-Mufrad 1204 contains that ending with other wording/order differences. Neither is presented as an exact source for the entire combined formula.
- The refuge in Allah's perfect words is supported for three repetitions in the **evening** by Tirmidhi 3604b. The morning stored three is not promoted to an authenticated morning count.
- The ya-Hayy prayer, hidden-shirk prayer, generic istighfar and salawat entries do not acquire fixed morning repetition counts from general evidence. The exact scope appears beside the text.
- The existing daily tahlil distinction and morning-only Juwairiya count remain. Source notes now also appear in Arabic on Arabic and Sorani pages, explicitly tagged `lang="ar"`, so qualifications are not dropped outside Persian.

## Sources

The machine-readable ledger contains the exact links, complete expected Arabic, reference labels, dates and per-card scope notes. Primary collections are linked directly through Sunnah.com; Dorar entries identify the edition, hadith and named scholar's grading where needed. No hadith number was inferred solely from a similar opening.

Key links: [Muslim 2723b](https://sunnah.com/muslim:2723b), [Abu Dawud 5074](https://sunnah.com/abudawud:5074), [Abu Dawud 5069](https://sunnah.com/abudawud:5069), [Tirmidhi 3604b](https://sunnah.com/tirmidhi:3604b), [morning fitrah](https://dorar.net/h/KRHM2Ekv), [ya-Hayy](https://dorar.net/h/CRWdWiqR?osoul=1), [hidden shirk](https://dorar.net/h/mPHTtyAI?osoul=1), [Abu Darda report](https://dorar.net/h/25zQ25ne).

## Validation and release boundary

- Tests check all 45 exact texts, invalidation after Arabic text changes, weak-report count handling, context-specific counts, and the corrected Persian meaning.
- The generated-site verifier checks source links, qualifications and count badges in all three language editions, as well as its existing sitemap/metadata checks.
- Browser Search Console inspection, indexing requests and Instagram profile changes are separate external actions. They are not proven by a site build and remained unavailable when Chrome control failed to initialize in this session.
- Website deployment requires the new develop-to-main pull request to be merged. A local build or pushed branch is not production proof.
