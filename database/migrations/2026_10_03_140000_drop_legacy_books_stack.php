<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Buang sisa legacy "books" stack (schema v1) yang digantikan
 * products + hierarki divisi/departemen/kelas/subkelas + vendors.
 *
 * Urutan penting: lepas FK dulu, baru drop tabel.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Bersihkan data transaksi jalur lama (book_id, tanpa id_produk)
        DB::table('sale_items')->whereNull('id_produk')->delete();
        DB::table('sales')->whereNotIn('id', function ($q) {
            $q->select('sale_id')->from('sale_items');
        })->delete();
        DB::statement('DELETE FROM payments WHERE sale_id NOT IN (SELECT id FROM sales)');

        // 2. Lepas FK + index + kolom book_id / publisher_id
        Schema::table('sale_items', function (Blueprint $t) {
            $t->dropForeign(['book_id']);
            $t->dropIndex(['book_id']);
        });
        Schema::table('sale_items', function (Blueprint $t) {
            if (Schema::hasColumn('sale_items', 'book_id')) {
                $t->dropColumn('book_id');
            }
        });

        Schema::table('po_items', function (Blueprint $t) {
            if ($this->hasFk('po_items', 'po_items_book_id_foreign')) {
                $t->dropForeign(['book_id']);
            }
        });
        Schema::table('po_items', function (Blueprint $t) {
            if (Schema::hasColumn('po_items', 'book_id')) {
                $t->dropColumn('book_id');
            }
        });

        Schema::table('purchase_orders', function (Blueprint $t) {
            if ($this->hasFk('purchase_orders', 'purchase_orders_publisher_id_foreign')) {
                $t->dropForeign(['publisher_id']);
            }
        });
        Schema::table('purchase_orders', function (Blueprint $t) {
            if (Schema::hasColumn('purchase_orders', 'publisher_id')) {
                $t->dropColumn('publisher_id');
            }
        });

        // 3. Drop tabel legacy (urutan: anak -> induk)
        Schema::dropIfExists('books');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('publishers');
        Schema::dropIfExists('loyalty_points');
        Schema::dropIfExists('activity_logs');
    }

    public function down(): void
    {
        // Tidak di-restore otomatis; pakai backup SQL kalau perlu balik.
    }

    private function hasFk(string $table, string $fk): bool
    {
        return DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', $table)
            ->where('CONSTRAINT_NAME', $fk)
            ->where('CONSTRAINT_TYPE', 'FOREIGN KEY')
            ->exists();
    }
};