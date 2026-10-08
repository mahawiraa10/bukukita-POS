<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Hierarki 4 Level: Divisi -> Departemen -> Kelas -> Sub Kelas
        // Cascade protection: orangtuanya ditolak hapus kalau anaknya masih ada (restrictOnDelete)

        Schema::create('divisions', function (Blueprint $table) {
            $table->string('id_divisi', 10)->primary();
            $table->string('nama_divisi', 100);
            $table->timestamps();
        });

        Schema::create('departments', function (Blueprint $table) {
            $table->string('id_departemen', 10)->primary();
            $table->string('nama_departemen', 100);
            $table->string('id_divisi', 10);
            $table->foreign('id_divisi')->references('id_divisi')->on('divisions')->restrictOnDelete();
            $table->timestamps();
        });

        Schema::create('classes', function (Blueprint $table) {
            $table->string('id_kelas', 10)->primary();
            $table->string('nama_kelas', 100);
            $table->string('id_departemen', 10);
            $table->foreign('id_departemen')->references('id_departemen')->on('departments')->restrictOnDelete();
            $table->timestamps();
        });

        Schema::create('subclasses', function (Blueprint $table) {
            $table->string('id_subkelas', 10)->primary();
            $table->string('nama_subkelas', 100);
            $table->string('id_kelas', 10);
            $table->foreign('id_kelas')->references('id_kelas')->on('classes')->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subclasses');
        Schema::dropIfExists('classes');
        Schema::dropIfExists('departments');
        Schema::dropIfExists('divisions');
    }
};
