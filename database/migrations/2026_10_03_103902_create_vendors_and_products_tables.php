<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pemasok/Vendor — dipisah per divisi (buku & non-buku), email unik
        Schema::create('vendors', function (Blueprint $table) {
            $table->string('id_pemasok', 20)->primary();
            $table->string('nama_pemasok', 150);
            $table->string('alamat_lengkap', 255);
            $table->string('nomor_telepon_pemasok', 20);
            $table->string('email_pemasok', 100)->unique()->nullable();
            $table->decimal('diskon_pemasok', 10, 2)->nullable();
            $table->string('nomor_rekening', 30)->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('id_divisi', 10);
            $table->foreign('id_divisi')->references('id_divisi')->on('divisions')->restrictOnDelete();
            $table->timestamps();
        });

        // Produk — SKU unik (validasi anti-duplikat bulk inject), HPP otomatis
        Schema::create('products', function (Blueprint $table) {
            $table->string('id_produk', 20)->primary();
            $table->string('sku', 50)->unique();
            $table->string('id_pemasok', 20)->nullable();
            $table->foreign('id_pemasok')->references('id_pemasok')->on('vendors')->nullOnDelete();
            $table->string('id_departemen', 10);
            $table->foreign('id_departemen')->references('id_departemen')->on('departments')->restrictOnDelete();
            $table->string('id_subkelas', 10)->nullable();
            $table->foreign('id_subkelas')->references('id_subkelas')->on('subclasses')->nullOnDelete();
            $table->string('nama_produk', 100);
            $table->string('deskripsi_produk', 75)->nullable(); // item desc (pengarang/ukuran)
            $table->string('penerbit', 150)->nullable();
            $table->string('isbn', 20)->unique()->nullable();
            $table->decimal('harga_jual', 10, 2);
            $table->decimal('harga_beli', 10, 2);
            $table->decimal('harga_pokok', 10, 2); // cost price / HPP
            $table->decimal('diskon_base', 10, 2)->nullable(); // diskon dasar produk (fungsi 12)
            $table->integer('stok')->default(0);
            $table->integer('min_stock')->default(5);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
        Schema::dropIfExists('vendors');
    }
};
