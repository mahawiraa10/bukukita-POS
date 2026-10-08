<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Penerimaan barang (penerimaan_barang)
        Schema::create('receiving_goods', function (Blueprint $table) {
            $table->string('id_penerimaan', 20)->primary();
            $table->string('id_pesanan', 20)->nullable()->index(); // ref PO
            $table->date('tanggal_penerimaan');
            $table->string('nomor_faktur', 30);   // invoice vendor
            $table->string('nomor_surat_jalan', 30)->nullable();
            $table->string('id_pemasok', 20)->index();
            $table->string('penerima', 150);
            $table->enum('status', ['SELESAI', 'SEBAGIAN'])->default('SELESAI');
            $table->timestamps();
        });

        Schema::create('receiving_items', function (Blueprint $table) {
            $table->id();
            $table->string('id_penerimaan', 20)->index();
            $table->string('id_produk', 20);
            $table->integer('jumlah_diterima');
            $table->decimal('harga_beli', 10, 2);
            $table->decimal('diskon_pemasok', 10, 2)->nullable();
            $table->timestamps();
        });

        // Retur vendor (vendor_returns)
        Schema::create('vendor_returns', function (Blueprint $table) {
            $table->string('id_pengembalian', 20)->primary();
            $table->string('id_pemasok', 20)->index();
            $table->string('id_penerimaan', 20)->nullable(); // ref penerimaan
            $table->string('id_produk', 20);
            $table->string('nama_produk', 100);
            $table->string('alasan', 255);
            $table->date('tanggal_pengembalian');
            $table->integer('jumlah_dikembalikan');
            $table->timestamps();
        });

        // Load/inject produk (load_produk)
        Schema::create('product_loads', function (Blueprint $table) {
            $table->string('id_load', 20)->primary();
            $table->string('id_produk', 20)->index();
            $table->date('tanggal_load');
            $table->string('sumber_data', 100); // misal nama file excel
            $table->integer('jumlah_produk');   // qty di-inject
            $table->enum('status_load', ['SELESAI', 'BERLANGSUNG'])->default('BERLANGSUNG');
            $table->string('id_acara', 20)->nullable(); // bind event
            $table->timestamps();
        });

        // Kartu stok / pergerakan stok (perubahan_stok) — APPEND-ONLY
        Schema::create('stock_mutations', function (Blueprint $table) {
            $table->string('id_perubahan_stok', 20)->primary();
            $table->string('id_produk', 20)->index();
            $table->string('nama_produk', 100);
            $table->enum('jenis_perubahan', ['PENERIMAAN', 'PENJUALAN', 'REFUND', 'PENGEMBALIAN_PEMASOK', 'PENYESUAIAN', 'LOAD_EVENT']);
            $table->integer('jumlah_perubahan');
            $table->integer('saldo_setelah'); // balance_after
            $table->date('tanggal_perubahan');
            $table->string('id_sesi_kasir', 20)->nullable();
            $table->string('ref_type', 30)->nullable(); // sale/receiving/refund/dll
            $table->string('ref_id', 20)->nullable();
            $table->string('id_pengguna', 20)->nullable();
            $table->timestamps();
        });

        // Penutupan acara (penutupan_acara)
        Schema::create('event_closings', function (Blueprint $table) {
            $table->string('id_penutupan_acara', 20)->primary();
            $table->string('id_acara', 20)->index();
            $table->string('id_produk', 20)->nullable();
            $table->date('tanggal_selesai');
            $table->enum('status_acara', ['SELESAI', 'BERLANGSUNG'])->default('BERLANGSUNG');
            $table->integer('jumlah_sisa_produk')->default(0);
            $table->string('nomor_bon_pengembalian', 30)->nullable();
            $table->date('tanggal_pengembalian')->nullable();
            $table->timestamps();
        });

        // Update purchase_orders lama -> kolom sesuai spec (vendor, event, pembuat string)
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->string('id_pemasok', 20)->nullable()->index();
            $table->string('id_acara', 20)->nullable()->index();
        });

    }

    public function down(): void
    {
        Schema::table('purchase_orders', fn (Blueprint $t) => $t->dropColumn(['id_pemasok', 'id_acara']));
        Schema::dropIfExists('event_closings');
        Schema::dropIfExists('stock_mutations');
        Schema::dropIfExists('product_loads');
        Schema::dropIfExists('vendor_returns');
        Schema::dropIfExists('receiving_items');
        Schema::dropIfExists('receiving_goods');
    }
};
