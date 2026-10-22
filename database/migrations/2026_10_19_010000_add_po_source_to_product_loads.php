<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pemisahan sumber barang yang di-inject ke event.
 *
 * product_loads.id_pesanan:
 *   - terisi  -> barang berasal dari PO tertentu (konsinyasi = titipan vendor, reguler = beli putus)
 *   - kosong  -> barang diambil dari stok gudang bebas
 *
 * tipe_sumber dibuang: turunan dari PO (purchase_orders.tipe_po), bukan deklarasi manual.
 * Dua tempat menyimpan fakta yang sama = cepat basi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_loads', function (Blueprint $table) {
            $table->string('id_pesanan', 20)->nullable()->index()->after('id_produk');
            $table->dropColumn('tipe_sumber');
        });
    }

    public function down(): void
    {
        Schema::table('product_loads', function (Blueprint $table) {
            $table->enum('tipe_sumber', ['reguler', 'konsinyasi'])->default('reguler')->after('id_acara');
            $table->dropColumn('id_pesanan');
        });
    }
};
