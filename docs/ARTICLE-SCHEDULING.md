# Article scheduling and private batch operations

The panel stores a scheduled article as `status=published` with a future UTC `published_at`. All public article lists **and direct article URLs** use the same publication boundary. The app and the website's one-minute background regeneration reveal an article only after that time. The admin panel accepts local Tehran times.

For an approved pair of drafts when the browser is unavailable, the manually dispatched `Deploy` workflow has a mutually exclusive `schedule_articles_only=true` mode. It does not deploy or reset the application checkout. It reads a private, temporary base64 JSON batch from the `ARTICLE_BATCH_20261024` repository secret, tests scheduling, uploads the batch and `deploy/schedule-articles.php` into private `backend/storage/app/article-imports/<run-id>/`, imports within one transaction, and verifies the rows and public invisibility. Draft bodies are not committed to the public repository or printed in logs. Remove the temporary secret after a successful, verified import.

The batch contains `timezone: Asia/Tehran`, `reference_slug: daily-adhkar-start`, and exactly two articles with `title`, `slug`, `excerpt`, plain-text `content` and local `publish_at` (`YYYY-MM-DDTHH:mm`). Both dates must be future dates exactly 48 hours apart. The author is inherited from the existing reference article after checking that its user is an administrator.

The importer adds rows only. Identical retries return the existing IDs; a conflicting slug or invalid date rejects and rolls back the whole batch. It does not overwrite articles, create accounts or edit cron entries. Source drafts and the editorial plan remain in the user's local content folder.

Focused verification:

```sh
cd backend
php artisan test --filter='PostShowScheduleTest|ScheduledArticleBatchTest|PanelArticleScheduleTest'
```

Deployment reuses the existing private Node runtime when its SHA-256 matches CI's official runtime, avoiding an unnecessary large upload for content-related fixes.
