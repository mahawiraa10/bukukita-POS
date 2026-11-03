<?php

namespace App\Support;

use Illuminate\Database\Eloquent\SoftDeletes;

class IdGen
{
    // ID string berbasis prefix + urut 5 digit, PK bisa diubah admin di form master.
    public static function next(string $modelClass, string $pk, string $prefix, int $pad = 5): string
    {
        // Jangan pakai max($pk) string: kalau tabel campur prefix (seed lama pakai VND-,
        // generator pakai VDR-) max() balik prefix lain -> nomor ke-reset -> duplikat (1062).
        // Row SoftDeleted ikut dihitung, atau ID bekas row trashed tabrakan.
        $query = in_array(SoftDeletes::class, class_uses_recursive($modelClass), true)
            ? $modelClass::withTrashed()
            : $modelClass::query();

        $max = 0;
        foreach ($query->pluck($pk) as $id) {
            if (str_contains((string) $id, '-')) {
                $n = (int) substr((string) $id, strrpos((string) $id, '-') + 1);
                $max = max($max, $n);
            }
        }
        return $prefix . '-' . str_pad((string) ($max + 1), $pad, '0', STR_PAD_LEFT);
    }
}
