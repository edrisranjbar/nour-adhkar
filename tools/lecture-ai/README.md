# Lecture descriptions from audio

The admin edit button now POSTs directly to its authenticated generation endpoint using AJAX.
There is no GitHub Action or external worker queue. The scheduled lecture-ai workflow and its
internal claim/config/report routes have been removed. Old queued lectures can be retried from
their edit screen without waiting for an external worker.

The button disables immediately, shows progress text, then puts the saved description into the
textarea without reloading. If the textarea was edited during processing, that edit is retained
and the user is told that the generated version was saved on the server. Errors are displayed,
the previous description stays intact on failure, and retry becomes available.
Generation automatically saves transcript, summary and description; other unsaved form fields
are not saved. A per-lecture lock prevents concurrent duplicate requests.

Configure the HTTPS provider URL, encrypted API key and separate audio/text models at
/admin/settings/lecture-ai. All fetched models can be selected regardless of pricing. The
provider must support multipart /audio/transcriptions and JSON /chat/completions.
A text-only model cannot handle audio/transcriptions. Changing provider clears model selection.
Blank key retains the existing key. No key is populated into the HTML or flashed to session input.

The PHP server itself must reach the provider. Requests have a 50-second combined processing
budget, with a 60-second browser timeout. This is a response/error budget, not a guarantee that
long lectures complete in under a minute. Direct audio input is limited to 25 MB. Remote audio
download has a 12-second timeout; uploaded audio is read locally without a self-HTTP download.
The web server/PHP proxy timeout must allow the request budget. Connection, HTTP and empty-output
errors are reported without exposing provider response bodies or keys.

Summary failure does not silently mark the lecture complete. Audio changes during generation
prevent the old result from replacing the new audio's description. Public comment and store-stat
refreshes are unaffected.

Tests: backend/tests/Feature/LectureGenerationTest.php and LectureAiSettingsTest.php use mocked
provider calls; no live account request was made. Live compatibility and speed need verification
from the deployed server with an actual compatible audio model and a sample lecture.
The scripts under tools/lecture-ai are retained only as historical tools and are not invoked.

### HTTP 413 recovery

A provider 413 triggers local ffmpeg conversion into two-minute mono 16 kHz / 16 kbps MP3
parts. Every part must produce text; transcripts are concatenated in order before one summary.
No partial result replaces the saved description. Temporary parts are removed on success/failure.
The existing 50-second deadline still applies, including conversion and all provider calls.
ffmpeg with libmp3lame must be installed and executable by PHP-FPM on the server. It is not
installed by this code. A missing/failed binary, repeated 413 or timeout shows a specific error.
The server's ffmpeg availability and actual provider size limit still need live verification.

### MiMo V2.6 audio via chat completions

MiMo V2.6 models use POST /chat/completions with a user input_audio content block whose
input_audio.data is the public HTTPS audio URL, as documented by Xiaomi. Model IDs containing
mimo-v2.6 select this transport; the exact catalog ID is forwarded unchanged. No audio file
is uploaded to the gateway and no /audio/transcriptions call is made for these models.
The provider must be able to download the public lecture URL. Transcription and summary remain
two calls using the configured respective models and one shared deadline. Truncated transcription
is rejected. Error messages include a sanitized x-request-id when the gateway supplies it.

ManageIt returned "The route api/v1/audio/transcriptions could not be found" on a direct
unauthenticated POST, while /chat/completions reached authentication (401). Their published
guide lists chat/completions but no audio/transcriptions route. A gateway route rejection
does not establish whether a model invocation occurred or what the account dashboard logs.
Live authenticated forwarding of input_audio through ManageIt remains unverified.

Sources: https://ai.manageit.dev/llms-full.txt
https://mimo.mi.com/docs/en-US/quick-start/usage-guide/multimodal-understanding/audio-understanding

The x-manageit-output-capped header only reports that an output allowance was lowered; it is
not evidence that the response was truncated. Completed responses with finish_reason=stop
are accepted even with that header. Actual finish_reason=length in either transcription or
summary prevents replacement of the existing description and reports the output-limit failure.

### Separate AJAX phase budgets and resumable summaries

The UI now POSTs phase=transcribe, then phase=summary. Each request has its own 50-second
server deadline and 60-second browser timeout; the total operation may therefore exceed one
minute. This removes the previous shared deadline that starved summarization after a long
successful transcription. It does not guarantee completion of any individual provider call.

Completed transcripts are cached for 15 minutes, keyed by lecture ID, audio source and saved
provider/model configuration. Retries reuse this transcript for the summary phase. Existing
descriptions are only replaced after summary success. Audio/configuration changes invalidate
the resume key. Cache eviction or expiry requires a new transcription. The phases are protected
by the same authenticated route and per-lecture lock. No GitHub job or background worker is used.

Connection diagnostics identify transcription versus summary and log only lecture ID, phase
and elapsed milliseconds (no keys, transcript or audio). Provider HTTP 200 in an upstream
dashboard alone does not prove full delivery to our client or success of the second call.
