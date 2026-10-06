"""Publish due JPEG posts from the admin queue using Instagram Login credentials.

Never replay a media_publish request: timeouts may mean the post was already published.
Credentials stay in environment variables and are never printed.
"""
import json
import os
import time
import urllib.error
import urllib.parse
import urllib.request


def request(url, token, data=None):
    body = urllib.parse.urlencode(data).encode() if data is not None else None
    req = urllib.request.Request(url, data=body, headers={"Authorization": "Bearer " + token, "Accept": "application/json"})
    try:
        with urllib.request.urlopen(req, timeout=45) as response:
            return json.load(response)
    except urllib.error.HTTPError as exc:
        # Do not log request URLs, tokens, or unfiltered third-party response bodies.
        raise RuntimeError(f"HTTP {exc.code}") from None


def publish(task, api, worker_token, account, token, graph, pause=time.sleep):
    def report(stage, **fields):
        return request(f"{api}/internal/instagram/{task['id']}/report", worker_token,
                       {"claim": task["claim"], "stage": stage, **fields})

    started = False
    try:
        container = request(f"{graph}/{account}/media", token, {"image_url": task["image_url"], "caption": task["caption"]})["id"]
        report("container", container_id=container)
        for _ in range(12):
            state = request(f"{graph}/{container}?fields=status_code", token).get("status_code")
            if state == "FINISHED":
                break
            if state in ("ERROR", "EXPIRED"):
                raise RuntimeError("Image processing failed")
            pause(5)
        else:
            raise RuntimeError("Image processing did not finish")
        report("publish_started")
        started = True
        media = request(f"{graph}/{account}/media_publish", token, {"creation_id": container})["id"]
        permalink = request(f"{graph}/{media}?fields=permalink", token)["permalink"]
        # Safe to repeat this database report, unlike the actual publishing request.
        for attempt in range(3):
            try:
                report("published", media_id=media, permalink=permalink)
                return
            except Exception:
                if attempt == 2:
                    raise RuntimeError("Publication result needs manual verification") from None
                pause(2)
    except Exception:
        try:
            report("failed", error="نتیجه ارسال باید در اینستاگرام بررسی شود؛ برای جلوگیری از پست تکراری، ارسال خودکار متوقف شد." if started else "ارسال انجام نشد؛ اتصال، مجوز حساب و دسترسی به تصویر را بررسی کنید.")
        finally:
            raise RuntimeError("Instagram publishing failed; check the admin queue") from None


def main():
    api = os.environ.get("INSTAGRAM_API_URL", "https://api.adhkar.ir/api").rstrip("/")
    account = os.environ["INSTAGRAM_ACCOUNT_ID"]
    token = os.environ["INSTAGRAM_ACCESS_TOKEN"]
    worker_token = os.environ["INSTAGRAM_PUBLISH_TOKEN"]
    version = os.environ.get("INSTAGRAM_GRAPH_VERSION", "v25.0")
    graph = "https://graph.instagram.com/" + version
    for _ in range(5):
        task = request(f"{api}/internal/instagram/claim", worker_token, {"account_id": account}).get("data")
        if not task:
            return
        publish(task, api, worker_token, account, token, graph)
        print("Published one scheduled post")


if __name__ == "__main__":
    try:
        main()
    except Exception:
        print("Scheduled publishing stopped. Check account configuration and the admin queue.")
        raise SystemExit(1) from None
