# Lecture descriptions from audio

In `/admin`, open a lecture and press «ساخت توضیح از روی صوت». The workflow `.github/workflows/lecture-ai.yml` transcribes the audio with Groq's free `whisper-large-v3` (Persian). It cuts the audio into 5-minute parts and transcribes six at a time. The backend then replaces the lecture description with the transcript. The edit page checks every 15 seconds and fills in the field when it's ready.

Each run watches the queue for about 28 minutes, checking every 20 seconds, and a new run starts every 30 minutes. While a run is alive, a click is picked up within seconds. You can also start a run by hand with "Run workflow".

The API server in Iran cannot reach Groq, which is why this runs on GitHub Actions. It uses `GET /api/internal/lectures/claim` and `POST /api/internal/lectures/{id}/ai`, both protected by `LECTURE_AI_TOKEN`.

## Setup

- Repository secrets: `GROQ_API_KEY` (free key from console.groq.com) and `LECTURE_AI_TOKEN`.
- The backend `.env` needs the same `LECTURE_AI_TOKEN`. Run `php artisan config:cache` after changing it.

## Limits

The free tier caps audio minutes per hour and per day. When a lecture hits a limit, the workflow waits up to 15 minutes, then keeps the lecture queued for a later run.
