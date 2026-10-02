<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ScholarController as ApiScholarController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

/** Admin panel: a scholar's lectures. Audio is an uploaded file (public disk) or an https link. */
class LectureController extends Controller
{
    /** Upload limit in KB (100 MB). The web server and PHP limits must allow it too; see deploy notes. */
    private const MAX_UPLOAD_KB = 102400;

    public function index(int $scholarId)
    {
        $scholar = $this->scholar($scholarId);
        $items = DB::table('lectures')->where('scholar_id', $scholarId)->orderBy('sort_order')->orderBy('id')->get()
            ->map(function ($l) {
                $l->resolved_url = ApiScholarController::audioUrl($l);
                return $l;
            });
        return view('panel.lectures.index', compact('scholar', 'items'));
    }

    public function create(int $scholarId)
    {
        return view('panel.lectures.form', ['scholar' => $this->scholar($scholarId), 'lecture' => null]);
    }

    public function store(Request $request, int $scholarId)
    {
        $this->scholar($scholarId);
        $data = $this->validated($request, null);
        $next = (int) DB::table('lectures')->where('scholar_id', $scholarId)->max('sort_order') + 10;
        DB::table('lectures')->insert($data + [
            'scholar_id' => $scholarId, 'sort_order' => $next, 'created_at' => now(), 'updated_at' => now(),
        ]);
        return redirect()->route('panel.lectures.index', $scholarId)->with('status', 'سخنرانی اضافه شد.');
    }

    public function edit(int $scholarId, int $id)
    {
        return view('panel.lectures.form', ['scholar' => $this->scholar($scholarId), 'lecture' => $this->lecture($scholarId, $id)]);
    }

    public function update(Request $request, int $scholarId, int $id)
    {
        $lecture = $this->lecture($scholarId, $id);
        DB::table('lectures')->where('id', $id)->update($this->validated($request, $lecture) + ['updated_at' => now()]);
        return redirect()->route('panel.lectures.index', $scholarId)->with('status', 'سخنرانی به‌روزرسانی شد.');
    }

    public function toggle(int $scholarId, int $id)
    {
        $lecture = $this->lecture($scholarId, $id);
        DB::table('lectures')->where('id', $id)->update(['published' => !$lecture->published, 'updated_at' => now()]);
        return back()->with('status', $lecture->published ? 'سخنرانی از برنامه پنهان شد.' : 'سخنرانی در برنامه نمایش داده می‌شود.');
    }

    /**
     * «ساخت توضیح از روی صوت»: queues the lecture for the lecture-ai workflow, which transcribes and
     * summarizes it and writes the result into the description.
     */
    public function generate(int $scholarId, int $id)
    {
        $this->lecture($scholarId, $id);
        DB::table('lectures')->where('id', $id)->update([
            'ai_status' => 'queued', 'ai_error' => null, 'ai_claimed_at' => null, 'updated_at' => now(),
        ]);
        return back()->with('status', 'ساخت توضیح شروع شد. چند دقیقه طول می‌کشد؛ این صفحه را باز نگه دارید تا توضیح خودکار جایگزین شود.');
    }

    /** Polled by the edit form while a description is being generated. */
    public function aiStatus(int $scholarId, int $id)
    {
        $lecture = $this->lecture($scholarId, $id);
        return response()->json([
            'status' => $lecture->ai_status,
            'error' => $lecture->ai_status === 'failed' ? $lecture->ai_error : null,
            'description' => $lecture->ai_status === 'done' ? (string) $lecture->description : null,
        ]);
    }

    public function move(Request $request, int $scholarId, int $id, string $direction)
    {
        $this->lecture($scholarId, $id);
        Reorder::move('lectures', $id, $direction, 'scholar_id');
        if ($request->expectsJson()) {
            return response()->json(['order' => DB::table('lectures')->where('scholar_id', $scholarId)
                ->orderBy('sort_order')->orderBy('id')->pluck('id')]);
        }
        return back();
    }

    public function destroy(int $scholarId, int $id)
    {
        $lecture = $this->lecture($scholarId, $id);
        if ($lecture->audio_path) {
            Storage::disk('public')->delete($lecture->audio_path);
        }
        DB::table('lectures')->where('id', $id)->delete();
        return back()->with('status', 'سخنرانی حذف شد.');
    }

    private function validated(Request $request, ?object $existing): array
    {
        $data = $request->validate([
            'title' => 'required|string|max:200',
            'description' => 'nullable|string|max:1000000',
            'audio_file' => 'nullable|file|mimes:mp3,m4a,aac,ogg,oga,wav|max:' . self::MAX_UPLOAD_KB,
            'audio_url' => 'nullable|string|max:500|url:https',
            'duration_min' => 'nullable|integer|min:0|max:1440',
            'duration_sec_part' => 'nullable|integer|min:0|max:59',
        ], [
            'audio_file.mimes' => 'فایل صوتی باید mp3، m4a، aac، ogg یا wav باشد.',
            'audio_file.max' => 'حجم فایل صوتی حداکثر ۱۰۰ مگابایت است.',
            'audio_url.url' => 'لینک صوت باید با https:// شروع شود.',
        ], [
            'title' => 'عنوان', 'description' => 'توضیح', 'audio_file' => 'فایل صوتی', 'audio_url' => 'لینک صوت',
        ]);

        $row = [
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'published' => $request->boolean('published'),
        ];

        // Duration is no longer on the form (the app reads the real length from the audio); only
        // set it when a client still sends it, so existing values are kept.
        if ($request->hasAny(['duration_min', 'duration_sec_part'])) {
            $total = (int) ($data['duration_min'] ?? 0) * 60 + (int) ($data['duration_sec_part'] ?? 0);
            $row['duration_sec'] = $total > 0 ? $total : null;
        }

        if ($request->hasFile('audio_file')) {
            // New upload replaces any previous file or link.
            if ($existing?->audio_path) {
                Storage::disk('public')->delete($existing->audio_path);
            }
            $row['audio_path'] = $request->file('audio_file')->store('lectures', 'public');
            $row['audio_url'] = null;
            $row += self::freshAi();
        } elseif (!empty($data['audio_url'])) {
            if ($existing?->audio_path) {
                Storage::disk('public')->delete($existing->audio_path);
            }
            $row['audio_path'] = null;
            if ($existing?->audio_url !== $data['audio_url']) {
                $row += self::freshAi();
            }
            $row['audio_url'] = $data['audio_url'];
        } elseif (!$existing || (!$existing->audio_path && !$existing->audio_url)) {
            throw ValidationException::withMessages(['audio_file' => 'یک فایل صوتی بارگذاری کنید یا لینک صوت را وارد کنید.']);
        }

        return $row;
    }

    /** New audio: forget the old transcript; the admin can generate a new description from the edit form. */
    private static function freshAi(): array
    {
        return ['ai_status' => 'idle', 'ai_error' => null, 'ai_claimed_at' => null, 'transcript' => null, 'summary' => null];
    }

    private function scholar(int $id): object
    {
        $scholar = DB::table('scholars')->find($id);
        abort_unless($scholar, 404);
        return $scholar;
    }

    private function lecture(int $scholarId, int $id): object
    {
        $lecture = DB::table('lectures')->where('scholar_id', $scholarId)->find($id);
        abort_unless($lecture, 404);
        return $lecture;
    }
}
