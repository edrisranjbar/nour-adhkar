# Lecture transcripts and summaries

`process.py` runs from `.github/workflows/lecture-ai.yml` every 30 minutes (or manually with "Run workflow"). It uses Groq's free tier:

- `whisper-large-v3` for Persian speech-to-text, sent as 10-minute Opus chunks.
- The first available model from `PREFERRED_MODELS` in `process.py` for the summary (Groq retires models, so the script checks Groq's model list and logs its choice; set the repository variable `GROQ_SUMMARY_MODEL` to force one). It summarizes each slice of the transcript first, then the whole lecture.

The API server in Iran cannot reach Groq, so the workflow does the work in three steps:

1. Claims lectures from `GET /api/internal/lectures/claim`.
2. Downloads their audio.
3. Posts the result to `POST /api/internal/lectures/{id}/ai`.

Both endpoints require `LECTURE_AI_TOKEN`.

## Setup

1. Create a free key at console.groq.com.
2. In this repository's Settings → Secrets and variables → Actions, add:
   - `GROQ_API_KEY`: the Groq key.
   - `LECTURE_AI_TOKEN`: a long random string. Put the same value in the backend `.env` as `LECTURE_AI_TOKEN`.

## Review

Generated text is never shown in the app on its own. In `/admin`, open the lecture, correct the summary and transcript (especially verses, hadith and names), and tick «خلاصه و متن را بررسی کرده‌ام». «ساخت دوباره متن و خلاصه» queues a lecture again. Replacing a lecture's audio also queues it again and hides the old text.

## Limits

The free tier caps audio minutes and summary tokens per hour and per day. When a lecture hits a limit, the workflow waits up to 25 minutes, then returns the lecture to the queue for a later run. Roughly a few hours of audio per day can be processed, depending on Groq's current free limits.
