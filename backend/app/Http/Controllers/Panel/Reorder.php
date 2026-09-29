<?php

namespace App\Http\Controllers\Panel;

use Illuminate\Support\Facades\DB;

/** Moves a row up or down by rewriting sort_order in steps of 10 (optionally within a parent). */
final class Reorder
{
    public static function move(string $table, int $id, string $direction, ?string $parentColumn = null): void
    {
        abort_unless(in_array($direction, ['up', 'down'], true), 404);
        $row = DB::table($table)->find($id);
        abort_unless($row, 404);

        $query = DB::table($table)->orderBy('sort_order')->orderBy('id');
        if ($parentColumn) {
            $query->where($parentColumn, $row->{$parentColumn});
        }
        $ordered = $query->pluck('id')->all();
        $index = array_search($id, $ordered, true);
        $swapWith = $direction === 'up' ? $index - 1 : $index + 1;
        if (!isset($ordered[$swapWith])) {
            return;
        }
        [$ordered[$index], $ordered[$swapWith]] = [$ordered[$swapWith], $ordered[$index]];
        DB::transaction(function () use ($table, $ordered) {
            foreach ($ordered as $position => $rowId) {
                DB::table($table)->where('id', $rowId)->update(['sort_order' => ($position + 1) * 10]);
            }
        });
    }
}
