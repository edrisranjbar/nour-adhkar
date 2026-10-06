# Instagram queue removed

The Instagram queue, template editor, renderer, admin navigation, API endpoints, session-panel bundle and publishing worker were removed at the owner's request on 2026-10-06. The scheduled publishing workflow was disabled and removed. Website deployment no longer builds the retired panel.

Historical migrations and their catalog fixture are retained for migration compatibility. Existing database tables/rows and uploaded images are preserved; no destructive data migration is applied. Old public panel assets are moved into private storage on deployment. Marketing designs, the profile image and caption drafts remain available as standalone files.

Verification: the website production build and deploy shell syntax check pass. Focused backend retirement/admin analytics checks pass: 7 tests, 60 assertions (existing PHP 8.5 dependency deprecations). Removed admin/worker endpoints return 404; the remaining admin panel works and archive data remains intact.
