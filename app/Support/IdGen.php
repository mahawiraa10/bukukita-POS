<?php

namespace App\Support;

class IdGen
{
    // ID string berbasis prefix + urut 5 digit, PK bisa diubah admin di form master.
    public static function next(string $modelClass, string $pk, string $prefix): string
    {
        $last = $modelClass::max($pk);
        $n = $last ? ((int) substr((string) $last, strlen($prefix) + 1)) + 1 : 1;
        return $prefix . '-' . str_pad((string) $n, 5, '0', STR_PAD_LEFT);
    }
}
