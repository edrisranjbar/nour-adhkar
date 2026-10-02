# App feedback and admin notices

At `/admin/users`, only inactive accounts have a delete button. Deletion requires browser confirmation and permanently removes the account and dependent records according to the existing database relationships. The server rejects deletion of active accounts (including accounts reactivated after the page loaded) and the signed-in administrator's own account. Search and pagination are preserved when returning to the list.

Run the Laravel migrations before enabling the Android client. The Android app sends feedback to `POST /api/app-feedback` with `type` (`suggestion`, `criticism`, or `other`) and `message`. The endpoint is public and rate limited. Feedback is visible to administrators in the Blade admin panel at `/admin/feedback` on the API host (session login at `/admin/login`, users with `role = admin`). The panel only manages the app: notices (`/admin/notices`), feedback, and app users. The website is becoming a landing page and has no admin.

Administrators create, edit, publish/unpublish, or delete notices at `/admin/notices`. Only published notices are returned by `GET /api/app-notices?installation_id=<uuid>`. The Android app stores a random installation UUID locally and calls `POST /api/app-notices/{id}/read` with that UUID when a notice is tapped. Read state is per installation; reinstalling or clearing app data creates a new inbox identity. The API is read on demand when opening the app inbox and needs an internet connection. This is an in-app inbox, not a push notification service.

The dashboard's published-notices card shows the total recorded read count (`app_notice_reads`) instead of the draft count, labelled «بار خوانده‌شدن». Each notice/installation pair counts once, including reads of notices subsequently unpublished; repeated read requests do not increase the count.

## Android app email verification

- Requests with header `X-Nour-Client: android` must verify the email once before receiving a JWT. Web requests (no header) are unchanged.
- `POST /api/auth/register` (app) → 201 `{verification_required: true, email, retry_after}`, no token.
- `POST /api/auth/login` (app, unverified) → 403 `{verification_required: true, ...}` and a fresh code is emailed (respecting the cooldown).
- `POST /api/auth/verify-email` `{email, password, code}` → token + user. 5-digit code, 10 min expiry, stored hashed, 5 attempts max; already-verified accounts get 409 (use password login).
- `POST /api/auth/resend-code` `{email}` → 60 s cooldown (429 with `retry_after`); same response whether or not the email exists.
- Email: `App\Mail\EmailVerificationCode` with `resources/views/emails/verification_code_fa(.blade.php|_text.blade.php)`, sent through the configured mailer (`MAIL_MAILER=resend`).

Deploy: `php artisan migrate` (creates `email_verification_codes`), set `RESEND_API_KEY` (or `RESEND_KEY`), `MAIL_MAILER=resend`, and a `MAIL_FROM_ADDRESS` on a domain verified in Resend.
Tests: `tests/Feature/EmailVerificationTest.php`.
