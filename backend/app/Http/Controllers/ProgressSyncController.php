<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProgressSyncController extends Controller
{
    public function sync(Request $request)
    {
        if (strlen($request->getContent()) > 4_000_000) {
            abort(413);
        }
        $data = $request->validate([
            'version' => ['required', 'integer', 'in:1'],
            'records' => ['present', 'array', 'max:10000'],
            'records.*' => ['required', 'array:key,modified,device,deleted,value'],
            'records.*.key' => ['required', 'string', 'max:1024', 'distinct'],
            'records.*.modified' => ['required', 'integer', 'min:1', 'max:'.(now()->getTimestampMs() + 86_400_000)],
            'records.*.device' => ['required', 'uuid'],
            'records.*.deleted' => ['required', 'boolean'],
            'records.*.value' => ['present'],
        ]);
        foreach ($data['records'] as $record) {
            if (!$this->validRecord($record)) {
                throw ValidationException::withMessages(['records' => 'Invalid progress record.']);
            }
        }

        $records = DB::transaction(function () use ($request, $data) {
            $userId = $request->user()->getAuthIdentifier();
            // Lock the owner even before the first backup exists, serializing concurrent devices.
            DB::table('users')->where('id', $userId)->lockForUpdate()->first();
            $backup = DB::table('app_progress_backups')->where('user_id', $userId)->first();
            $records = $backup ? json_decode($backup->records, true, 512, JSON_THROW_ON_ERROR) : [];
            foreach ($data['records'] as $incoming) {
                $old = $records[$incoming['key']] ?? null;
                if (!$old || $incoming['modified'] > $old['modified'] ||
                    ($incoming['modified'] === $old['modified'] && strcmp($incoming['device'], $old['device']) > 0)) {
                    $records[$incoming['key']] = $incoming;
                }
            }
            $json = json_encode($records, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
            if (count($records) > 10000 || strlen($json) > 3_900_000) {
                abort(413);
            }
            DB::table('app_progress_backups')->updateOrInsert(['user_id' => $userId], [
                'records' => $json, 'created_at' => $backup?->created_at ?? now(), 'updated_at' => now(),
            ]);
            return array_values($records);
        });
        return response()->json(['version' => 1, 'records' => $records]);
    }

    private function validRecord(array $record): bool
    {
        $key = $record['key'];
        $known = ['activity_day_keys', 'favorite_dhikr_keys', 'custom_dhikr', 'tasbih_counts_map',
            'quran_last_read_page', 'quran_highlights', 'quran_notes', 'quran_khatm_goal', 'quran_khatm_daily_logs'];
        if (preg_match('/^[ps]:([^:]+)(?::([A-Za-z0-9_-]+))?$/D', $key, $matches)) {
            $name = $matches[1];
            if (!in_array($name, $known, true) && !preg_match('/^daily_checklist_-?\d+$/D', $name) &&
                !preg_match('/^[a-z_]+_completed_day$/D', $name)) {
                return false;
            }
            if ($record['deleted']) return $record['value'] === null;
            if ($key[0] === 's') return isset($matches[2]) && $record['value'] === true;
            $value = $record['value'];
            return is_array($value) && isset($value['type'], $value['value']) &&
                match ($value['type']) {
                    'string' => is_string($value['value']) && strlen($value['value']) <= 1_000_000,
                    'int', 'long' => is_int($value['value']),
                    default => false,
                };
        }
        if ($key === 'q:state') return $record['deleted'] ? $record['value'] === null : is_string($record['value']);
        if (preg_match('/^h:[a-f0-9]{64}$/D', $key)) {
            if ($record['deleted']) return $record['value'] === null;
            $v = $record['value'];
            return is_array($v) && isset($v['name'], $v['count'], $v['time']) && is_string($v['name']) &&
                strlen($v['name']) <= 10000 && is_int($v['count']) && $v['count'] > 0 && is_int($v['time']) && $v['time'] > 0;
        }
        if (preg_match('/^d:([a-z_]+)_(\d+)$/D', $key, $m)) {
            if ($record['deleted']) return $record['value'] === null;
            $v = $record['value'];
            return is_array($v) && isset($v['category'], $v['dhikr'], $v['count'], $v['target'], $v['updated']) &&
                $v['category'] === $m[1] && $v['dhikr'] === (int) $m[2] && is_int($v['count']) && $v['count'] >= 0 &&
                is_int($v['target']) && $v['target'] > 0 && is_int($v['updated']);
        }
        return false;
    }
}
