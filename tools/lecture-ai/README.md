# Lecture transcripts

`process.py` runs from `.github/workflows/lecture-ai.yml` every 30 minutes, or manually with "Run workflow". It transcribes lectures with Groq's free `whisper-large-v3` (Persian), sending the audio as 10-minute Opus chunks.

The API server in Iran cannot reach Groq, so the workflow:

1. Claims lectures from `GET /api/internal/lectures/claim`.
2. Downloads their audio.
3. Posts the transcript to `POST /api/internal/lectures/{id}/ai`.

Both endpoints require `LECTURE_AI_TOKEN`.

## Setup

1. Create a free key at console.groq.com.
2. In this repository's Actions secrets, add:
   - `GROQ_API_KEY`: the Groq key.
   - `LECTURE_AI_TOKEN`: a long random string. Put the same value in the backend `.env`, then run `php artisan config:cache` or redeploy.

## Review

The app never shows a transcript on its own. In `/admin`, open the lecture, correct the transcript (especially verses, hadith and names), and tick «متن را بررسی کرده‌ام». «ساخت دوباره متن» queues the lecture again. Replacing a lecture's audio also queues it again and hides the old text.

## Limits

The free tier caps audio minutes per hour and per day. When a lecture hits a limit, the workflow waits up to 25 minutes, then returns the lecture to the queue for a later run.
