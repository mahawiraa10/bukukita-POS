<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Lebarkan dulu ke VARCHAR supaya value baru bisa ditulis (enum lama menolak 'debit').
        DB::statement("ALTER TABLE sales MODIFY payment_method VARCHAR(20) NOT NULL DEFAULT 'cash'");
        DB::statement("ALTER TABLE payments MODIFY payment_method VARCHAR(20) NOT NULL DEFAULT 'cash'");
        DB::statement("ALTER TABLE daily_closings MODIFY metode_pembayaran VARCHAR(30) NOT NULL DEFAULT 'TUNAI'");

        // 2) Normalisasi data lama (qris/transfer/kartu kredit -> debit).
        DB::table('sales')->whereIn('payment_method', ['qris', 'transfer'])->update(['payment_method' => 'debit']);
        DB::table('payments')->whereIn('payment_method', ['qris', 'transfer'])->update(['payment_method' => 'debit']);
        DB::table('daily_closings')->where('metode_pembayaran', 'KARTU KREDIT')->update(['metode_pembayaran' => 'DEBIT']);

        // 3) Kunci ulang jadi enum 2 nilai.
        DB::statement("ALTER TABLE sales MODIFY payment_method ENUM('cash','debit') NOT NULL DEFAULT 'cash'");
        DB::statement("ALTER TABLE payments MODIFY payment_method ENUM('cash','debit') NOT NULL DEFAULT 'cash'");
        DB::statement("ALTER TABLE daily_closings MODIFY metode_pembayaran ENUM('TUNAI','DEBIT') NOT NULL DEFAULT 'TUNAI'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE sales MODIFY payment_method VARCHAR(20) NOT NULL DEFAULT 'cash'");
        DB::statement("ALTER TABLE payments MODIFY payment_method VARCHAR(20) NOT NULL DEFAULT 'cash'");
        DB::statement("ALTER TABLE daily_closings MODIFY metode_pembayaran VARCHAR(30) NOT NULL DEFAULT 'TUNAI'");
        DB::statement("ALTER TABLE sales MODIFY payment_method ENUM('cash','qris','transfer') NOT NULL DEFAULT 'cash'");
        DB::statement("ALTER TABLE payments MODIFY payment_method ENUM('cash','qris','transfer') NOT NULL DEFAULT 'cash'");
        DB::statement("ALTER TABLE daily_closings MODIFY metode_pembayaran ENUM('TUNAI','DEBIT','KARTU KREDIT') NOT NULL DEFAULT 'TUNAI'");
    }
};
