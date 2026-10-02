# Lecture descriptions from audio

In `/admin`, open a lecture and press «ساخت توضیح از روی صوت». The workflow `.github/workflows/lecture-ai.yml` checks every 5 minutes for queued lectures. For each one, it:

1. Transcribes the audio with Groq's free `whisper-large-v3` (Persian, 10-minute Opus chunks).
2. Summarizes the transcript with a free Groq chat model: the first available from `PREFERRED_MODELS` in `process.py`, logged per run, or `GROQ_SUMMARY_MODEL` if set.
3. Posts both back. The backend replaces the lecture description with «خلاصه» + summary, then «متن کامل سخنرانی» + transcript.

The edit page checks every 15 seconds while it waits and fills in the description when it's ready. Edit it like any other description. If summarizing fails, the transcript alone is used.

The API server in Iran cannot reach Groq, which is why this runs on GitHub Actions. It uses `GET /api/internal/lectures/claim` and `POST /api/internal/lectures/{id}/ai`, both protected by `LECTURE_AI_TOKEN`.

## Setup

- Repository secrets: `GROQ_API_KEY` (free key from console.groq.com) and `LECTURE_AI_TOKEN`.
- The backend `.env` needs the same `LECTURE_AI_TOKEN`. Run `php artisan config:cache` after changing it.

## Limits

The free tier caps audio minutes and chat tokens per hour and per day. When a lecture hits a limit, the workflow waits up to 25 minutes, then keeps the lecture queued for a later run.
