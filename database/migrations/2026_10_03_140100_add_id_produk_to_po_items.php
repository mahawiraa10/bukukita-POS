<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('po_items', function (Blueprint $t) {
            if (!Schema::hasColumn('po_items', 'id_produk')) {
                $t->string('id_produk', 20)->nullable()->after('purchase_order_id');
                $t->index('id_produk');
            }
        });
    }

    public function down(): void
    {
        Schema::table('po_items', function (Blueprint $t) {
            if (Schema::hasColumn('po_items', 'id_produk')) {
                $t->dropColumn('id_produk');
            }
        });
    }
};