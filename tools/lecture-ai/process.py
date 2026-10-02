"""Transcribe and summarize Nour lectures with Groq's free tier, outside Iran.

Run by .github/workflows/lecture-ai.yml. api.adhkar.ir cannot reach Groq, so this job claims
pending lectures from the backend, downloads the audio, transcribes it with Whisper, summarizes
the transcript with an LLM, and posts both back. Nothing reaches the app until an admin reviews
the text and ticks «نمایش در برنامه» in the panel.

Environment:
  GROQ_API_KEY         Groq key (repository secret)
  LECTURE_AI_TOKEN     shared secret, same as LECTURE_AI_TOKEN in the backend .env
  LECTURE_AI_API       backend API base, default https://api.adhkar.ir/api
  GROQ_SUMMARY_MODEL   chat model for summaries, default llama-3.3-70b-versatile
  LECTURES_PER_RUN     default 1
"""

import glob
import os
import subprocess
import sys
import tempfile
import time

import requests

API = os.environ.get("LECTURE_AI_API", "https://api.adhkar.ir/api").rstrip("/")
TOKEN = os.environ["LECTURE_AI_TOKEN"]
GROQ_KEY = os.environ["GROQ_API_KEY"]
GROQ = "https://api.groq.com/openai/v1"
WHISPER_MODEL = "whisper-large-v3"
SUMMARY_MODEL = os.environ.get("GROQ_SUMMARY_MODEL") or "llama-3.3-70b-versatile"
PER_RUN = int(os.environ.get("LECTURES_PER_RUN") or 1)

# Ten-minute mono 16 kHz Opus chunks are ~1.2 MB, far under Groq's 25 MB free-tier file limit.
CHUNK_SECONDS = 600
# Transcript slices for the summary's first pass; sized for the free tier's tokens-per-minute.
SUMMARY_SLICE_CHARS = 6000
# Total time one lecture may spend waiting on rate limits before it is handed back for later.
MAX_WAIT_SECONDS = 25 * 60

SYSTEM_PROMPT = (
    "تو خلاصه‌نویس دقیق سخنرانی‌های دینی فارسی هستی. فقط آنچه سخنران واقعاً گفته را بنویس؛ "
    "هیچ آیه، حدیث، حکم یا نظری اضافه نکن و چیزی را به سخنران نسبت نده که در متن نیست. "
    "متن با تبدیل خودکار گفتار به نوشتار ساخته شده و ممکن است غلط داشته باشد؛ اگر بخشی نامفهوم است، از آن بگذر. "
    "به فارسی روان و ساده بنویس. از قالب‌بندی مارک‌داون، عنوان و ستاره استفاده نکن."
)


class RetryLater(Exception):
    """A rate limit outlasted MAX_WAIT_SECONDS; the lecture goes back to the queue."""


class Budget:
    def __init__(self):
        self.waited = 0

    def sleep(self, response):
        delay = float(response.headers.get("retry-after") or 30)
        if self.waited + delay > MAX_WAIT_SECONDS:
            raise RetryLater(f"Groq rate limit (retry after {int(delay)}s)")
        print(f"  rate limited, waiting {int(delay)}s", flush=True)
        time.sleep(delay + 1)
        self.waited += delay


def backend(method, path, **kwargs):
    response = requests.request(
        method, f"{API}/{path}", timeout=60,
        headers={"Authorization": f"Bearer {TOKEN}", "Accept": "application/json"}, **kwargs,
    )
    response.raise_for_status()
    return response.json()


def groq(path, budget, **kwargs):
    for _ in range(50):
        response = requests.post(
            f"{GROQ}/{path}", timeout=300, headers={"Authorization": f"Bearer {GROQ_KEY}"}, **kwargs,
        )
        if response.status_code == 429:
            budget.sleep(response)
            continue
        if response.status_code >= 500:
            time.sleep(10)
            continue
        if response.status_code >= 400:
            raise RuntimeError(f"Groq {response.status_code}: {response.text[:300]}")
        return response
    raise RuntimeError("Groq kept failing")


