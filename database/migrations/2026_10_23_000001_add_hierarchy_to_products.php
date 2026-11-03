<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Lengkapi 4 level hierarki langsung di produk biar kolom KATEGORI
            // bisa render penuh + kasir bisa sortir tanpa join berantai.
            $table->string('id_divisi', 10)->nullable()->after('id_pemasok');
            $table->string('id_kelas', 10)->nullable()->after('id_departemen');
            $table->foreign('id_divisi')->references('id_divisi')->on('divisions')->restrictOnDelete();
            $table->foreign('id_kelas')->references('id_kelas')->on('classes')->restrictOnDelete();
        });

        // Backfill produk lama dari rantai existing: subkelas -> kelas -> departemen -> divisi
        DB::statement('
            UPDATE products p
            LEFT JOIN subclasses sc ON sc.id_subkelas = p.id_subkelas
            LEFT JOIN departments d ON d.id_departemen = p.id_departemen
            SET p.id_kelas  = COALESCE(p.id_kelas, sc.id_kelas),
                p.id_divisi = COALESCE(p.id_divisi, d.id_divisi)
        ');
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['id_divisi']);
            $table->dropForeign(['id_kelas']);
            $table->dropColumn(['id_divisi', 'id_kelas']);
        });
    }
};
