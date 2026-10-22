<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Modul Gudang:
 * - stock_mutations: tambah jenis PENGEMBALIAN_GUDANG (sisa event balik ke gudang)
 * - product_loads: tambah tipe_sumber (konsinyasi = wajib balik vendor, reguler = balik gudang)
 * - vendor_returns: tambah id_acara (telusuri retur berasal dari event mana)
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE stock_mutations MODIFY jenis_perubahan ENUM(
            'PENERIMAAN','PENJUALAN','REFUND','PENGEMBALIAN_PEMASOK','PENGEMBALIAN_GUDANG','PENYESUAIAN','LOAD_EVENT'
        ) NOT NULL");

        Schema::table('product_loads', function (Blueprint $table) {
            $table->enum('tipe_sumber', ['reguler', 'konsinyasi'])->default('reguler')->after('id_acara');
        });

        Schema::table('vendor_returns', function (Blueprint $table) {
            $table->string('id_acara', 20)->nullable()->index()->after('id_penerimaan');
        });
    }

    public function down(): void
    {
        Schema::table('vendor_returns', fn (Blueprint $t) => $t->dropColumn('id_acara'));
        Schema::table('product_loads', fn (Blueprint $t) => $t->dropColumn('tipe_sumber'));
        DB::statement("ALTER TABLE stock_mutations MODIFY jenis_perubahan ENUM(
            'PENERIMAAN','PENJUALAN','REFUND','PENGEMBALIAN_PEMASOK','PENYESUAIAN','LOAD_EVENT'
        ) NOT NULL");
    }
};
