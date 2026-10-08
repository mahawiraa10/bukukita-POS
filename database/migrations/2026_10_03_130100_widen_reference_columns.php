<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // No invoice format INV/YYYYMMDD/KAS-XX/NNNN = 24 char, tidak muat di varchar(20)
        Schema::table('stock_mutations', fn (Blueprint $t) => $t->string('ref_id', 40)->nullable()->change());
        Schema::table('refunds', fn (Blueprint $t) => $t->string('id_transaksi_referensi', 40)->change());
        Schema::table('member_transactions', fn (Blueprint $t) => $t->string('id_transaksi', 40)->nullable()->change());
    }

    public function down(): void
    {
        Schema::table('stock_mutations', fn (Blueprint $t) => $t->string('ref_id', 20)->nullable()->change());
        Schema::table('refunds', fn (Blueprint $t) => $t->string('id_transaksi_referensi', 20)->change());
        Schema::table('member_transactions', fn (Blueprint $t) => $t->string('id_transaksi', 20)->nullable()->change());
    }
};