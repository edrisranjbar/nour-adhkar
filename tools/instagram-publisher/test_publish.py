import unittest
from unittest.mock import patch
import publish

class PublisherTest(unittest.TestCase):
    def test_never_replays_ambiguous_publish(self):
        calls = []
        def request(url, token, data=None):
            calls.append((url, data))
            if url.endswith('/media_publish'):
                raise TimeoutError()
            if url.endswith('/media'):
                return {'id': '111'}
            if 'status_code' in url:
                return {'status_code': 'FINISHED'}
            return {}
        with patch.object(publish, 'request', request):
            with self.assertRaises(RuntimeError):
                publish.publish({'id': 1, 'claim': 'test', 'caption': 'test', 'image_url': 'https://example.test/image.jpg'}, 'https://api.test', 'worker', '123', 'token', 'https://graph.test', pause=lambda _: None)
        self.assertEqual(sum(url.endswith('/media_publish') for url, _ in calls), 1)
        self.assertEqual([data['stage'] for url, data in calls if url.endswith('/report')], ['container', 'publish_started', 'failed'])

    def test_only_result_recording_is_retried(self):
        calls = []; attempts = 0
        def request(url, token, data=None):
            nonlocal attempts
            calls.append((url, data))
            if url.endswith('/media'): return {'id': '111'}
            if 'status_code' in url: return {'status_code': 'FINISHED'}
            if url.endswith('/media_publish'): return {'id': '222'}
            if 'permalink' in url: return {'permalink': 'https://www.instagram.com/p/Test/'}
            if data.get('stage') == 'published':
                attempts += 1
                if attempts < 3: raise TimeoutError()
            return {}
        with patch.object(publish, 'request', request):
            publish.publish({'id': 1, 'claim': 'test', 'caption': 'test', 'image_url': 'https://example.test/image.jpg'}, 'https://api.test', 'worker', '123', 'token', 'https://graph.test', pause=lambda _: None)
        self.assertEqual(sum(url.endswith('/media_publish') for url, _ in calls), 1)
        self.assertEqual(attempts, 3)

if __name__ == '__main__': unittest.main()
