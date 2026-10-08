<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Transaksi anggota (transaksi_member)
        Schema::create('member_transactions', function (Blueprint $table) {
            $table->string('id_transaksi_anggota', 20)->primary();
            $table->string('id_anggota', 20)->index();
            $table->string('id_transaksi', 20)->nullable()->index();
            $table->string('id_poin', 10)->nullable();
            $table->integer('poin_diperoleh')->default(0);
            $table->integer('poin_digunakan')->nullable();
            $table->integer('saldo_poin');
            $table->timestamps();
        });

        // Kolom baru untuk penjualan lama agar nyambung modul shift/event/refund
        Schema::table('sales', function (Blueprint $table) {
            $table->string('id_sesi_kasir', 20)->nullable()->index();
            $table->string('id_acara', 20)->nullable()->index();
            $table->date('business_date')->nullable()->index();
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->string('id_produk', 20)->nullable()->index(); // link ke products baru
            $table->decimal('diskon_produk', 10, 2)->default(0);
        });

        // PIN supervisor untuk otorisasi refund/void (Function 5)
        Schema::table('users', function (Blueprint $table) {
            $table->string('pin', 255)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_transactions');
        Schema::table('sales', fn (Blueprint $t) => $t->dropColumn(['id_sesi_kasir', 'id_acara', 'business_date']));
        Schema::table('sale_items', fn (Blueprint $t) => $t->dropColumn(['id_produk', 'diskon_produk']));
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn('pin'));
    }
};
