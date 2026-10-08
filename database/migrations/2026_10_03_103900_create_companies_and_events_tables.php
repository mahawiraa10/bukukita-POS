<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel Perusahaan (setup nama company & alamat)
        Schema::create('companies', function (Blueprint $table) {
            $table->increments('id_perusahaan');
            $table->string('nama_perusahaan', 100);
            $table->string('alamat_perusahaan', 255);
            $table->string('logo')->nullable();
            $table->string('email')->nullable();
            $table->string('nomor_telepon', 20)->nullable();
            $table->timestamps();
        });

        // Tabel Acara/Event (ID bisa diubah admin, diskon % atau nilai)
        Schema::create('events', function (Blueprint $table) {
            $table->string('id_acara', 20)->primary();
            $table->string('nama_acara', 100);
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->string('lokasi', 150);
            $table->enum('jenis_diskon', ['PERSEN', 'NILAI'])->nullable();
            $table->decimal('nilai_diskon', 10, 2)->nullable();
            $table->enum('status_acara', ['AKTIF', 'NONAKTIF'])->default('NONAKTIF');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
        Schema::dropIfExists('companies');
    }
};
