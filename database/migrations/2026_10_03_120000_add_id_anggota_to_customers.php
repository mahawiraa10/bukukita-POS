<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('id_anggota', 20)->nullable()->unique()->after('id');
        });

        // Backfill: MBR-00001, MBR-00002, ...
        $n = 0;
        foreach (DB::table('customers')->orderBy('id')->pluck('id') as $id) {
            $n++;
            DB::table('customers')->where('id', $id)
                ->update(['id_anggota' => 'MBR-' . str_pad((string) $n, 5, '0', STR_PAD_LEFT)]);
        }
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('id_anggota');
        });
    }
};
