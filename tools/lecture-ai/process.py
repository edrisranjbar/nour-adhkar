"""Transcribe Nour lectures with Groq's free Whisper, outside Iran.

Run by .github/workflows/lecture-ai.yml. api.adhkar.ir cannot reach Groq, so this job claims the
lectures an admin queued with «ساخت توضیح از روی صوت», downloads the audio, transcribes all parts in
parallel, and posts the transcript back; the backend writes it into the lecture description.

Each run keeps checking the queue for WATCH_MINUTES, so a click on the button is picked up within
POLL_SECONDS while a run is alive, rather than waiting for the next scheduled start.

Environment:
  GROQ_API_KEY         Groq key (repository secret)
  LECTURE_AI_TOKEN     shared secret, same as LECTURE_AI_TOKEN in the backend .env
  LECTURE_AI_API       backend API base, default https://api.adhkar.ir/api
  WATCH_MINUTES        how long one run keeps checking the queue, default 28
"""

import glob
import os
import subprocess
import sys
import tempfile
import time
from concurrent.futures import ThreadPoolExecutor

import requests

API = (os.environ.get("LECTURE_AI_API") or "https://api.adhkar.ir/api").rstrip("/")
TOKEN = os.environ["LECTURE_AI_TOKEN"]
GROQ_KEY = os.environ["GROQ_API_KEY"]
GROQ = "https://api.groq.com/openai/v1"
WHISPER_MODEL = "whisper-large-v3"
WATCH_MINUTES = float(os.environ.get("WATCH_MINUTES") or 28)
POLL_SECONDS = 20

# Five-minute mono 16 kHz Opus parts (~0.6 MB each), sent to Whisper several at a time.
CHUNK_SECONDS = 300
PARALLEL = 6
# Total time one lecture may spend waiting on rate limits before it is handed back for later.
MAX_WAIT_SECONDS = 15 * 60


class RetryLater(Exception):
    """A rate limit outlasted MAX_WAIT_SECONDS; the lecture goes back to the queue."""


def backend(method, path, **kwargs):
    response = requests.request(
        method, f"{API}/{path}", timeout=60,
        headers={"Authorization": f"Bearer {TOKEN}", "Accept": "application/json"}, **kwargs,
    )
    response.raise_for_status()
    return response.json()


def transcribe_part(path):
    """One part, retrying on rate limits (honouring retry-after) and on Groq server errors."""
    with open(path, "rb") as audio:
        payload = audio.read()
    waited = 0.0
    for _ in range(40):
        response = requests.post(
            f"{GROQ}/audio/transcriptions", timeout=300,
            headers={"Authorization": f"Bearer {GROQ_KEY}"},
            files={"file": (os.path.basename(path), payload, "audio/ogg")},
            data={"model": WHISPER_MODEL, "language": "fa", "response_format": "text", "temperature": "0"},
        )
        if response.status_code == 429:
            delay = float(response.headers.get("retry-after") or 30)
            if waited + delay > MAX_WAIT_SECONDS:
                raise RetryLater(f"Groq rate limit (retry after {int(delay)}s)")
            time.sleep(delay + 1)
            waited += delay
            continue
        if response.status_code >= 500:
            time.sleep(5)
            continue
        if response.status_code >= 400:
            raise RuntimeError(f"Groq {response.status_code}: {response.text[:300]}")
        return response.text.strip()
    raise RuntimeError("Groq kept failing")


def split_audio(url, workdir):
    source = os.path.join(workdir, "source")
    with requests.get(url, stream=True, timeout=120) as response:
        response.raise_for_status()
        with open(source, "wb") as out:
            for block in response.iter_content(1 << 20):
                out.write(block)
    subprocess.run(
        ["ffmpeg", "-loglevel", "error", "-threads", "0", "-i", source, "-vn", "-ac", "1", "-ar", "16000",
         "-c:a", "libopus", "-b:a", "16k", "-f", "segment", "-segment_time", str(CHUNK_SECONDS),
         os.path.join(workdir, "part%03d.ogg")],
        check=True,
    )
    return sorted(glob.glob(os.path.join(workdir, "part*.ogg")))


def process(lecture):
    started = time.time()
    print(f"Lecture {lecture['id']}: {lecture['title']}", flush=True)
    with tempfile.TemporaryDirectory() as workdir:
        parts = split_audio(lecture["audioUrl"], workdir)
        print(f"  {len(parts)} parts, transcribing {min(PARALLEL, len(parts))} at a time", flush=True)
        with ThreadPoolExecutor(max_workers=PARALLEL) as pool:
            texts = list(pool.map(transcribe_part, parts))  # map keeps the parts in order
    transcript = "\n\n".join(t for t in texts if t)
    if not transcript:
        raise RuntimeError("Whisper returned no text")
    print(f"  transcribed in {int(time.time() - started)}s", flush=True)
    return transcript


def handle(lecture):
    try:
        backend("POST", f"internal/lectures/{lecture['id']}/ai", json={"transcript": process(lecture)})
        print("  done", flush=True)
        return True
    except RetryLater as error:
        backend("POST", f"internal/lectures/{lecture['id']}/ai", json={"error": str(error), "retry": True})
        print(f"  postponed: {error}", flush=True)
    except Exception as error:  # report every other failure to the panel, then keep watching
        backend("POST", f"internal/lectures/{lecture['id']}/ai", json={"error": str(error)[:1000]})
        print(f"  failed: {error}", flush=True)
    return False


def main():
    deadline = time.time() + WATCH_MINUTES * 60
    failed = 0
    while True:
        try:
            lectures = backend("GET", "internal/lectures/claim", params={"limit": 1})["data"]
        except requests.RequestException as error:
            print(f"Queue check failed: {error}", flush=True)
            lectures = []
        for lecture in lectures:
            if not handle(lecture):
                failed += 1
        if lectures:
            continue  # look again straight away; more may be queued
        if time.time() + POLL_SECONDS > deadline:
            break
        time.sleep(POLL_SECONDS)
    print("Finished watching the queue.")
    return 1 if failed else 0


if __name__ == "__main__":
    sys.exit(main())
