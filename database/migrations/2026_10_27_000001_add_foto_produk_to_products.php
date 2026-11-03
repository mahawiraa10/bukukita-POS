<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Path/URL foto produk. Wajib .webp (divalidasi di controller) —
            // biar katalog ringan & konsisten, gak campur jpg/png.
            $table->string('foto_produk', 255)->nullable()->after('isbn');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('foto_produk');
        });
    }
};