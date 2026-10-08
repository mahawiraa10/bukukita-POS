<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Sesi kasir / TILL (sesi_till)
        Schema::create('cashier_shifts', function (Blueprint $table) {
            $table->string('id_sesi_kasir', 20)->primary();
            $table->string('id_pengguna', 20)->index(); // FK level aplikasi (users.id bigint)
            $table->string('nomor_kasir', 10);
            $table->dateTime('waktu_buka');
            $table->dateTime('waktu_tutup')->nullable();
            $table->decimal('modal_awal', 10, 2);
            $table->enum('status_sesi', ['BUKA', 'TUTUP'])->default('BUKA');
            $table->timestamps();
        });

        // Transaksi refund (transaksi_refund)
        Schema::create('refunds', function (Blueprint $table) {
            $table->string('id_pengembalian', 20)->primary();
            $table->string('id_produk', 20)->index();
            $table->string('id_transaksi_referensi', 20); // nota lama
            $table->date('tanggal_pengembalian');
            $table->decimal('jumlah_pengembalian', 10, 2); // nilai uang
            $table->string('sumber_pengembalian', 30);     // modal awal / barang lain
            $table->integer('jumlah_dikembalikan');         // qty barang
            $table->decimal('jumlah_nilai', 10, 2);         // nilai barang
            $table->timestamps();
        });

        // Penutupan kasir (closing_till)
        Schema::create('daily_closings', function (Blueprint $table) {
            $table->string('id_penutupan', 20)->primary();
            $table->string('id_sesi_kasir', 20)->index();
            $table->decimal('modal_awal', 10, 2);
            $table->enum('metode_pembayaran', ['TUNAI', 'DEBIT', 'KARTU KREDIT']);
            $table->decimal('hasil_penjualan', 10, 2);
            $table->decimal('jumlah_pengembalian', 10, 2)->default(0);
            $table->decimal('uang_fisik', 10, 2)->nullable();
            $table->decimal('selisih', 10, 2)->nullable();
            $table->date('tanggal_penutupan');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_closings');
        Schema::dropIfExists('refunds');
        Schema::dropIfExists('cashier_shifts');
    }
};
