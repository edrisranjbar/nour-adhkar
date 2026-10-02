"""Transcribe Nour lectures with Groq's free tier, outside Iran.

Run by .github/workflows/lecture-ai.yml. api.adhkar.ir cannot reach Groq, so this job claims
pending lectures from the backend, downloads the audio, transcribes it with Whisper, and posts the
transcript back. Nothing reaches the app until an admin reviews the text and ticks it in the panel.

Environment:
  GROQ_API_KEY         Groq key (repository secret)
  LECTURE_AI_TOKEN     shared secret, same as LECTURE_AI_TOKEN in the backend .env
  LECTURE_AI_API       backend API base, default https://api.adhkar.ir/api
  LECTURES_PER_RUN     default 1
"""

import glob
import os
import subprocess
import sys
import tempfile
import time

import requests

API = (os.environ.get("LECTURE_AI_API") or "https://api.adhkar.ir/api").rstrip("/")
TOKEN = os.environ["LECTURE_AI_TOKEN"]
GROQ_KEY = os.environ["GROQ_API_KEY"]
GROQ = "https://api.groq.com/openai/v1"
WHISPER_MODEL = "whisper-large-v3"
PER_RUN = int(os.environ.get("LECTURES_PER_RUN") or 1)

# Ten-minute mono 16 kHz Opus chunks are ~1.2 MB, far under Groq's 25 MB free-tier file limit.
CHUNK_SECONDS = 600
# Total time one lecture may spend waiting on rate limits before it is handed back for later.
MAX_WAIT_SECONDS = 25 * 60


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


def process(lecture):
    print(f"Lecture {lecture['id']}: {lecture['title']}", flush=True)
    with tempfile.TemporaryDirectory() as workdir:
        transcript = transcribe(split_audio(lecture["audioUrl"], workdir), Budget())
    if not transcript:
        raise RuntimeError("Whisper returned no text")
    return transcript


def main():
    lectures = backend("GET", "internal/lectures/claim", params={"limit": PER_RUN})["data"]
    if not lectures:
        print("No lectures waiting.")
        return 0
    failed = 0
    for lecture in lectures:
        try:
            backend("POST", f"internal/lectures/{lecture['id']}/ai", json={"transcript": process(lecture)})
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
