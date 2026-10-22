<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            // Tipe PO: reguler (beli putus) / konsinyasi (titip jual/event)
            $table->enum('tipe_po', ['reguler', 'konsinyasi'])->default('reguler')->after('po_code');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn('tipe_po');
        });
    }
};