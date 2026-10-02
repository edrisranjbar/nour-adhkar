# Lecture transcripts and summaries

`process.py` runs from `.github/workflows/lecture-ai.yml` every 30 minutes, or manually with "Run workflow". It transcribes lectures with Groq's free `whisper-large-v3` (Persian), sending the audio as 10-minute Opus chunks. It then summarizes each transcript with a free Groq chat model: the first available model from `PREFERRED_MODELS` in `process.py`, logged on every run, or `GROQ_SUMMARY_MODEL` if that variable is set. If summarizing fails, the transcript is still saved.

The API server in Iran cannot reach Groq, so the workflow:

1. Claims lectures from `GET /api/internal/lectures/claim`.
2. Downloads their audio.
3. Posts the transcript and summary to `POST /api/internal/lectures/{id}/ai`.

Both endpoints require `LECTURE_AI_TOKEN`.

## Setup

1. Create a free key at console.groq.com.
2. In this repository's Actions secrets, add:
   - `GROQ_API_KEY`: the Groq key.
   - `LECTURE_AI_TOKEN`: a long random string. Put the same value in the backend `.env`, then run `php artisan config:cache` or redeploy.

## Review

The app never shows a transcript on its own. In `/admin`, open the lecture, correct the summary and transcript (especially verses, hadith and names), and tick «خلاصه و متن را بررسی کرده‌ام». The app then shows the summary, the lecture's own description, and the transcript in the lecture description, so older app versions get them too. «ساخت دوباره خلاصه و متن» queues the lecture again. Replacing a lecture's audio also queues it again and hides the old text.

## Limits

The free tier caps audio minutes and chat tokens per hour and per day. When a lecture hits a limit, the workflow waits up to 25 minutes, then returns the lecture to the queue for a later run.
