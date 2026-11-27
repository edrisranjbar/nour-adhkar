import importlib.util
import os
from pathlib import Path
import unittest
from unittest.mock import Mock, patch

with patch.dict(os.environ, {"LECTURE_AI_TOKEN": "test-token"}), patch.dict("sys.modules", {"requests": Mock()}):
    spec = importlib.util.spec_from_file_location("lecture_worker", Path(__file__).with_name("process.py"))
    worker = importlib.util.module_from_spec(spec)
    spec.loader.exec_module(worker)


class WorkerTest(unittest.TestCase):
    def setUp(self):
        worker.SETTINGS = {"base_url": "https://example.org/v1", "api_key": "secret",
                           "transcription_model": "audio", "summary_model": "text"}

    def test_summary_uses_selected_provider_and_model(self):
        response = Mock(status_code=200)
        response.json.return_value = {"choices": [{"message": {"content": "Summary"}}]}
        with patch.object(worker.requests, "post", return_value=response) as post:
            self.assertEqual(worker.summarize("Transcript"), "Summary")
            self.assertEqual(post.call_args.args[0], "https://example.org/v1/chat/completions")
            self.assertEqual(post.call_args.kwargs["json"]["model"], "text")
            self.assertFalse(post.call_args.kwargs["allow_redirects"])

    def test_reports_both_outputs(self):
        with patch.object(worker, "process", return_value="Transcript"), patch.object(worker, "summarize", return_value="Summary"):
            self.assertEqual(worker.result({}), {"transcript": "Transcript", "summary": "Summary"})

    def test_provider_error_body_does_not_leak(self):
        with patch.object(worker.requests, "post", return_value=Mock(status_code=401, text="secret")):
            with self.assertRaisesRegex(RuntimeError, "^Summary service HTTP 401$"):
                worker.summarize("Transcript")


if __name__ == "__main__":
    unittest.main()
