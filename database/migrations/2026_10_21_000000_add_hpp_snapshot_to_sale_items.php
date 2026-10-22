<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Snapshot HPP di baris penjualan.
 *
 * Tanpa ini, profit historis dihitung dari products.harga_pokok yang bisa berubah
 * setiap restock -> laporan event lama ikut berubah (profit retroaktif).
 * Dengan kolom ini, HPP terkunci saat transaksi terjadi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->decimal('hpp_snapshot', 15, 2)->nullable()->after('price');
        });

        // Backfill data lama: pakai harga_pokok produk saat ini (nilai terbaik yang ada).
        DB::statement('UPDATE sale_items si
            JOIN products p ON p.id_produk = si.id_produk
            SET si.hpp_snapshot = p.harga_pokok
            WHERE si.hpp_snapshot IS NULL');
    }

    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropColumn('hpp_snapshot');
        });
    }
};
