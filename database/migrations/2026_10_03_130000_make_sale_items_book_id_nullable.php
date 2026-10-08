<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // POS v2 memakai id_produk (products), bukan book_id legacy. book_id jadi opsional.
        Schema::table('sale_items', function (Blueprint $table) {
            $table->unsignedBigInteger('book_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->unsignedBigInteger('book_id')->nullable(false)->change();
        });
    }
};