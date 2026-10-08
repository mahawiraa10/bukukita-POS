<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Point — aturan konversi poin (nilai_transaksi Rp X = nilai_poin Y)
        Schema::create('loyalty_rules', function (Blueprint $table) {
            $table->string('id_poin', 10)->primary();
            $table->decimal('nilai_transaksi', 10, 2); // misal 10000
            $table->integer('nilai_poin');              // misal dapat 1 poin
            $table->integer('tier_silver_min')->default(500000);
            $table->integer('tier_gold_min')->default(2000000);
            $table->decimal('diskon_silver', 5, 2)->default(5);
            $table->decimal('diskon_gold', 5, 2)->default(10);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loyalty_rules');
    }
};
