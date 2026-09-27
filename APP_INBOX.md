# App feedback and admin notices

Run the Laravel migrations before enabling the Android client. The Android app sends feedback to `POST /api/app-feedback` with `type` (`suggestion`, `criticism`, or `other`) and `message`. The endpoint is public and rate limited. Feedback is visible to authenticated administrators at `/admin/app-inbox` in the web panel.

Administrators create a draft or published notice on that page. Only published notices are returned by `GET /api/app-notices?installation_id=<uuid>`. The Android app stores a random installation UUID locally and calls `POST /api/app-notices/{id}/read` with that UUID when a notice is tapped. Read state is per installation; reinstalling or clearing app data creates a new inbox identity. The API is read on demand when opening the app inbox and needs an internet connection. This is an in-app inbox, not a push notification service.
