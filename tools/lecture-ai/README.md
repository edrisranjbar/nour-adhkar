# Lecture descriptions from audio

In `/admin`, open a lecture and press «ساخت توضیح از روی صوت». The workflow `.github/workflows/lecture-ai.yml` transcribes the audio with Groq's free `whisper-large-v3` (Persian). It cuts the audio into 5-minute parts and transcribes six at a time. The backend then replaces the lecture description with the transcript. The edit page checks every 15 seconds and fills in the field when it's ready.

Each run watches the queue for about 28 minutes, checking every 20 seconds, and a new run starts every 30 minutes. While a run is alive, a click is picked up within seconds. You can also start a run by hand with "Run workflow".

The API server in Iran cannot reach Groq, which is why this runs on GitHub Actions. It uses `GET /api/internal/lectures/claim` and `POST /api/internal/lectures/{id}/ai`, both protected by `LECTURE_AI_TOKEN`.

## Setup

- Repository secrets: `GROQ_API_KEY` (free key from console.groq.com) and `LECTURE_AI_TOKEN`.
- The backend `.env` needs the same `LECTURE_AI_TOKEN`. Run `php artisan config:cache` after changing it.

## Limits

The free tier caps audio minutes per hour and per day. When a lecture hits a limit, the workflow waits up to 15 minutes, then keeps the lecture queued for a later run.

## Configurable provider (admin)

Open /admin/settings/lecture-ai. Save the HTTPS OpenAI-compatible API base (default
https://ai.manageit.dev/api/v1) and API key, fetch the catalog, then select separate transcription
and summary models. Keys are encrypted with Laravel APP_KEY, never populated in the password
field or flashed into validation input. Blank key retains the saved key. Changing provider
requires a replacement key and clears model/catalog selections; preserve APP_KEY and backups.

Only models whose catalog explicitly reports zero prompt/completion prices (and no other
nonzero numeric pricing) can be selected. Missing pricing is unknown, not free. Catalog access
to manageit returned HTTP 401 without a key; its actual free audio capability remains unverified.
No model quality, quota, pricing or speech support can be confirmed until an authorized catalog
and a real sample are tested. /audio/transcriptions multipart and /chat/completions JSON must
be supported. No paid fallback is used. A text model cannot transcribe audio by itself.

Run the migration before deploying the worker. The worker retrieves settings through
GET /api/internal/lectures/config using the existing LECTURE_AI_TOKEN, with no-store responses;
do not expose this token. Settings are loaded before claiming each lecture. Incomplete saved
configuration blocks processing without claiming a lecture. Before any settings row exists,
the existing GROQ_API_KEY workflow remains available for compatibility.

Configured processing transcribes all five-minute parts and summarizes in bounded text chunks,
then combines summaries in multiple stages. Both outputs are posted back to the existing report
endpoint. Summary failures fail/requeue the job instead of silently reporting completion.
The workflow only requires LECTURE_AI_TOKEN when admin configuration is used; there is no need
to copy the provider API key into GitHub secrets. Never enable request/header debug logging.

Verification: PHP feature tests use mocked HTTP; Python unit tests stub requests and perform no
external calls. Run python -m unittest discover -s tools/lecture-ai -p test_process.py.
Live provider transcription/summary verification requires the admin-saved key and selected models.