def split_audio(url, workdir):
    source = os.path.join(workdir, "source")
    with requests.get(url, stream=True, timeout=120) as response:
        response.raise_for_status()
        with open(source, "wb") as out:
            for block in response.iter_content(1 << 20):
                out.write(block)
    subprocess.run(
        ["ffmpeg", "-loglevel", "error", "-i", source, "-vn", "-ac", "1", "-ar", "16000",
         "-c:a", "libopus", "-b:a", "16k", "-f", "segment", "-segment_time", str(CHUNK_SECONDS),
         os.path.join(workdir, "chunk%03d.ogg")],
        check=True,
    )
    return sorted(glob.glob(os.path.join(workdir, "chunk*.ogg")))


def transcribe(chunks, budget):
    parts = []
    for index, path in enumerate(chunks, 1):
        print(f"  transcribing {index}/{len(chunks)}", flush=True)
        with open(path, "rb") as audio:
            response = groq(
                "audio/transcriptions", budget,
                files={"file": (os.path.basename(path), audio.read(), "audio/ogg")},
                data={"model": WHISPER_MODEL, "language": "fa", "response_format": "text", "temperature": "0"},
            )
        text = response.text.strip()
        if text:
            parts.append(text)
    return "\n\n".join(parts)


def chat(prompt, budget, max_tokens):
    response = groq(
        "chat/completions", budget,
        json={
            "model": SUMMARY_MODEL, "temperature": 0.2, "max_tokens": max_tokens,
            "messages": [{"role": "system", "content": SYSTEM_PROMPT}, {"role": "user", "content": prompt}],
        },
    )
    return response.json()["choices"][0]["message"]["content"].strip()


def slices(text):
    """Splits on paragraph or sentence boundaries into pieces of about SUMMARY_SLICE_CHARS."""
    pieces, current = [], ""
    for sentence in text.replace("\n", " ").split(". "):
        if current and len(current) + len(sentence) > SUMMARY_SLICE_CHARS:
            pieces.append(current)
            current = ""
        current += sentence + ". "
    if current.strip():
        pieces.append(current)
    # Whisper sometimes emits long runs without punctuation; cut those at a fixed size.
    return [p[i:i + SUMMARY_SLICE_CHARS] for p in pieces for i in range(0, len(p), SUMMARY_SLICE_CHARS)]


def summarize(title, transcript, budget):
    notes = []
    pieces = slices(transcript)
    for index, piece in enumerate(pieces, 1):
        print(f"  summarizing part {index}/{len(pieces)}", flush=True)
        notes.append(chat(
            f"بخش {index} از {len(pieces)} سخنرانی «{title}». نکته‌های اصلی این بخش را "
            f"در چند خط کوتاه، هر خط با «• »، بنویس:\n\n{piece}",
            budget, 600,
        ))
    return chat(
        f"این‌ها یادداشت‌های بخش‌های پیاپی سخنرانی «{title}» است. از روی آن‌ها بنویس:\n"
        "۱. یک بند کوتاه (سه تا پنج جمله) درباره موضوع و پیام اصلی سخنرانی.\n"
        "۲. یک خط خالی و سپس چهار تا هشت نکته اصلی، هر خط با «• ».\n"
        "فقط همین دو بخش را بنویس.\n\n" + "\n\n".join(notes),
        budget, 1200,
    )


def process(lecture):
    print(f"Lecture {lecture['id']}: {lecture['title']}", flush=True)
    budget = Budget()
    with tempfile.TemporaryDirectory() as workdir:
        transcript = transcribe(split_audio(lecture["audioUrl"], workdir), budget)
    if not transcript:
        raise RuntimeError("Whisper returned no text")
    summary = summarize(lecture["title"], transcript, budget)
    return transcript, summary


def main():
    lectures = backend("GET", "internal/lectures/claim", params={"limit": PER_RUN})["data"]
    if not lectures:
        print("No lectures waiting.")
        return 0
    failed = 0
    for lecture in lectures:
        try:
            transcript, summary = process(lecture)
            backend("POST", f"internal/lectures/{lecture['id']}/ai", json={"transcript": transcript, "summary": summary})
            print("  done", flush=True)
        except RetryLater as error:
            backend("POST", f"internal/lectures/{lecture['id']}/ai", json={"error": str(error), "retry": True})
            print(f"  postponed: {error}", flush=True)
        except Exception as error:  # report every other failure to the panel, then continue
            failed += 1
            backend("POST", f"internal/lectures/{lecture['id']}/ai", json={"error": str(error)[:1000]})
            print(f"  failed: {error}", flush=True)
    return 1 if failed else 0


if __name__ == "__main__":
    sys.exit(main())
